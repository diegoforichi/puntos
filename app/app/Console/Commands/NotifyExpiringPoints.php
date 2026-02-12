<?php

namespace App\Console\Commands;

use App\Jobs\EnviarNotificacionWhatsApp;
use App\Models\Tenant;
use App\Models\Configuracion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NotifyExpiringPoints extends Command
{
    protected $signature = 'puntos:notificar-vencimiento {--days=7 : Días previos al vencimiento para notificar} {--tenant=}';

    protected $description = 'Envía notificaciones WhatsApp a clientes con puntos próximos a vencer';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $days = $days > 0 ? $days : 7;
        $tenantOption = $this->option('tenant');

        $tenantsQuery = Tenant::where('estado', 'activo');
        if ($tenantOption) {
            $tenantsQuery->where(function ($query) use ($tenantOption) {
                $query->where('rut', $tenantOption)
                    ->orWhere('id', $tenantOption);
            });
        }

        $tenants = $tenantsQuery->get();

        if ($tenants->isEmpty()) {
            $this->warn('No se encontraron tenants activos para procesar.');
            return self::SUCCESS;
        }

        $totalNotificaciones = 0;
        $totalTenantsProcesados = 0;

        foreach ($tenants as $tenant) {
            try {
                $sqlitePath = $tenant->getSqlitePath();

                if (!file_exists($sqlitePath)) {
                    $this->error("Tenant {$tenant->rut}: archivo SQLite no encontrado ({$sqlitePath})");
                    continue;
                }

                config([
                    'database.connections.tenant' => [
                        'driver' => 'sqlite',
                        'database' => $sqlitePath,
                        'prefix' => '',
                        'foreign_key_constraints' => false,
                    ],
                ]);

                DB::purge('tenant');

                $eventos = Configuracion::get('eventos_whatsapp', []);

                if (!($eventos['puntos_por_vencer'] ?? false)) {
                    $this->line("Tenant {$tenant->rut}: notificaciones de puntos por vencer desactivadas");
                    continue;
                }

                $desde = Carbon::now();
                $hasta = Carbon::now()->addDays($days);

                // Buscar clientes que tienen facturas por vencer en los próximos N días
                // Obtenemos la fecha más próxima de vencimiento por cliente
                $clientesConFacturasPorVencer = DB::connection('tenant')->table('facturas')
                    ->select(
                        'cliente_id',
                        DB::raw('MIN(fecha_vencimiento) as proxima_expiracion')
                    )
                    ->where('fecha_vencimiento', '>', $desde)
                    ->where('fecha_vencimiento', '<=', $hasta)
                    ->where('puntos_generados', '>', 0)
                    ->groupBy('cliente_id')
                    ->get();

                if ($clientesConFacturasPorVencer->isEmpty()) {
                    $this->line("Tenant {$tenant->rut}: sin clientes con puntos por vencer en {$days} día(s)");
                    continue;
                }

                // Obtener datos completos de clientes (incluyendo puntos_acumulados = saldo REAL)
                $clientes = DB::connection('tenant')->table('clientes')
                    ->whereIn('id', $clientesConFacturasPorVencer->pluck('cliente_id'))
                    ->where('puntos_acumulados', '>', 0) // Solo clientes con saldo real > 0
                    ->get()
                    ->keyBy('id');

                if ($clientes->isEmpty()) {
                    $this->line("Tenant {$tenant->rut}: clientes con facturas por vencer pero sin saldo real disponible");
                    continue;
                }

                // Mapear fechas de expiración por cliente_id
                $fechasExpiracion = $clientesConFacturasPorVencer->pluck('proxima_expiracion', 'cliente_id');

                $notificacionesTenant = 0;
                $clientesSinTelefono = 0;
                $clientesSinSaldo = 0;
                $delaySegundos = 0;
                $intervaloEntreJobs = 8; // 8 segundos entre cada mensaje para evitar bloqueos

                foreach ($clientes as $cliente) {
                    if (empty($cliente->telefono)) {
                        $clientesSinTelefono++;
                        continue;
                    }

                    // Usar el saldo REAL del cliente (puntos_acumulados), no la suma de facturas
                    $puntosReales = (float) $cliente->puntos_acumulados;

                    if ($puntosReales <= 0) {
                        $clientesSinSaldo++;
                        continue;
                    }

                    $clienteArray = (array) $cliente;
                    $fechaVencimiento = $fechasExpiracion->get($cliente->id)
                        ? Carbon::parse($fechasExpiracion->get($cliente->id))->format('d/m/Y')
                        : Carbon::now()->addDays($days)->format('d/m/Y');

                    // Encolar con delay progresivo para evitar bloqueos de WhatsApp
                    EnviarNotificacionWhatsApp::dispatch(
                        $tenant->id,
                        EnviarNotificacionWhatsApp::TIPO_VENCIMIENTO,
                        $clienteArray,
                        [
                            'puntos' => $puntosReales,
                            'fecha_vencimiento' => $fechaVencimiento,
                        ]
                    )->delay(now()->addSeconds($delaySegundos));

                    $delaySegundos += $intervaloEntreJobs;
                    $notificacionesTenant++;
                }

                DB::purge('tenant');

                if ($notificacionesTenant > 0) {
                    $totalNotificaciones += $notificacionesTenant;
                    $totalTenantsProcesados++;
                    $tiempoEstimado = ceil($notificacionesTenant * $intervaloEntreJobs / 60);
                    $this->info("Tenant {$tenant->rut}: {$notificacionesTenant} notificación(es) encolada(s) (~{$tiempoEstimado} min)");
                }

                if ($clientesSinTelefono > 0) {
                    $this->line("Tenant {$tenant->rut}: {$clientesSinTelefono} cliente(s) omitido(s) sin teléfono");
                }

                if ($clientesSinSaldo > 0) {
                    $this->line("Tenant {$tenant->rut}: {$clientesSinSaldo} cliente(s) omitido(s) sin saldo real");
                }
            } catch (\Throwable $e) {
                Log::error('Error notificando puntos por vencer', [
                    'tenant' => $tenant->rut,
                    'error' => $e->getMessage(),
                ]);

                $this->error("Tenant {$tenant->rut}: error - {$e->getMessage()}");
            }
        }

        $this->line('-------------------------------------------');
        $this->info("Tenants procesados: {$totalTenantsProcesados}");
        $this->info("Notificaciones encoladas: {$totalNotificaciones}");

        if ($totalNotificaciones > 0) {
            $tiempoTotal = ceil($totalNotificaciones * 8 / 60);
            $this->info("Tiempo estimado de envío: ~{$tiempoTotal} minutos (8 seg entre mensajes)");
        }

        return self::SUCCESS;
    }
}
