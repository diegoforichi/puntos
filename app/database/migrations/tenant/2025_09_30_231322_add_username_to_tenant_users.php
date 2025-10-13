<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Esta migración se ejecuta manualmente sobre cada base SQLite de los tenants
 * cuando se ejecute el comando tenant:setup-database o al aplicar migraciones posteriores.
 */

return new class extends Migration
{
    public function up(): void
    {
        $tables = collect(DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name='usuarios'"))
            ->map(fn($row) => is_array($row) ? $row['name'] : $row->name)
            ->toArray();

        if (!in_array('usuarios', $tables, true)) {
            return;
        }

        $cols = collect(DB::select("PRAGMA table_info('usuarios')"))
            ->map(fn($col) => is_array($col) ? $col['name'] : $col->name)
            ->toArray();

        if (!in_array('username', $cols, true)) {
            Schema::table('usuarios', function (Blueprint $table) {
                $table->string('username', 100)->unique()->default('')->after('email');
            });
        }
    }

    public function down(): void
    {
        $tables = collect(DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name='usuarios'"))
            ->map(fn($row) => is_array($row) ? $row['name'] : $row->name)
            ->toArray();

        if (!in_array('usuarios', $tables, true)) {
            return;
        }

        $cols = collect(DB::select("PRAGMA table_info('usuarios')"))
            ->map(fn($col) => is_array($col) ? $col['name'] : $col->name)
            ->toArray();

        if (in_array('username', $cols, true)) {
            Schema::table('usuarios', function (Blueprint $table) {
                $table->dropColumn('username');
            });
        }
    }
};
