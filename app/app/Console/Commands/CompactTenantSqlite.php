<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompactTenantSqlite extends Command
{
    protected $signature = 'tenant:compactar-sqlite {--tenant=} {--analyze : Ejecuta ANALYZE después de VACUUM}';

    protected $description = 'Ejecuta VACUUM (y opcionalmente ANALYZE) sobre las bases SQLite de los tenants.';

    public function handle(): int
    {
        $tenantRut = $this->option('tenant');
        $ejecutarAnalyze = (bool) $this->option('analyze');

        $query = Tenant::on('mysql')->where('estado', 'activo');

        if ($tenantRut) {
            $query->where('rut', $tenantRut);
        }

        $tenants = $query->get();

        if ($tenants->isEmpty()) {
            $this->warn('No se encontraron tenants para compactar.');

            return self::SUCCESS;
        }

        $this->info(sprintf('Iniciando compactación para %d tenant(s)...', $tenants->count()));

        foreach ($tenants as $tenant) {
            $sqlitePath = $tenant->getSqlitePath();

            if (! file_exists($sqlitePath)) {
                $this->warn("Tenant {$tenant->rut}: base SQLite no encontrada ({$sqlitePath}).");

                continue;
            }

            $sizeAntes = filesize($sqlitePath);

            try {
                $pdo = new \PDO('sqlite:'.$sqlitePath);
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                $pdo->exec('PRAGMA journal_mode = WAL;');
                $pdo->exec('VACUUM;');

                if ($ejecutarAnalyze) {
                    $pdo->exec('ANALYZE;');
                }

                $pdo = null;

                $sizeDespues = filesize($sqlitePath);
                $ahorro = $sizeAntes - $sizeDespues;

                $this->line(sprintf(
                    'Tenant %s: %s → %s (ahorro %s)',
                    $tenant->rut,
                    $this->formatearBytes($sizeAntes),
                    $this->formatearBytes($sizeDespues),
                    $this->formatearBytes($ahorro)
                ));
            } catch (\Throwable $e) {
                Log::error('Error compactando SQLite del tenant', [
                    'tenant' => $tenant->rut,
                    'error' => $e->getMessage(),
                ]);

                $this->error("Tenant {$tenant->rut}: error ejecutando VACUUM - {$e->getMessage()}");
            } finally {
                DB::purge('tenant');
            }
        }

        $this->info('Compactación finalizada.');

        return self::SUCCESS;
    }

    private function formatearBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $unidades = ['B', 'KB', 'MB', 'GB'];
        $potencia = (int) floor(log($bytes, 1024));
        $potencia = min($potencia, count($unidades) - 1);

        return number_format($bytes / (1024 ** $potencia), 2).' '.$unidades[$potencia];
    }
}
