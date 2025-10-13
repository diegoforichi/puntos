<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $cols = collect(DB::select("PRAGMA table_info('promociones')"))
            ->map(fn($col) => is_array($col) ? $col['name'] : $col->name)
            ->toArray();

        if (!in_array('descripcion', $cols, true)) {
            Schema::table('promociones', function (Blueprint $table) {
                $table->text('descripcion')->nullable()->after('nombre');
            });
        }

        if (in_array('condicion', $cols, true) && !in_array('condiciones', $cols, true)) {
            Schema::table('promociones', function (Blueprint $table) {
                $table->renameColumn('condicion', 'condiciones');
            });
        }

        if (!in_array('prioridad', $cols, true)) {
            Schema::table('promociones', function (Blueprint $table) {
                $table->unsignedTinyInteger('prioridad')->default(50)->after('fecha_fin');
            });
        }

        // Ajustar valores de tipo antiguos
        DB::table('promociones')->update([
            'tipo' => DB::raw("CASE 
                WHEN tipo = 'puntos_extra' THEN 'bonificacion'
                WHEN tipo = 'descuento_canje' THEN 'descuento'
                ELSE tipo
            END")
        ]);

        // Asegurar que el campo condiciones sea JSON válido
        $promos = DB::table('promociones')->select('id', 'condiciones')->get();
        foreach ($promos as $promo) {
            if (!empty($promo->condiciones) && !self::isJson($promo->condiciones)) {
                DB::table('promociones')
                    ->where('id', $promo->id)
                    ->update(['condiciones' => json_encode(['raw' => $promo->condiciones])]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promociones', function (Blueprint $table) {
            if (Schema::hasColumn('promociones', 'descripcion')) {
                $table->dropColumn('descripcion');
            }

            if (Schema::hasColumn('promociones', 'condiciones')) {
                $table->renameColumn('condiciones', 'condicion');
            }

            if (Schema::hasColumn('promociones', 'prioridad')) {
                $table->dropColumn('prioridad');
            }
        });
    }

    private static function isJson($string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
};

