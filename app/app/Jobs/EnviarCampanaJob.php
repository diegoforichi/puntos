<?php

namespace App\Jobs;

use App\Models\Campana;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class EnviarCampanaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(public int $campanaId, public ?int $tenantId = null)
    {
        $this->queue = 'campanas';
    }

    public function handle(): void
    {
        $campana = null;
        $tenant = null;

        // Ruta principal: usar tenant_id explícito para evitar mezclar tenants
        if ($this->tenantId) {
            $tenant = Tenant::on('mysql')
                ->where('estado', 'activo')
                ->where('id', $this->tenantId)
                ->first();

            if (! $tenant) {
                return;
            }

            $sqlitePath = $tenant->getSqlitePath();
            if (! file_exists($sqlitePath)) {
                return;
            }

            Config::set('database.connections.tenant', [
                'driver' => 'sqlite',
                'database' => $sqlitePath,
                'prefix' => '',
                'foreign_key_constraints' => true,
            ]);
            DB::purge('tenant');
            DB::setDefaultConnection('tenant');

            $campana = Campana::find($this->campanaId);

            if (! $campana) {
                return;
            }

            if ($campana->tenant_id && (int) $campana->tenant_id !== (int) $tenant->id) {
                return;
            }
        } else {
            // Compatibilidad con jobs antiguos en cola que no traen tenantId
            $tenants = Tenant::on('mysql')->where('estado', 'activo')->get();

            foreach ($tenants as $t) {
                $sqlitePath = $t->getSqlitePath();
                if (! file_exists($sqlitePath)) {
                    continue;
                }

                Config::set('database.connections.tenant_temp', [
                    'driver' => 'sqlite',
                    'database' => $sqlitePath,
                    'prefix' => '',
                    'foreign_key_constraints' => true,
                ]);
                DB::purge('tenant_temp');

                $found = DB::connection('tenant_temp')->table('campanas')->where('id', $this->campanaId)->first();

                if ($found) {
                    $tenant = $t;
                    Config::set('database.connections.tenant', [
                        'driver' => 'sqlite',
                        'database' => $sqlitePath,
                        'prefix' => '',
                        'foreign_key_constraints' => true,
                    ]);
                    DB::purge('tenant');
                    DB::setDefaultConnection('tenant');

                    $campana = Campana::find($this->campanaId);
                    break;
                }
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

        $jobs = $enviosPendientes->map(function ($envio) use ($tenant) {
            return new ProcesarEnvioCampana($envio->id, $tenant->id);
        })->toArray();

        if (empty($jobs)) {
            $campana->update(['estado' => 'completada']);

            return;
        }

        $intervaloSegundos = 8; // 8 segundos entre cada mensaje para evitar bloqueos de WhatsApp

        foreach ($jobs as $index => $job) {
            dispatch($job)->onQueue('campanas')->delay(now()->addSeconds($index * $intervaloSegundos));
        }

        $campana->update(['estado' => 'enviando']);
    }
}
