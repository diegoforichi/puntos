<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('facturas')) {
            Schema::table('facturas', function (Blueprint $table) {
                if (!Schema::hasColumn('facturas', 'cfe_id')) {
                    $table->unsignedInteger('cfe_id')->nullable()->after('promocion_aplicada');
                }

                if (!Schema::hasColumn('facturas', 'acumulo')) {
                    $table->boolean('acumulo')->default(true)->after('cfe_id');
                }

                if (!Schema::hasColumn('facturas', 'motivo_no_acumulo')) {
                    $table->string('motivo_no_acumulo', 255)->nullable()->after('acumulo');
                }
            });
        }

        if (Schema::hasTable('webhook_inbox')) {
            Schema::table('webhook_inbox', function (Blueprint $table) {
                if (!Schema::hasColumn('webhook_inbox', 'cfe_id')) {
                    $table->unsignedInteger('cfe_id')->nullable()->after('estado');
                }

                if (!Schema::hasColumn('webhook_inbox', 'documento_cliente')) {
                    $table->string('documento_cliente', 50)->nullable()->after('cfe_id');
                }

                if (!Schema::hasColumn('webhook_inbox', 'puntos_generados')) {
                    $table->decimal('puntos_generados', 10, 2)->default(0)->after('documento_cliente');
                }

                if (!Schema::hasColumn('webhook_inbox', 'motivo_no_acumulo')) {
                    $table->string('motivo_no_acumulo', 255)->nullable()->after('puntos_generados');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('facturas')) {
            Schema::table('facturas', function (Blueprint $table) {
                if (Schema::hasColumn('facturas', 'motivo_no_acumulo')) {
                    $table->dropColumn('motivo_no_acumulo');
                }

                if (Schema::hasColumn('facturas', 'acumulo')) {
                    $table->dropColumn('acumulo');
                }

                if (Schema::hasColumn('facturas', 'cfe_id')) {
                    $table->dropColumn('cfe_id');
                }
            });
        }

        if (Schema::hasTable('webhook_inbox')) {
            Schema::table('webhook_inbox', function (Blueprint $table) {
                if (Schema::hasColumn('webhook_inbox', 'motivo_no_acumulo')) {
                    $table->dropColumn('motivo_no_acumulo');
                }

                if (Schema::hasColumn('webhook_inbox', 'puntos_generados')) {
                    $table->dropColumn('puntos_generados');
                }

                if (Schema::hasColumn('webhook_inbox', 'documento_cliente')) {
                    $table->dropColumn('documento_cliente');
                }

                if (Schema::hasColumn('webhook_inbox', 'cfe_id')) {
                    $table->dropColumn('cfe_id');
                }
            });
        }
    }
};

