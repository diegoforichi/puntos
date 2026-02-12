<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class CleanupInvalidClientPhones extends Command
{
    /**
     * Artisan signature.
     */
    protected $signature = 'puntos:limpiar-telefonos {tenant : RUT del tenant} {--numbers=099437448,43624675 : Lista de números (separados por coma) que se consideran inválidos} {--dry-run : Mostrar resultados sin modificar registros}';

    /**
     * Description.
     */
    protected $description = 'Limpia teléfonos inválidos de clientes dejándolos en NULL para que se actualicen con próximas compras.';

    public function handle(): int
    {
        $tenantRut = trim((string) $this->argument('tenant'));
        $numbers = collect(explode(',', (string) $this->option('numbers')))
            ->map(fn (string $value) => preg_replace('/\D/', '', $value))
            ->filter()
            ->values()
            ->all();
        $dryRun = (bool) $this->option('dry-run');

        if (empty($numbers)) {
            $this->error('Debe especificar al menos un número inválido mediante --numbers.');

            return self::FAILURE;
        }

        $tenant = Tenant::on('mysql')->where('rut', $tenantRut)->first();

        if (! $tenant) {
            $this->error("No se encontró el tenant con RUT {$tenantRut}.");

            return self::FAILURE;
        }

        $sqlitePath = $tenant->getSqlitePath();

        if (! file_exists($sqlitePath)) {
            $this->error("No se encontró la base SQLite del tenant ({$sqlitePath}).");

            return self::FAILURE;
        }

        Config::set('database.connections.tenant_temp', [
            'driver' => 'sqlite',
            'database' => $sqlitePath,
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]);
        DB::purge('tenant_temp');

        $this->line("Analizando clientes del tenant {$tenant->nombre_comercial} ({$tenant->rut})");
        $this->line('Números inválidos considerados: '.implode(', ', $numbers));

        $query = DB::connection('tenant_temp')
            ->table('clientes')
            ->select('id', 'documento', 'nombre', 'telefono')
            ->whereNotNull('telefono')
            ->where(function ($q) use ($numbers) {
                foreach ($numbers as $number) {
                    $q->orWhere('telefono', 'like', "%{$number}%");
                }
            });

        $totalAfectados = $query->count();

        if ($totalAfectados === 0) {
            $this->info('No se encontraron clientes con teléfonos que coincidan con los números provistos.');

            return self::SUCCESS;
        }

        $this->warn("Clientes afectados: {$totalAfectados}");

        if ($dryRun) {
            $muestra = $query->limit(10)->get();

            if ($muestra->isNotEmpty()) {
                $this->table(
                    ['ID', 'Documento', 'Nombre', 'Teléfono'],
                    $muestra->map(fn ($cliente) => Arr::only((array) $cliente, ['id', 'documento', 'nombre', 'telefono']))
                );
            }

            $this->comment('Dry-run finalizado. No se realizaron cambios.');

            return self::SUCCESS;
        }

        $actualizados = DB::connection('tenant_temp')
            ->table('clientes')
            ->whereNotNull('telefono')
            ->where(function ($q) use ($numbers) {
                foreach ($numbers as $number) {
                    $q->orWhere('telefono', 'like', "%{$number}%");
                }
            })
            ->update(['telefono' => null]);

        $this->info("Teléfonos limpiados: {$actualizados}");
        $this->comment('Los clientes quedarán sin teléfono y se actualizarán con la próxima compra.');

        return self::SUCCESS;
    }
}
