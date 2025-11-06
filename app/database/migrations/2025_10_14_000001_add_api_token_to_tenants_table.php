<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (! Schema::hasColumn('tenants', 'api_token')) {
                $table->string('api_token', 80)->nullable()->after('api_key');
            }

            if (! Schema::hasColumn('tenants', 'api_token_last_used_at')) {
                $table->timestamp('api_token_last_used_at')->nullable()->after('api_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (Schema::hasColumn('tenants', 'api_token_last_used_at')) {
                $table->dropColumn('api_token_last_used_at');
            }

            if (Schema::hasColumn('tenants', 'api_token')) {
                $table->dropColumn('api_token');
            }
        });
    }
};
