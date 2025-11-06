<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use PDO;

class TenantMigrateRaw extends Command
{
    protected $signature = 'tenant:migrate-raw {rut}';

    protected $description = 'Ejecuta migraciones de tenant usando SQL puro compatible con SQLite antiguo';

    public function handle(): int
    {
        $rut = $this->argument('rut');
        $tenant = Tenant::where('rut', $rut)->first();

        if (! $tenant) {
            $this->error("Tenant {$rut} no encontrado");

            return self::FAILURE;
        }

        $sqlitePath = $tenant->getSqlitePath();

        if (! file_exists($sqlitePath)) {
            $this->error("Archivo SQLite no existe: {$sqlitePath}");

            return self::FAILURE;
        }

        try {
            $pdo = new PDO('sqlite:'.$sqlitePath);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Obtener tablas existentes
            $existingTables = [];
            $result = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'");
            foreach ($result as $row) {
                $existingTables[] = $row['name'];
            }

            $this->info("Tenant {$rut}: Ejecutando migraciones de campañas...");

            // Solo crear tablas de campañas si no existen
            if (! in_array('campanas', $existingTables)) {
                $this->createCampanasTables($pdo);
            } else {
                $this->info('  - Tablas de campañas ya existen, aplicando actualizaciones...');
                $this->updateCampanasTables($pdo);
            }

            $this->info("Tenant {$rut}: Ajustando tabla puntos_canjeados...");

            if (! in_array('puntos_canjeados', $existingTables)) {
                $this->createPuntosCanjeadosTable($pdo);
            } else {
                $this->updatePuntosCanjeadosTable($pdo);
            }

            $this->info("Tenant {$rut}: Migraciones completadas correctamente");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error al migrar tenant {$rut}: ".$e->getMessage());

            return self::FAILURE;
        }
    }

    private function createCampanasTables(PDO $pdo): void
    {
        $this->info('  - Creando tabla campanas...');

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS campanas (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                tenant_id INTEGER NULL,
                canal VARCHAR(20) DEFAULT 'ambos',
                tipo_envio VARCHAR(20) DEFAULT 'todos',
                titulo VARCHAR(255) NOT NULL,
                subtitulo VARCHAR(255) NULL,
                imagen_url VARCHAR(500) NULL,
                asunto_email VARCHAR(255) NULL,
                cuerpo_texto TEXT NOT NULL,
                mensaje_whatsapp TEXT NULL,
                fecha_programada DATETIME NULL,
                estado VARCHAR(20) DEFAULT 'borrador',
                totales TEXT NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                deleted_at DATETIME NULL
            )
        ");

        $pdo->exec('CREATE INDEX IF NOT EXISTS campanas_tenant_id_index ON campanas (tenant_id)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS campanas_canal_index ON campanas (canal)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS campanas_estado_index ON campanas (estado)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS campanas_fecha_programada_index ON campanas (fecha_programada)');

        $this->info('  - Creando tabla campana_envios...');

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS campana_envios (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                campana_id INTEGER NOT NULL,
                cliente_id INTEGER NOT NULL,
                canal VARCHAR(20) DEFAULT 'whatsapp',
                estado VARCHAR(20) DEFAULT 'pendiente',
                intentos INTEGER DEFAULT 0,
                error_mensaje TEXT NULL,
                sent_at DATETIME NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )
        ");

        $pdo->exec('CREATE INDEX IF NOT EXISTS campana_envios_campana_id_estado_index ON campana_envios (campana_id, estado)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS campana_envios_cliente_id_index ON campana_envios (cliente_id)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS campana_envios_canal_index ON campana_envios (canal)');
    }

    private function updateCampanasTables(PDO $pdo): void
    {
        // Verificar y agregar columnas faltantes en campanas
        $campanasColumns = $this->getTableColumns($pdo, 'campanas');

        if (! in_array('tenant_id', $campanasColumns)) {
            $this->info('  - Agregando columna tenant_id a campanas...');
            $pdo->exec('ALTER TABLE campanas ADD COLUMN tenant_id INTEGER NULL');
            $pdo->exec('CREATE INDEX IF NOT EXISTS campanas_tenant_id_index ON campanas (tenant_id)');
        }

        if (! in_array('mensaje_whatsapp', $campanasColumns)) {
            $this->info('  - Agregando columna mensaje_whatsapp a campanas...');
            $pdo->exec('ALTER TABLE campanas ADD COLUMN mensaje_whatsapp TEXT NULL');
        }

        if (! in_array('deleted_at', $campanasColumns)) {
            $this->info('  - Agregando columna deleted_at a campanas...');
            $pdo->exec('ALTER TABLE campanas ADD COLUMN deleted_at DATETIME NULL');
        }

        // Verificar y agregar columnas faltantes en campana_envios
        $enviosColumns = $this->getTableColumns($pdo, 'campana_envios');

        if (! in_array('canal', $enviosColumns)) {
            $this->info('  - Agregando columna canal a campana_envios...');
            $pdo->exec("ALTER TABLE campana_envios ADD COLUMN canal VARCHAR(20) DEFAULT 'whatsapp'");
            $pdo->exec('CREATE INDEX IF NOT EXISTS campana_envios_canal_index ON campana_envios (canal)');
        }
    }

    private function createPuntosCanjeadosTable(PDO $pdo): void
    {
        $this->info('  - Creando tabla puntos_canjeados...');

        $pdo->exec('
            CREATE TABLE IF NOT EXISTS puntos_canjeados (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                cliente_id INTEGER NOT NULL,
                puntos_canjeados DECIMAL(10,2) NOT NULL,
                puntos_restantes DECIMAL(10,2) NOT NULL,
                concepto VARCHAR(500) NULL,
                autorizado_por VARCHAR(100) NULL,
                origen VARCHAR(50) DEFAULT "panel",
                referencia VARCHAR(255) NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )
        ');

        $pdo->exec('CREATE INDEX IF NOT EXISTS puntos_canjeados_cliente_id_index ON puntos_canjeados (cliente_id)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS puntos_canjeados_origen_index ON puntos_canjeados (origen)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS puntos_canjeados_created_at_index ON puntos_canjeados (created_at)');
    }

    private function updatePuntosCanjeadosTable(PDO $pdo): void
    {
        $columns = $this->getTableColumns($pdo, 'puntos_canjeados');

        if (! in_array('puntos_restantes', $columns)) {
            $this->info('  - Agregando columna puntos_restantes a puntos_canjeados...');
            $pdo->exec('ALTER TABLE puntos_canjeados ADD COLUMN puntos_restantes DECIMAL(10,2) NULL');
        }

        if (! in_array('concepto', $columns)) {
            $this->info('  - Agregando columna concepto a puntos_canjeados...');
            $pdo->exec('ALTER TABLE puntos_canjeados ADD COLUMN concepto VARCHAR(500) NULL');
        }

        if (! in_array('autorizado_por', $columns)) {
            $this->info('  - Agregando columna autorizado_por a puntos_canjeados...');
            $pdo->exec('ALTER TABLE puntos_canjeados ADD COLUMN autorizado_por VARCHAR(100) NULL');
        }

        if (! in_array('origen', $columns)) {
            $this->info('  - Agregando columna origen a puntos_canjeados...');
            $pdo->exec('ALTER TABLE puntos_canjeados ADD COLUMN origen VARCHAR(50) DEFAULT "panel"');
        }

        if (! in_array('referencia', $columns)) {
            $this->info('  - Agregando columna referencia a puntos_canjeados...');
            $pdo->exec('ALTER TABLE puntos_canjeados ADD COLUMN referencia VARCHAR(255) NULL');
        }

        if (! in_array('created_at', $columns)) {
            $this->info('  - Agregando columna created_at a puntos_canjeados...');
            $pdo->exec('ALTER TABLE puntos_canjeados ADD COLUMN created_at DATETIME NULL');
        }

        if (! in_array('updated_at', $columns)) {
            $this->info('  - Agregando columna updated_at a puntos_canjeados...');
            $pdo->exec('ALTER TABLE puntos_canjeados ADD COLUMN updated_at DATETIME NULL');
        }
    }

    private function getTableColumns(PDO $pdo, string $tableName): array
    {
        $columns = [];
        $result = $pdo->query("PRAGMA table_info({$tableName})");
        foreach ($result as $row) {
            $columns[] = $row['name'];
        }

        return $columns;
    }
}
