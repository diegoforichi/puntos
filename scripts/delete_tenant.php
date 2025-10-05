<?php

require __DIR__ . '/../app/vendor/autoload.php';

$app = require __DIR__ . '/../app/bootstrap/app.php';

/** @var \Illuminate\Contracts\Console\Kernel $kernel */
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

$rut = $argv[1] ?? null;

if (!$rut) {
    fwrite(STDERR, "Debe indicar el RUT del tenant" . PHP_EOL);
    exit(1);
}

$tenant = Tenant::withTrashed()->where('rut', $rut)->first();

if (!$tenant) {
    echo "Tenant no encontrado" . PHP_EOL;
    exit(0);
}

$sqlite = $tenant->getSqlitePath();

// Eliminar registros relacionados en MySQL
DB::table('webhook_inbox_global')->where('tenant_rut', $rut)->delete();

$tenant->forceDelete();

echo "Tenant eliminado" . PHP_EOL;

// Eliminar archivo SQLite si existe
if ($sqlite && file_exists($sqlite)) {
    unlink($sqlite);
    echo "SQLite eliminado: {$sqlite}" . PHP_EOL;
}


