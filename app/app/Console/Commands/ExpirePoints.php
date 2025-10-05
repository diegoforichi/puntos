<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\Actividad;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ExpirePoints extends Command
{
    protected $signature = 'puntos:expirar {--tenant=} {--days=0 : Días de gracia extras antes de expirar (default 0)}';

    protected $description = 'Descuenta puntos vencidos en todos los tenants y registra historial';

    public function handle(): int
    {
        $tenantOption = $this->option('tenant');
        $extraDays = max(0, (int) $this->option('days'));

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

        $tenantsProcesados = 0;
        $totalClientesAfectados = 0;
        $totalPuntosVencidos = 0;
        $hoy = Carbon::now();
        $fechaCorte = $hoy->copy()->subDays($extraDays);

        foreach ($tenants as $tenant) {
            $enTransaccion = false;

            try {
                $sqlitePath = $tenant->getSqlitePath();

                if (!file_exists($sqlitePath)) {
                    $this->error("Tenant {$tenant->rut}: archivo SQLite no encontrado ({$sqlitePath})");
                    DB::setDefaultConnection('mysql');
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
                DB::setDefaultConnection('tenant');

                $facturasVencidas = DB::connection('tenant')->table('facturas')
                    ->select(
                        'cliente_id',
                        DB::raw('SUM(puntos_generados) as puntos_vencidos')
                    )
                    ->where('fecha_vencimiento', '<', $fechaCorte)
                    ->where('puntos_generados', '>', 0)
                    ->groupBy('cliente_id')
                    ->get();

                if ($facturasVencidas->isEmpty()) {
                    $this->line("Tenant {$tenant->rut}: sin puntos vencidos");
                    DB::purge('tenant');
                    DB::setDefaultConnection('mysql');
                    continue;
                }

                $clientesIds = $facturasVencidas->pluck('cliente_id');
                $clientes = DB::connection('tenant')->table('clientes')
                    ->whereIn('id', $clientesIds)
                    ->get()
                    ->keyBy('id');

                $clientesActualizados = 0;
                $puntosVencidosTenant = 0;
                $detallesLog = [];

                DB::connection('tenant')->beginTransaction();
                $enTransaccion = true;

                foreach ($facturasVencidas as $registro) {
                    $cliente = $clientes->get($registro->cliente_id);

                    if (!$cliente) {
                        continue;
                    }

                    $puntosVencidos = (float) $registro->puntos_vencidos;

                    if ($puntosVencidos <= 0) {
                        continue;
                    }

                    $puntosRestantes = max(0, (float) $cliente->puntos_acumulados - $puntosVencidos);

                    DB::connection('tenant')->table('clientes')
                        ->where('id', $cliente->id)
                        ->update([
                            'puntos_acumulados' => $puntosRestantes,
                            'updated_at' => now(),
                        ]);

                    DB::connection('tenant')->table('puntos_vencidos')->insert([
                        'cliente_id' => $cliente->id,
                        'puntos_vencidos' => $puntosVencidos,
                        'motivo' => 'Vencimiento automático',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::connection('tenant')->table('facturas')
                        ->where('cliente_id', $cliente->id)
                        ->where('fecha_vencimiento', '<', $fechaCorte)
                        ->where('puntos_generados', '>', 0)
                        ->update([
                            'puntos_generados' => 0,
                            'updated_at' => now(),
                        ]);

                    $clientesActualizados++;
                    $puntosVencidosTenant += $puntosVencidos;

                    $detallesLog[] = [
                        'cliente_id' => $cliente->id,
                        'cliente_documento' => $cliente->documento,
                        'puntos_vencidos' => $puntosVencidos,
                        'puntos_restantes' => $puntosRestantes,
                    ];
                }

                DB::connection('tenant')->commit();
                $enTransaccion = false;

                if ($clientesActualizados > 0) {
                    $tenantsProcesados++;
                    $totalClientesAfectados += $clientesActualizados;
                    $totalPuntosVencidos += $puntosVencidosTenant;

                    $this->info("Tenant {$tenant->rut}: vencidos {$puntosVencidosTenant} puntos en {$clientesActualizados} cliente(s)");

                    DB::connection('tenant')->table('actividades')->insert([
                        'usuario_id' => null,
                        'accion' => Actividad::ACCION_CONFIG,
                        'descripcion' => 'Vencimiento automático de puntos ejecutado',
                        'datos_json' => json_encode([
                            'clientes_afectados' => $clientesActualizados,
                            'puntos_vencidos' => $puntosVencidosTenant,
                            'fecha_corte' => $fechaCorte->toDateTimeString(),
                            'detalle' => $detallesLog,
                        ], JSON_UNESCAPED_UNICODE),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $this->line("Tenant {$tenant->rut}: puntos vencidos encontrados, pero ningún cliente necesitó actualización");
                }

                DB::purge('tenant');
                DB::setDefaultConnection('mysql');

            } catch (\Throwable $e) {
                if ($enTransaccion) {
                    DB::connection('tenant')->rollBack();
                }
                DB::purge('tenant');
                DB::setDefaultConnection('mysql');

                Log::error('Error expirando puntos', [
                    'tenant' => $tenant->rut,
                    'error' => $e->getMessage(),
                ]);

                $this->error("Tenant {$tenant->rut}: error - {$e->getMessage()}");
            }
        }

        DB::setDefaultConnection('mysql');

        $this->line('-------------------------------------------');
        $this->info("Tenants procesados: {$tenantsProcesados}");
        $this->info("Clientes afectados: {$totalClientesAfectados}");
        $this->info("Puntos vencidos totales: " . number_format($totalPuntosVencidos, 2, ',', '.'));

        if ($totalClientesAfectados === 0) {
            $this->line('No se vencieron puntos en esta ejecución.');
        }

        return self::SUCCESS;
    }
}
