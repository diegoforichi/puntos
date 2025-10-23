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
            $table->boolean('allow_custom_whatsapp')->default(false)->after('api_token_last_used_at');
            $table->boolean('allow_custom_email')->default(false)->after('allow_custom_whatsapp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['allow_custom_whatsapp', 'allow_custom_email']);
        });
    }
};
