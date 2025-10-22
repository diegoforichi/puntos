<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\Configuracion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Throwable;

class MigrateInvoicesFromCsv extends Command
{
    protected $signature = 'puntos:migrar-facturas
        {--tenant= : RUT del tenant}
        {--csv= : Ruta del archivo CSV}
        {--dry-run : Solo validar, no escribir en la base}
        {--override-validity-days= : Forzar días de vencimiento}';

    protected $description = 'Migrar facturas históricas desde un CSV a la base SQLite del tenant';

    public function handle(): int
    {
        $tenantRut = $this->option('tenant') ?? $this->ask('RUT del tenant');
        $csvPath = $this->option('csv') ?? $this->ask('Ruta del CSV');
        $dryRun = $this->option('dry-run');
        $diasOverride = $this->option('override-validity-days');

        $tenant = Tenant::where('rut', $tenantRut)->first();
        if (!$tenant) {
            $this->error("No se encontró el tenant {$tenantRut}");
            return self::FAILURE;
        }

        $sqlitePath = $tenant->getSqlitePath();
        if (!File::exists($sqlitePath)) {
            $this->error('No se encuentra la base SQLite del tenant.');
            return self::FAILURE;
        }

        if (!File::exists($csvPath)) {
            $this->error("No existe el archivo CSV {$csvPath}");
            return self::FAILURE;
        }

        $this->info("Procesando {$csvPath} para tenant {$tenant->nombre_comercial} ({$tenant->rut})");

        $this->configurarConexionTenant($sqlitePath);

        $diasVencimiento = $diasOverride
            ? (int) $diasOverride
            : (int) Configuracion::get('dias_vencimiento', 180);

        $stats = [
            'total' => 0,
            'acumulan' => 0,
            'excluidas' => 0,
            'errores' => 0,
        ];

        $filas = [];
        $errores = [];

        try {
            $file = new \SplFileObject($csvPath, 'r');
            $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);
            $headers = [];
            foreach ($file as $index => $row) {
                if ($row === [null] || $row === false) {
                    continue;
                }

                if ($index === 0) {
                    $headers = $this->normalizarHeaders($row);
                    continue;
                }

                $registro = $this->combinarFila($headers, $row);
                if ($registro === null) {
                    continue;
                }

                $stats['total']++;

                $documento = trim($registro['DocumentoCliente'] ?? '');
                if ($documento === '') {
                    $stats['errores']++;
                    $errores[] = "Fila {$stats['total']}: Documento vacío";
                    continue;
                }

                $esRut = strlen(preg_replace('/\D/', '', $documento)) > 8;
                $permitido = !$esRut;

                $filas[] = [
                    'documento' => $documento,
                    'nombre' => trim($registro['NombreCliente'] ?? ''),
                    'telefono' => trim($registro['TelefonoCliente'] ?? ''),
                    'numero' => trim($registro['NumeroFactura'] ?? ''),
                    'moneda' => trim($registro['MonedaFactura'] ?? 'UYU'),
                    'importe' => (float) str_replace(',', '.', $registro['ImporteFactura'] ?? 0),
                    'puntos' => (float) str_replace(',', '.', $registro['PuntosGenerados'] ?? 0),
                    'fecha' => $this->parseFecha($registro['__ultima_columna__'] ?? null),
                    'permitido' => $permitido,
                ];

                $permitido ? $stats['acumulan']++ : $stats['excluidas']++;
            }
        } catch (Throwable $e) {
            $this->error('No se pudo procesar el CSV: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->table(
            ['Total filas', 'Acumulan', 'Excluidas (e-factura)', 'Errores'],
            [[
                $stats['total'],
                $stats['acumulan'],
                $stats['excluidas'],
                $stats['errores'],
            ]]
        );

        if ($stats['errores'] && $dryRun) {
            $this->warn('Se encontraron errores en el CSV:');
            foreach (array_slice($errores, 0, 10) as $error) {
                $this->line(" - {$error}");
            }
        }

        if ($dryRun) {
            $this->info('Dry run completado. No se escribieron datos.');
            return self::SUCCESS;
        }

        DB::connection('tenant')->transaction(function () use ($filas, $diasVencimiento) {
            foreach ($filas as $fila) {
                $fechaEmision = ($fila['fecha'] ?? now())->copy();
                $fechaVencimiento = $fechaEmision->copy()->addDays($diasVencimiento);

                $cliente = DB::connection('tenant')->table('clientes')->where('documento', $fila['documento'])->first();
                if (!$cliente) {
                    $clienteId = DB::connection('tenant')->table('clientes')->insertGetId([
                        'documento' => $fila['documento'],
                        'nombre' => $fila['nombre'] ?: 'Cliente',
                        'telefono' => $fila['telefono'] ?: null,
                        'puntos_acumulados' => 0,
                        'ultima_actividad' => $fechaEmision->toDateTimeString(),
                        'created_at' => $fechaEmision->toDateTimeString(),
                        'updated_at' => $fechaEmision->toDateTimeString(),
                    ]);
                    $cliente = DB::connection('tenant')->table('clientes')->find($clienteId);
                }

                $existe = DB::connection('tenant')->table('facturas')
                    ->where('cliente_id', $cliente->id)
                    ->where('numero_factura', $fila['numero'])
                    ->exists();

                if ($existe) {
                    continue;
                }

                $puntos = $fila['permitido'] ? $fila['puntos'] : 0;
                $motivoNoAcumulo = $fila['permitido'] ? null : 'excluir_efacturas';

                DB::connection('tenant')->table('facturas')->insert([
                    'cliente_id' => $cliente->id,
                    'numero_factura' => $fila['numero'],
                    'monto_total' => $fila['importe'],
                    'moneda' => $fila['moneda'] ?: 'UYU',
                    'puntos_generados' => $puntos,
                    'promocion_aplicada' => null,
                    'cfe_id' => $fila['permitido'] ? 101 : 111,
                    'acumulo' => $fila['permitido'],
                    'motivo_no_acumulo' => $motivoNoAcumulo,
                    'fecha_emision' => $fechaEmision->toDateTimeString(),
                    'fecha_vencimiento' => $fechaVencimiento->toDateTimeString(),
                    'created_at' => $fechaEmision->toDateTimeString(),
                    'updated_at' => $fechaEmision->toDateTimeString(),
                ]);

                if ($fila['permitido'] && $puntos > 0) {
                    DB::connection('tenant')->table('clientes')
                        ->where('id', $cliente->id)
                        ->update([
                            'puntos_acumulados' => DB::raw('puntos_acumulados + ' . $puntos),
                            'ultima_actividad' => $fechaEmision->toDateTimeString(),
                            'updated_at' => $fechaEmision->toDateTimeString(),
                        ]);
                }
            }
        });

        $this->info('Migración completada correctamente.');
        return self::SUCCESS;
    }

    private function configurarConexionTenant(string $sqlitePath): void
    {
        config([
            'database.connections.tenant' => [
                'driver' => 'sqlite',
                'database' => $sqlitePath,
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
        ]);
    }

    private function parseFecha(?string $valor)
    {
        if (!$valor) {
            return now();
        }

        $valor = trim($valor);

        // El formato del CSV suele venir como: Mon Sep 29 2025 16:16:54 GMT-0300 (hora estándar de Uruguay)
        // Eliminamos cualquier texto entre paréntesis al final para que PHP pueda parsearlo.
        $valorSinParentesis = preg_replace('/\s*\(.*\)$/', '', $valor);

        $formatos = [
            'D M d Y H:i:s \G\M\T O', // Mon Sep 29 2025 16:16:54 GMT-0300
            'Y-m-d H:i:s',
            'Y-m-d',
            'd/m/Y',
            'd/m/Y H:i:s',
        ];

        foreach ($formatos as $formato) {
            $fecha = \DateTime::createFromFormat($formato, $valorSinParentesis);
            if ($fecha !== false) {
                return \Carbon\Carbon::instance($fecha)->setTimezone('America/Montevideo');
            }
        }

        // Intento final con strtotime (por si el formato cambia levemente pero sigue en inglés)
        $timestamp = strtotime($valorSinParentesis);
        if ($timestamp !== false) {
            return \Carbon\Carbon::createFromTimestamp($timestamp, 'America/Montevideo');
        }

        return now();
    }

    private function normalizarHeaders(array $headers): array
    {
        $resultado = [];
        $ultimoIndice = count($headers) - 1;

        foreach ($headers as $idx => $header) {
            $header = trim((string) $header);
            if ($header === '' && $idx === $ultimoIndice) {
                $header = '__ultima_columna__';
            }
            $resultado[$idx] = $header !== '' ? $header : "columna_{$idx}";
        }

        return $resultado;
    }

    private function combinarFila(array $headers, array $row): ?array
    {
        if (empty($row) || $row === [null]) {
            return null;
        }

        $row = array_pad($row, count($headers), null);
        $assoc = [];
        foreach ($headers as $idx => $header) {
            $assoc[$header] = $row[$idx] ?? null;
        }

        return $assoc;
    }
}
