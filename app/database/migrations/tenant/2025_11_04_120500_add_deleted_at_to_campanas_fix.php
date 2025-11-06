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
        // Usa la conexión activa del comando tenant:migrate (tenant_temp)
        Schema::table('campanas', function (Blueprint $table) {
            if (! Schema::hasColumn('campanas', 'deleted_at')) {
                $table->timestamp('deleted_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campanas', function (Blueprint $table) {
            if (Schema::hasColumn('campanas', 'deleted_at')) {
                $table->dropColumn('deleted_at');
            }
        });
    }
};
