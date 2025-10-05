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
        Schema::create('webhook_inbox_global', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_rut', 20)->nullable()->comment('RUT del tenant');
            $table->enum('estado', ['pendiente', 'procesado', 'error'])->default('pendiente');
            $table->string('origen', 100)->nullable()->comment('Origen/adaptador usado');
            $table->integer('http_status')->nullable()->comment('Código HTTP de respuesta');
            $table->text('mensaje_error')->nullable()->comment('Mensaje de error si falla');
            $table->text('payload_json')->nullable()->comment('JSON recibido (primeros 5000 chars)');
            $table->unsignedInteger('cfe_id')->nullable()->comment('Tipo de comprobante');
            $table->string('documento_cliente', 50)->nullable()->comment('Documento del cliente en la factura');
            $table->decimal('puntos_generados', 10, 2)->nullable()->comment('Puntos que se generaron (o restaron)');
            $table->string('motivo_no_acumulo', 255)->nullable()->comment('Motivo cuando no se acumulan puntos');
            $table->timestamp('procesado_en')->nullable()->comment('Fecha de procesamiento exitoso');
            $table->timestamps();
            
            // Índices para búsquedas
            $table->index('tenant_rut');
            $table->index('estado');
            $table->index('cfe_id');
            $table->index('documento_cliente');
            $table->index('created_at');
            
            // Foreign key (opcional, permite NULL si el RUT no existe aún)
            $table->foreign('tenant_rut')->references('rut')->on('tenants')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_inbox_global');
    }
};
