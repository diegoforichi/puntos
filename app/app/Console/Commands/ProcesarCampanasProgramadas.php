<?php

namespace App\Console\Commands;

use App\Jobs\EnviarCampanaJob;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class ProcesarCampanasProgramadas extends Command
{
    protected $signature = 'campanas:procesar-programadas';

    protected $description = 'Despacha campañas programadas cuyo horario ya llegó.';

    public function handle(): int
    {
        $tenants = Tenant::on('mysql')->where('estado', 'activo')->get();
        $totalEncoladas = 0;

        foreach ($tenants as $tenant) {
            $sqlitePath = $tenant->getSqlitePath();

            if (! file_exists($sqlitePath)) {
                continue;
            }

            // Configurar conexión temporal para este tenant
            Config::set('database.connections.tenant_temp', [
                'driver' => 'sqlite',
                'database' => $sqlitePath,
                'prefix' => '',
                'foreign_key_constraints' => true,
            ]);
            DB::purge('tenant_temp');

            // Buscar campañas programadas cuya hora ya llegó
            $campanasPendientes = DB::connection('tenant_temp')
                ->table('campanas')
                ->where('estado', 'pendiente')
                ->whereNotNull('fecha_programada')
                ->where('fecha_programada', '<=', now()->toDateTimeString())
                ->get();

            foreach ($campanasPendientes as $campana) {
                // Actualizar estado a en_cola
                DB::connection('tenant_temp')
                    ->table('campanas')
                    ->where('id', $campana->id)
                    ->update([
                        'estado' => 'en_cola',
                        'fecha_programada' => null,
                        'updated_at' => now()->toDateTimeString(),
                    ]);

                // Despachar job
                EnviarCampanaJob::dispatch($campana->id)->onQueue('campanas');

                $this->info("Tenant {$tenant->rut}: Campaña {$campana->id} encolada.");
                $totalEncoladas++;
            }
        }

        if ($totalEncoladas === 0) {
            $this->info('No hay campañas programadas para procesar.');
        } else {
            $this->info("Total de campañas encoladas: {$totalEncoladas}");
        }

        return self::SUCCESS;
    }
}
