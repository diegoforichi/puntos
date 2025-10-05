<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('webhook_inbox_global')) {
            Schema::table('webhook_inbox_global', function (Blueprint $table) {
                if (!Schema::hasColumn('webhook_inbox_global', 'cfe_id')) {
                    $table->unsignedInteger('cfe_id')->nullable()->after('payload_json');
                }
                if (!Schema::hasColumn('webhook_inbox_global', 'documento_cliente')) {
                    $table->string('documento_cliente', 50)->nullable()->after('cfe_id');
                }
                if (!Schema::hasColumn('webhook_inbox_global', 'puntos_generados')) {
                    $table->decimal('puntos_generados', 10, 2)->nullable()->after('documento_cliente');
                }
                if (!Schema::hasColumn('webhook_inbox_global', 'motivo_no_acumulo')) {
                    $table->string('motivo_no_acumulo', 255)->nullable()->after('puntos_generados');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('webhook_inbox_global')) {
            Schema::table('webhook_inbox_global', function (Blueprint $table) {
                if (Schema::hasColumn('webhook_inbox_global', 'motivo_no_acumulo')) {
                    $table->dropColumn('motivo_no_acumulo');
                }
                if (Schema::hasColumn('webhook_inbox_global', 'puntos_generados')) {
                    $table->dropColumn('puntos_generados');
                }
                if (Schema::hasColumn('webhook_inbox_global', 'documento_cliente')) {
                    $table->dropColumn('documento_cliente');
                }
                if (Schema::hasColumn('webhook_inbox_global', 'cfe_id')) {
                    $table->dropColumn('cfe_id');
                }
            });
        }
    }
};



