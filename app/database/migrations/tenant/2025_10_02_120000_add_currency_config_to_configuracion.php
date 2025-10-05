<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('configuracion')) {
            $now = now();

            $defaults = [
                [
                    'key' => 'moneda_base',
                    'value' => json_encode(['valor' => 'UYU']),
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'key' => 'tasa_usd',
                    'value' => json_encode(['valor' => 40.0]),
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'key' => 'moneda_desconocida',
                    'value' => json_encode(['valor' => 'omitir']),
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ];

            foreach ($defaults as $config) {
                $exists = DB::table('configuracion')->where('key', $config['key'])->exists();
                if (!$exists) {
                    DB::table('configuracion')->insert($config);
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('configuracion')) {
            DB::table('configuracion')->whereIn('key', [
                'moneda_base',
                'tasa_usd',
                'moneda_desconocida',
            ])->delete();
        }
    }
};


