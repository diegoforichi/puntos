<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TenantBackupService
{
    public static function backup(Tenant $tenant): string
    {
        $sqlitePath = $tenant->getSqlitePath();
        if (!File::exists($sqlitePath)) {
            throw new \RuntimeException('No se encuentra la base SQLite del tenant.');
        }

        $directory = storage_path('backups/tenants/' . $tenant->rut);
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $timestamp = now()->format('Ymd_His');
        $backupPath = $directory . "/{$tenant->rut}_{$timestamp}.sqlite.gz";

        File::put($backupPath, gzencode(File::get($sqlitePath), 9));

        if (Schema::hasColumn('tenants', 'ultima_respaldo')) {
            $tenant->ultima_respaldo = now();
            $tenant->save();
        }

        return $backupPath;
    }

    public static function lastBackupPath(Tenant $tenant): ?string
    {
        $directory = storage_path('backups/tenants/' . $tenant->rut);
        if (!File::exists($directory)) {
            return null;
        }

        $files = collect(File::files($directory))
            ->filter(fn ($file) => Str::endsWith($file->getFilename(), '.sqlite.gz'))
            ->sortByDesc(fn ($file) => $file->getCTime());

        return optional($files->first())->getPathname();
    }

    public static function lastBackupMetadata(Tenant $tenant): ?array
    {
        $path = self::lastBackupPath($tenant);
        if (!$path) {
            return null;
        }

        return [
            'path' => $path,
            'filename' => basename($path),
            'size_mb' => round(File::size($path) / 1024 / 1024, 2),
            'created_at' => File::lastModified($path),
        ];
    }

    public static function archive(Tenant $tenant): array
    {
        $backupPath = self::backup($tenant);
        $sqlitePath = $tenant->getSqlitePath();

        $archiveDir = storage_path('tenants_archive');
        if (!File::exists($archiveDir)) {
            File::makeDirectory($archiveDir, 0755, true);
        }

        $archivedSqlite = null;
        if (File::exists($sqlitePath)) {
            $archivedSqlite = $archiveDir . '/' . $tenant->rut . '_' . now()->format('Ymd_His') . '.sqlite';
            File::move($sqlitePath, $archivedSqlite);
        }

        return [
            'backup' => $backupPath,
            'sqlite' => $archivedSqlite,
        ];
    }
}
