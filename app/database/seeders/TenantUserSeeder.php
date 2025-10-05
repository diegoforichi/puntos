<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Tenant;

/**
 * Seeder para crear usuarios de prueba en el tenant demo
 * 
 * Crea 3 usuarios con diferentes roles:
 * - Admin: acceso completo
 * - Supervisor: canjear puntos y modificar config
 * - Operario: solo consulta
 */
class TenantUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Buscar tenant demo
        $tenant = Tenant::where('rut', '000000000016')->first();

        if (!$tenant) {
            $this->command->error('Tenant demo no encontrado. Ejecuta InitialDataSeeder primero.');
            return;
        }

        // Verificar que existe la base SQLite
        $sqlitePath = $tenant->getSqlitePath();
        if (!file_exists($sqlitePath)) {
            $this->command->error('Base SQLite del tenant no encontrada en: ' . $sqlitePath);
            $this->command->info('Ejecuta: php artisan tenant:setup-database 000000000016');
            return;
        }

        // Configurar conexión al tenant
        config([
            'database.connections.tenant' => [
                'driver' => 'sqlite',
                'database' => $sqlitePath,
                'prefix' => '',
                'foreign_key_constraints' => true,
            ]
        ]);

        DB::purge('tenant');
        DB::setDefaultConnection('tenant');

        // Verificar si ya existen usuarios
        $existingUsers = DB::table('usuarios')->count();
        
        if ($existingUsers > 0) {
            $this->command->warn('Ya existen ' . $existingUsers . ' usuario(s) en el tenant demo.');
            
            if (!$this->command->confirm('¿Deseas eliminarlos y crear nuevos usuarios de prueba?', false)) {
                $this->command->info('Operación cancelada.');
                DB::setDefaultConnection('mysql');
                return;
            }
            
            DB::table('usuarios')->delete();
            $this->command->info('Usuarios existentes eliminados.');
        }

        // Crear usuarios de prueba
        $passwords = config('tenant.default_passwords');

        $usuarios = [
            [
                'nombre' => 'Administrador Demo',
                'email' => 'admin@demo.com',
                'username' => 'admin',
                'password' => Hash::make($passwords['admin']),
                'rol' => 'admin',
                'activo' => 1,
                'ultimo_acceso' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Supervisor Demo',
                'email' => 'supervisor@demo.com',
                'username' => 'supervisor',
                'password' => Hash::make($passwords['supervisor']),
                'rol' => 'supervisor',
                'activo' => 1,
                'ultimo_acceso' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Operario Demo',
                'email' => 'operario@demo.com',
                'username' => 'operario',
                'password' => Hash::make($passwords['operario']),
                'rol' => 'operario',
                'activo' => 1,
                'ultimo_acceso' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($usuarios as $usuario) {
            DB::table('usuarios')->insert($usuario);
        }

        // Restaurar conexión por defecto
        DB::setDefaultConnection('mysql');

        $this->command->info('✅ Usuarios de prueba creados exitosamente en tenant demo (RUT: 000000000016)');
        $this->command->info('');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('📋 CREDENCIALES DE ACCESO:');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('');
        $this->command->info('👤 ADMINISTRADOR:');
        $this->command->info('   Email: admin@demo.com');
        $this->command->info('   Contraseña: ' . $passwords['admin']);
        $this->command->info('');
        $this->command->info('👤 SUPERVISOR:');
        $this->command->info('   Email: supervisor@demo.com');
        $this->command->info('   Contraseña: ' . $passwords['supervisor']);
        $this->command->info('');
        $this->command->info('👤 OPERARIO:');
        $this->command->info('   Email: operario@demo.com');
        $this->command->info('   Contraseña: ' . $passwords['operario']);
        $this->command->info('');
        $this->command->info('🔗 URL de acceso: http://localhost:8000/000000000016/login');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    }
}
