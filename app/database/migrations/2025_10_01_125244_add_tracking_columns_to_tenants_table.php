<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (!Schema::hasColumn('tenants', 'ultima_migracion')) {
                $table->timestamp('ultima_migracion')->nullable()->after('ultimo_webhook');
            }

            if (!Schema::hasColumn('tenants', 'ultima_respaldo')) {
                $table->timestamp('ultima_respaldo')->nullable()->after('ultima_migracion');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (Schema::hasColumn('tenants', 'ultima_respaldo')) {
                $table->dropColumn('ultima_respaldo');
            }

            if (Schema::hasColumn('tenants', 'ultima_migracion')) {
                $table->dropColumn('ultima_migracion');
            }
        });
    }
};
