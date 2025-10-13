<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migración para crear todas las tablas SQLite de un tenant
 * Se ejecuta cuando se crea un nuevo tenant
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tabla de clientes
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('documento', 20)->unique()->comment('Cédula o RUT del cliente');
            $table->string('nombre', 255)->comment('Nombre completo del cliente');
            $table->string('telefono', 50)->nullable()->comment('Teléfono para WhatsApp');
            $table->string('email', 255)->nullable();
            $table->string('direccion', 500)->nullable();
            $table->decimal('puntos_acumulados', 10, 2)->default(0)->comment('Puntos disponibles actuales');
            $table->timestamp('ultima_actividad')->nullable()->comment('Última factura o canje');
            $table->timestamps();
            
            $table->index('documento');
            $table->index('puntos_acumulados');
            $table->index('ultima_actividad');
        });
        
        // Tabla de facturas de referencia (solo puntos activos)
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id');
            $table->string('numero_factura', 100)->comment('Número de la factura');
            $table->decimal('monto_total', 10, 2)->comment('Monto total de la factura');
            $table->string('moneda', 10)->default('UYU');
            $table->decimal('puntos_generados', 10, 2)->comment('Puntos generados por esta factura');
            $table->string('promocion_aplicada', 100)->nullable()->comment('Nombre de promoción si aplica');
            $table->unsignedInteger('cfe_id')->nullable()->comment('Tipo de comprobante según DGI');
            $table->boolean('acumulo')->default(true)->comment('Indica si generó puntos');
            $table->string('motivo_no_acumulo', 255)->nullable()->comment('Motivo cuando no genera puntos');
            $table->text('payload_json')->nullable()->comment('JSON original de la factura');
            $table->timestamp('fecha_emision')->comment('Fecha de emisión de la factura');
            $table->timestamp('fecha_vencimiento')->nullable()->comment('Fecha de vencimiento de puntos');
            $table->timestamps();
            
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
            $table->index('cliente_id');
            $table->index('fecha_emision');
            $table->index('fecha_vencimiento');
            $table->index('cfe_id');
            $table->index('acumulo');
        });
        
        // Tabla de puntos canjeados (histórico)
        Schema::create('puntos_canjeados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id');
            $table->decimal('puntos_canjeados', 10, 2);
            $table->decimal('puntos_restantes', 10, 2)->comment('Puntos que quedaron después del canje');
            $table->string('concepto', 500)->nullable()->comment('Descripción del canje');
            $table->string('autorizado_por', 100)->nullable()->comment('Usuario que autorizó');
            $table->timestamps();
            
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
            $table->index('cliente_id');
            $table->index('created_at');
        });
        
        // Tabla de puntos vencidos (histórico)
        Schema::create('puntos_vencidos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id');
            $table->decimal('puntos_vencidos', 10, 2);
            $table->string('motivo', 255)->default('Vencimiento automático');
            $table->timestamps();
            
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
            $table->index('cliente_id');
            $table->index('created_at');
        });
        
        // Tabla de configuración del tenant
        Schema::create('configuracion', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable()->comment('Valor en JSON');
            $table->timestamps();
        });
        
        // Configuración por defecto (usar la conexión activa de la migración)
        DB::table('configuracion')->insert([
            [
                'key' => 'puntos_por_pesos',
                'value' => json_encode(['valor' => 100]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'dias_vencimiento',
                'value' => json_encode(['valor' => 180]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'acumulacion_excluir_efacturas',
                'value' => json_encode(['valor' => false]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        
        // Tabla de promociones
        Schema::create('promociones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 255);
            $table->text('descripcion')->nullable();
            $table->enum('tipo', ['descuento', 'bonificacion', 'multiplicador'])->default('multiplicador');
            $table->decimal('valor', 10, 2)->comment('Monto fijo, porcentaje extra o factor multiplicador');
            $table->text('condiciones')->nullable()->comment('Condiciones en JSON: monto_minimo, dias_semana, etc');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->unsignedTinyInteger('prioridad')->default(50);
            $table->boolean('activa')->default(true);
            $table->timestamps();

            $table->index('activa');
            $table->index(['fecha_inicio', 'fecha_fin']);
            $table->index('prioridad');
        });
        
        // Tabla de usuarios del tenant
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 255);
            $table->string('email', 255)->nullable()->unique();
            $table->string('username', 100)->unique();
            $table->string('password');
            $table->enum('rol', ['admin', 'supervisor', 'operario'])->default('operario');
            $table->boolean('activo')->default(true);
            $table->timestamp('ultimo_acceso')->nullable();
            $table->timestamps();
            
            $table->index('email');
            $table->index('rol');
            $table->index('activo');
        });
        
        // Tabla de actividades (log de acciones)
        Schema::create('actividades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->string('accion', 100)->comment('Tipo de acción: canje, configuracion, etc');
            $table->text('descripcion')->nullable();
            $table->text('datos_json')->nullable()->comment('Datos adicionales en JSON');
            $table->timestamps();
            
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('set null');
            $table->index('accion');
            $table->index('created_at');
        });
        
        // Tabla de webhook inbox (log local del tenant)
        Schema::create('webhook_inbox', function (Blueprint $table) {
            $table->id();
            $table->enum('estado', ['pendiente', 'procesado', 'error', 'omitido'])->default('pendiente');
            $table->unsignedInteger('cfe_id')->nullable();
            $table->string('documento_cliente', 50)->nullable();
            $table->decimal('puntos_generados', 10, 2)->default(0);
            $table->string('motivo_no_acumulo', 255)->nullable();
            $table->string('origen', 100)->nullable();
            $table->text('mensaje_error')->nullable();
            $table->text('payload_json')->nullable();
            $table->timestamp('procesado_en')->nullable();
            $table->timestamps();
            
            $table->index('estado');
            $table->index('cfe_id');
            $table->index('documento_cliente');
            $table->index('created_at');
        });
        
        // Tabla de logs de WhatsApp
        Schema::create('whatsapp_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->string('numero', 20);
            $table->string('evento', 50)->comment('Tipo de notificación');
            $table->text('mensaje');
            $table->enum('estado', ['enviado', 'fallido', 'pendiente'])->default('pendiente');
            $table->string('codigo_respuesta', 50)->nullable();
            $table->text('error_mensaje')->nullable();
            $table->timestamps();
            
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('set null');
            $table->index('cliente_id');
            $table->index('estado');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_logs');
        Schema::dropIfExists('webhook_inbox');
        Schema::dropIfExists('actividades');
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('promociones');
        Schema::dropIfExists('puntos_vencidos');
        Schema::dropIfExists('puntos_canjeados');
        Schema::dropIfExists('facturas');
        Schema::dropIfExists('clientes');
        Schema::dropIfExists('configuracion');
    }
};
