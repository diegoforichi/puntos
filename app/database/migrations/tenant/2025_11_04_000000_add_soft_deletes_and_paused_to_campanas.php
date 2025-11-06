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
        // No especificar conexión - usar la conexión activa del comando migrate
        Schema::table('campanas', function (Blueprint $table) {
            // Agregar soft deletes para mantener historial de campañas eliminadas
            if (! Schema::hasColumn('campanas', 'deleted_at')) {
                $table->softDeletes();
            }

            // Agregar índice para mejorar consultas que filtren por estado
            // Verificar si el índice ya existe antes de crearlo
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $sm->listTableIndexes('campanas');
            $hasEstadoIndex = false;
            foreach ($indexes as $index) {
                if (in_array('estado', $index->getColumns())) {
                    $hasEstadoIndex = true;
                    break;
                }
            }
            if (! $hasEstadoIndex) {
                $table->index('estado');
            }
        });

        // Nota: El estado 'pausada' se manejará a nivel de aplicación
        // sin necesidad de modificar el ENUM en la base de datos
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campanas', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropIndex(['estado']);
        });
    }
};
