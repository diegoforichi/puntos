<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\Configuracion;
use App\Services\NotificacionService;
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

                $facturas = DB::connection('tenant')->table('facturas')
                    ->select(
                        'cliente_id',
                        DB::raw('SUM(puntos_generados) as puntos_generados'),
                        DB::raw('MIN(fecha_vencimiento) as fecha_vencimiento')
                    )
                    ->where('fecha_vencimiento', '>', $desde)
                    ->where('fecha_vencimiento', '<=', $hasta)
                    ->where('puntos_generados', '>', 0)
                    ->groupBy('cliente_id')
                    ->get();

                if ($facturas->isEmpty()) {
                    $this->line("Tenant {$tenant->rut}: sin clientes con puntos por vencer en {$days} día(s)");
                    continue;
                }

                $clientes = DB::connection('tenant')->table('clientes')
                    ->whereIn('id', $facturas->pluck('cliente_id'))
                    ->get()
                    ->keyBy('id');

                $notificacionService = new NotificacionService($tenant);
                $notificacionesTenant = 0;
                $clientesSinTelefono = 0;

                foreach ($facturas as $factura) {
                    $cliente = $clientes->get($factura->cliente_id);

                    if (!$cliente) {
                        continue;
                    }

                    if (empty($cliente->telefono)) {
                        $clientesSinTelefono++;
                        continue;
                    }

                    $clienteArray = (array) $cliente;
                    $puntos = (float) $factura->puntos_generados;
                    $fechaVencimiento = $factura->fecha_vencimiento
                        ? Carbon::parse($factura->fecha_vencimiento)->format('d/m/Y')
                        : Carbon::now()->addDays($days)->format('d/m/Y');

                    $notificacionService->notificarPuntosProximosAVencer(
                        $clienteArray,
                        $puntos,
                        $fechaVencimiento
                    );

                    $notificacionesTenant++;
                }

                DB::purge('tenant');

                if ($notificacionesTenant > 0) {
                    $totalNotificaciones += $notificacionesTenant;
                    $totalTenantsProcesados++;
                    $this->info("Tenant {$tenant->rut}: {$notificacionesTenant} notificación(es) enviada(s)");
                }

                if ($clientesSinTelefono > 0) {
                    $this->line("Tenant {$tenant->rut}: {$clientesSinTelefono} cliente(s) omitido(s) sin teléfono");
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
        $this->info("Notificaciones enviadas: {$totalNotificaciones}");

        return self::SUCCESS;
    }
}
