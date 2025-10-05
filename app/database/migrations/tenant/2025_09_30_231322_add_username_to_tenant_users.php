<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Esta migración se ejecuta manualmente sobre cada base SQLite de los tenants
 * cuando se ejecute el comando tenant:setup-database o al aplicar migraciones posteriores.
 */

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('usuarios')) {
            return;
        }

        Schema::table('usuarios', function (Blueprint $table) {
            if (!Schema::hasColumn('usuarios', 'username')) {
                $table->string('username', 100)->unique()->default('')->after('email');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('usuarios')) {
            return;
        }

        if (Schema::hasColumn('usuarios', 'username')) {
            Schema::table('usuarios', function (Blueprint $table) {
                $table->dropColumn('username');
            });
        }
    }
};
