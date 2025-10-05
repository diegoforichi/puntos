<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('rut', 20)->unique()->comment('RUT del comercio/tenant');
            $table->string('nombre_comercial', 255)->comment('Nombre comercial del negocio');
            $table->string('api_key', 100)->unique()->comment('API Key para webhook');
            $table->enum('estado', ['activo', 'suspendido', 'eliminado'])->default('activo');
            $table->string('sqlite_path', 500)->comment('Ruta al archivo SQLite del tenant');
            
            // Datos de contacto
            $table->string('nombre_contacto', 255)->nullable();
            $table->string('email_contacto', 255)->nullable();
            $table->string('telefono_contacto', 50)->nullable();
            $table->string('direccion_contacto', 500)->nullable();
            
            // Configuración de webhook
            $table->string('formato_factura', 50)->default('efactura')->comment('Adaptador a usar: efactura, factupronto, etc');
            
            $table->timestamp('ultimo_webhook')->nullable();
            $table->timestamp('ultima_migracion')->nullable();
            $table->timestamp('ultima_respaldo')->nullable();
            $table->timestamps();
            $table->softDeletes()->comment('Fecha de eliminación lógica');
            
            // Índices
            $table->index('estado');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
