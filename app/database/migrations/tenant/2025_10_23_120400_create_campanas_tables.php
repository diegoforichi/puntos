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
            $table->id();
            $table->string('canal', 20)->comment('whatsapp o email');
            $table->string('titulo', 255);
            $table->string('subtitulo', 255)->nullable();
            $table->string('imagen_url', 500)->nullable();
            $table->string('asunto_email', 255)->nullable();
            $table->text('cuerpo');
            $table->timestamp('fecha_programada')->nullable();
            $table->enum('estado', ['borrador', 'pendiente', 'enviando', 'completada', 'pausada'])->default('borrador');
            $table->unsignedInteger('total_destinatarios')->default(0);
            $table->unsignedInteger('total_enviados')->default(0);
            $table->unsignedInteger('total_fallidos')->default(0);
            $table->timestamps();

            $table->index('canal');
            $table->index('estado');
            $table->index('fecha_programada');
        });

        Schema::create('campana_envios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campana_id');
            $table->unsignedBigInteger('cliente_id');
            $table->enum('estado', ['pendiente', 'enviado', 'fallido'])->default('pendiente');
            $table->unsignedTinyInteger('intentos')->default(0);
            $table->text('error_mensaje')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->foreign('campana_id')->references('id')->on('campanas')->onDelete('cascade');
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');

            $table->index(['campana_id', 'estado']);
            $table->index('cliente_id');
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
