<?php

namespace App\Jobs;

use App\Models\Campana;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class EnviarCampanaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(public int $campanaId)
    {
        $this->queue = 'campanas';
    }

    public function handle(): void
    {
        // Obtener todos los tenants desde la base central (mysql)
        $tenants = Tenant::on('mysql')->where('estado', 'activo')->get();

        $campana = null;
        $tenant = null;

        foreach ($tenants as $t) {
            $sqlitePath = $t->getSqlitePath();
            if (! file_exists($sqlitePath)) {
                continue;
            }

            // Configurar conexión temporal
            Config::set('database.connections.tenant_temp', [
                'driver' => 'sqlite',
                'database' => $sqlitePath,
                'prefix' => '',
                'foreign_key_constraints' => true,
            ]);
            DB::purge('tenant_temp');

            // Buscar la campaña en este tenant
            $found = DB::connection('tenant_temp')->table('campanas')->where('id', $this->campanaId)->first();

            if ($found) {
                $tenant = $t;
                // Configurar la conexión 'tenant' definitiva
                Config::set('database.connections.tenant', [
                    'driver' => 'sqlite',
                    'database' => $sqlitePath,
                    'prefix' => '',
                    'foreign_key_constraints' => true,
                ]);
                DB::purge('tenant');

                // Ahora sí podemos usar Eloquent
                $campana = Campana::find($this->campanaId);
                break;
            }
        }

        if (! $campana) {
            return;
        }

        $enviosPendientes = DB::connection('tenant')
            ->table('campana_envios')
            ->where('campana_id', $this->campanaId)
            ->where('estado', 'pendiente')
            ->get();

        $jobs = $enviosPendientes->map(function ($envio, int $index) {
            $job = new ProcesarEnvioCampana($envio->id);
            $job->delay(now()->addSeconds($index * 3));

            return $job;
        })->toArray();

        if (empty($jobs)) {
            $campana->update(['estado' => 'completada']);

            return;
        }

        Bus::batch($jobs)
            ->name("Campaña {$campana->id}")
            ->onQueue('campanas')
            ->dispatch();

        $campana->update(['estado' => 'enviando']);
    }
}
