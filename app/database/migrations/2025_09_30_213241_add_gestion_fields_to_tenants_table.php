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
        Schema::table('tenants', function (Blueprint $table) {
            $table->timestamp('ultimo_webhook')->nullable()->after('formato_factura');
            $table->unsignedBigInteger('facturas_recibidas')->default(0)->after('ultimo_webhook');
            $table->unsignedBigInteger('puntos_generados_total')->default(0)->after('facturas_recibidas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['ultimo_webhook', 'facturas_recibidas', 'puntos_generados_total']);
        });
    }
};
