<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campanas', function (Blueprint $table) {
            if (! Schema::hasColumn('campanas', 'tenant_id')) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
                $table->index('tenant_id');
            }

            if (! Schema::hasColumn('campanas', 'mensaje_whatsapp')) {
                $table->text('mensaje_whatsapp')->nullable()->after('cuerpo_texto');
            }
        });

        Schema::table('campana_envios', function (Blueprint $table) {
            if (! Schema::hasColumn('campana_envios', 'canal')) {
                $table->string('canal', 20)->default('whatsapp')->after('cliente_id');
                $table->index('canal');
            }
        });
    }

    public function down(): void
    {
        // Nota: Las versiones antiguas de SQLite no soportan dropColumn de forma nativa.
        Schema::table('campanas', function (Blueprint $table) {
            if (Schema::hasColumn('campanas', 'mensaje_whatsapp')) {
                $table->dropColumn('mensaje_whatsapp');
            }
        });
    }
};
