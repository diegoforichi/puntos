<?php

require __DIR__ . '/../app/vendor/autoload.php';

$app = require __DIR__ . '/../app/bootstrap/app.php';

/** @var \Illuminate\Contracts\Console\Kernel $kernel */
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$rut = $argv[1] ?? '000000000016';
$section = $argv[2] ?? 'facturas';

$tenant = Tenant::where('rut', $rut)->first();
if (!$tenant) {
    fwrite(STDERR, "TENANT NO ENCONTRADO: {$rut}" . PHP_EOL);
    exit(1);
}

config([
    'database.connections.tenant' => [
        'driver' => 'sqlite',
        'database' => $tenant->getSqlitePath(),
        'prefix' => '',
        'foreign_key_constraints' => false,
    ],
]);

DB::purge('tenant');

if (in_array($section, ['facturas', 'clientes', 'webhook'], true)) {
    $columns = Schema::connection('tenant')->getColumnListing($section === 'webhook' ? 'webhook_inbox' : $section);
    fwrite(STDERR, sprintf("Columnas %s: %s" . PHP_EOL, $section, implode(', ', $columns)));
}

switch ($section) {
    case 'facturas':
        $rows = DB::connection('tenant')
            ->table('facturas')
            ->orderByDesc('id')
            ->limit(10)
            ->get([
                'id',
                'numero_factura',
                'cfe_id',
                'monto_total',
                'puntos_generados',
                'acumulo',
                'motivo_no_acumulo',
                'created_at',
            ]);
        break;

    case 'clientes':
        $rows = DB::connection('tenant')
            ->table('clientes')
            ->orderByDesc('id')
            ->limit(10)
            ->get([
                'id',
                'documento',
                'nombre',
                'puntos_acumulados',
                'ultima_actividad',
            ]);
        break;

    case 'webhook':
        $rows = DB::connection('tenant')
            ->table('webhook_inbox')
            ->orderByDesc('id')
            ->limit(10)
            ->get([
                'id',
                'estado',
                'cfe_id',
                'documento_cliente',
                'puntos_generados',
                'motivo_no_acumulo',
                'created_at',
            ]);
        break;

    case 'migraciones':
        $rows = DB::connection('tenant')
            ->table('migrations')
            ->orderByDesc('batch')
            ->orderByDesc('migration')
            ->get();
        break;

    case 'global_webhooks':
        $rows = DB::connection('mysql')
            ->table('webhook_inbox_global')
            ->where('tenant_rut', $rut)
            ->orderByDesc('id')
            ->limit(10)
            ->get([
                'id',
                'estado',
                'http_status',
                'cfe_id',
                'documento_cliente',
                'puntos_generados',
                'motivo_no_acumulo',
                'created_at',
            ]);
        break;

    default:
        fwrite(STDERR, "SECCION NO SOPORTADA: {$section}" . PHP_EOL);
        exit(1);
}

foreach ($rows as $row) {
    echo json_encode($row, JSON_UNESCAPED_UNICODE) . PHP_EOL;
}


