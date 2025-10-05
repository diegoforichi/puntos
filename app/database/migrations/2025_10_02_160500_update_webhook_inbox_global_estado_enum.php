<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('webhook_inbox_global')) {
            DB::statement("ALTER TABLE `webhook_inbox_global` MODIFY `estado` ENUM('pendiente','procesado','error','omitido') NOT NULL DEFAULT 'pendiente'");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('webhook_inbox_global')) {
            DB::statement("ALTER TABLE `webhook_inbox_global` MODIFY `estado` ENUM('pendiente','procesado','error') NOT NULL DEFAULT 'pendiente'");
        }
    }
};
