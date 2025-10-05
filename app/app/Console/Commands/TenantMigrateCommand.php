<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use App\Models\Tenant;

class TenantMigrateCommand extends Command
{
    protected $signature = 'tenant:migrate {rut}';
    protected $description = 'Ejecuta migraciones de SQLite para el tenant indicado por RUT';

    public function handle(): int
    {
        $rut = $this->argument('rut');
        $tenant = Tenant::where('rut', $rut)->first();
        if (!$tenant) {
            $this->error("Tenant {$rut} no encontrado");
            return self::FAILURE;
        }

        $sqlitePath = $tenant->getSqlitePath();
        if (!File::exists(dirname($sqlitePath))) {
            File::makeDirectory(dirname($sqlitePath), 0755, true);
        }
        if (!File::exists($sqlitePath)) {
            File::put($sqlitePath, '');
        }

        config([
            'database.connections.tenant_temp' => [
                'driver' => 'sqlite',
                'database' => $sqlitePath,
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);

        DB::purge('tenant_temp');

        $this->info('Ejecutando migraciones del tenant...');
        Artisan::call('migrate', [
            '--database' => 'tenant_temp',
            '--path' => 'app/database/migrations/tenant',
            '--force' => true,
        ]);

        if (Schema::hasColumn('tenants', 'ultima_migracion')) {
            $tenant->ultima_migracion = now();
            $tenant->save();
        }

        DB::purge('tenant_temp');
        DB::setDefaultConnection('mysql');

        $this->info('Migraciones del tenant completadas.');
        return self::SUCCESS;
    }
}



