<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tenant;
use Illuminate\Support\Facades\File;

class InitialDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Iniciando seed de datos iniciales...');
        
        // Crear tenant demo
        $tenant = Tenant::create([
            'rut' => '000000000016',
            'nombre_comercial' => 'Demo Punto de Venta',
            'api_key' => 'test-api-key-demo',
            'estado' => 'activo',
            'sqlite_path' => storage_path('tenants/000000000016.sqlite'),
            'nombre_contacto' => 'Administrador Demo',
            'email_contacto' => 'demo@puntos.test',
            'telefono_contacto' => '099123456',
            'direccion_contacto' => 'Dirección Demo 9999, Montevideo',
            'formato_factura' => 'efactura',
            'facturas_recibidas' => 0,
            'puntos_generados_total' => 0,
        ]);
        
        $this->command->info("✅ Tenant demo creado: {$tenant->nombre_comercial}");
        $this->command->info("   RUT: {$tenant->rut}");
        $this->command->info("   API Key: {$tenant->api_key}");
        
        // Crear base SQLite del tenant (vacía por ahora)
        $sqlitePath = storage_path('tenants/000000000016.sqlite');
        if (!File::exists($sqlitePath)) {
            File::put($sqlitePath, '');
            $this->command->info("✅ Base SQLite creada: {$sqlitePath}");
        }
        
        $this->command->info('');
        $this->command->info('🎉 ¡Seed completado exitosamente!');
        $this->command->info('');
        $this->command->info('📌 Datos para probar el emulador de webhook:');
        $this->command->info('   URL: http://localhost:8000/api/webhook/ingest');
        $this->command->info('   RUT: 000000000016');
        $this->command->info('   API Key: test-api-key-demo');
    }
}
