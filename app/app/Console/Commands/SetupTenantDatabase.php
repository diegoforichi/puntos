<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class SetupTenantDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:setup-database {rut : RUT del tenant}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configurar base de datos SQLite de un tenant y ejecutar migraciones';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $rut = $this->argument('rut');
        
        $this->info("🔧 Configurando base de datos para tenant: {$rut}");
        
        // Buscar tenant
        $tenant = Tenant::where('rut', $rut)->first();
        
        if (!$tenant) {
            $this->error("❌ Tenant con RUT {$rut} no encontrado");
            return 1;
        }
        
        $sqlitePath = $tenant->getSqlitePath();
        
        // Crear archivo SQLite si no existe
        if (!File::exists($sqlitePath)) {
            File::put($sqlitePath, '');
            $this->info("✅ Archivo SQLite creado: {$sqlitePath}");
        }
        
        // Configurar conexión temporal a SQLite del tenant
        config([
            'database.connections.tenant_temp' => [
                'driver' => 'sqlite',
                'database' => $sqlitePath,
                'prefix' => '',
                'foreign_key_constraints' => true,
            ]
        ]);
        
        // Cambiar conexión por defecto temporalmente
        DB::purge('tenant_temp');
        DB::setDefaultConnection('tenant_temp');
        
        $this->info("📊 Ejecutando migraciones del tenant...");
        
        try {
            // Ejecutar migración de tablas del tenant
            $migrationFile = database_path('migrations/tenant/2025_09_29_000001_create_tenant_tables.php');
            
            if (!File::exists($migrationFile)) {
                $this->error("❌ Archivo de migración no encontrado: {$migrationFile}");
                return 1;
            }
            
            // Cargar y ejecutar la migración manualmente
            require_once $migrationFile;
            
            $migration = new class extends \Illuminate\Database\Migrations\Migration {
                public function up(): void {
                    require database_path('migrations/tenant/2025_09_29_000001_create_tenant_tables.php');
                }
            };
            
            // Ejecutar el contenido del archivo
            $migrationClass = require $migrationFile;
            $migrationClass->up();
            
            $this->info("✅ Migraciones ejecutadas exitosamente");
            
            // Verificar tablas creadas
            $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            $this->info("\n📋 Tablas creadas:");
            foreach ($tables as $table) {
                $this->line("   - {$table->name}");
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Error al ejecutar migraciones: " . $e->getMessage());
            return 1;
        } finally {
            // Restaurar conexión por defecto
            DB::setDefaultConnection('mysql');
        }
        
        $this->info("\n🎉 Base de datos del tenant configurada correctamente!");
        
        return 0;
    }
}
