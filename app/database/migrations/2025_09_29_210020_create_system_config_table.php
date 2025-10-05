<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('system_config', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique()->comment('Clave de configuración');
            $table->text('value')->nullable()->comment('Valor en JSON');
            $table->string('description', 500)->nullable()->comment('Descripción del parámetro');
            $table->timestamps();
            
            // Índice para búsquedas rápidas
            $table->index('key');
        });
        
        // Insertar configuración inicial
        DB::table('system_config')->insert([
            [
                'key' => 'whatsapp',
                'value' => json_encode([
                    'activo' => false,
                    'token' => '',
                    'url' => 'https://6023-39939.el-alt.com/monitorwappapi/api/message/sendMessageAndUrlDocument',
                    'codigo_pais' => '+598'
                ]),
                'description' => 'Configuración del servicio WhatsApp',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'email',
                'value' => json_encode([
                    'smtp_host' => 'smtp.gmail.com',
                    'smtp_port' => 587,
                    'smtp_user' => '',
                    'smtp_pass' => '',
                    'from_address' => 'sistema@tudominio.com',
                    'from_name' => 'Sistema de Puntos'
                ]),
                'description' => 'Configuración del servicio Email',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'retencion_datos',
                'value' => json_encode([
                    'años' => 1,
                    'tablas' => ['puntos_canjeados', 'puntos_vencidos', 'actividades', 'whatsapp_logs', 'facturas']
                ]),
                'description' => 'Política de retención de datos históricos',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_config');
    }
};
