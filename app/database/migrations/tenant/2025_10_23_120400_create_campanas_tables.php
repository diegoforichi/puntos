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
        Schema::create('campanas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('canal', 20)->comment('whatsapp,email,ambos');
            $table->string('tipo_envio', 20)->default('todos')->comment('todos,activos,inactivos');
            $table->string('titulo', 255);
            $table->string('subtitulo', 255)->nullable();
            $table->string('imagen_url', 500)->nullable();
            $table->string('asunto_email', 255)->nullable();
            $table->text('cuerpo_texto');
            $table->text('mensaje_whatsapp')->nullable();
            $table->timestamp('fecha_programada')->nullable();
            $table->string('estado', 20)->default('borrador');
            $table->json('totales')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('canal');
            $table->index('estado');
            $table->index('fecha_programada');
        });

        Schema::create('campana_envios', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('campana_id');
            $table->unsignedBigInteger('cliente_id');
            $table->string('canal', 20)->default('whatsapp');
            $table->enum('estado', ['pendiente', 'enviado', 'fallido'])->default('pendiente');
            $table->unsignedTinyInteger('intentos')->default(0);
            $table->text('error_mensaje')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->foreign('campana_id')->references('id')->on('campanas')->onDelete('cascade');
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');

            $table->index(['campana_id', 'estado']);
            $table->index('cliente_id');
            $table->index('canal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campana_envios');
        Schema::dropIfExists('campanas');
    }
};
