<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $tables = collect(DB::select("SELECT name FROM sqlite_master WHERE type='table'"))
            ->map(fn($row) => is_array($row) ? $row['name'] : $row->name)
            ->toArray();

        if (in_array('facturas', $tables, true)) {
            $colsFacturas = collect(DB::select("PRAGMA table_info('facturas')"))
                ->map(fn($col) => is_array($col) ? $col['name'] : $col->name)
                ->toArray();

            Schema::table('facturas', function (Blueprint $table) use ($colsFacturas) {
                if (!in_array('cfe_id', $colsFacturas, true)) {
                    $table->unsignedInteger('cfe_id')->nullable()->after('promocion_aplicada');
                }

                if (!in_array('acumulo', $colsFacturas, true)) {
                    $table->boolean('acumulo')->default(true)->after('cfe_id');
                }

                if (!in_array('motivo_no_acumulo', $colsFacturas, true)) {
                    $table->string('motivo_no_acumulo', 255)->nullable()->after('acumulo');
                }
            });
        }

        if (in_array('webhook_inbox', $tables, true)) {
            $colsWebhook = collect(DB::select("PRAGMA table_info('webhook_inbox')"))
                ->map(fn($col) => is_array($col) ? $col['name'] : $col->name)
                ->toArray();

            Schema::table('webhook_inbox', function (Blueprint $table) use ($colsWebhook) {
                if (!in_array('cfe_id', $colsWebhook, true)) {
                    $table->unsignedInteger('cfe_id')->nullable()->after('estado');
                }

                if (!in_array('documento_cliente', $colsWebhook, true)) {
                    $table->string('documento_cliente', 50)->nullable()->after('cfe_id');
                }

                if (!in_array('puntos_generados', $colsWebhook, true)) {
                    $table->decimal('puntos_generados', 10, 2)->default(0)->after('documento_cliente');
                }

                if (!in_array('motivo_no_acumulo', $colsWebhook, true)) {
                    $table->string('motivo_no_acumulo', 255)->nullable()->after('puntos_generados');
                }
            });
        }
    }

    public function down(): void
    {
        $tables = collect(DB::select("SELECT name FROM sqlite_master WHERE type='table'"))
            ->map(fn($row) => is_array($row) ? $row['name'] : $row->name)
            ->toArray();

        if (in_array('facturas', $tables, true)) {
            $colsFacturas = collect(DB::select("PRAGMA table_info('facturas')"))
                ->map(fn($col) => is_array($col) ? $col['name'] : $col->name)
                ->toArray();

            Schema::table('facturas', function (Blueprint $table) use ($colsFacturas) {
                if (in_array('motivo_no_acumulo', $colsFacturas, true)) {
                    $table->dropColumn('motivo_no_acumulo');
                }

                if (in_array('acumulo', $colsFacturas, true)) {
                    $table->dropColumn('acumulo');
                }

                if (in_array('cfe_id', $colsFacturas, true)) {
                    $table->dropColumn('cfe_id');
                }
            });
        }

        if (in_array('webhook_inbox', $tables, true)) {
            $colsWebhook = collect(DB::select("PRAGMA table_info('webhook_inbox')"))
                ->map(fn($col) => is_array($col) ? $col['name'] : $col->name)
                ->toArray();

            Schema::table('webhook_inbox', function (Blueprint $table) use ($colsWebhook) {
                if (in_array('motivo_no_acumulo', $colsWebhook, true)) {
                    $table->dropColumn('motivo_no_acumulo');
                }

                if (in_array('puntos_generados', $colsWebhook, true)) {
                    $table->dropColumn('puntos_generados');
                }

                if (in_array('documento_cliente', $colsWebhook, true)) {
                    $table->dropColumn('documento_cliente');
                }

                if (in_array('cfe_id', $colsWebhook, true)) {
                    $table->dropColumn('cfe_id');
                }
            });
        }
    }
};

