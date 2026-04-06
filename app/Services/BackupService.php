<?php

namespace App\Services;

use App\DTOs\BackupOperationDTO;
use App\Models\Backup;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Native\Desktop\Facades\Settings;

class BackupService
{
    public function __construct(
        protected string $databasePath,
        protected string $defaultBackupPath,
    ) {}

    public function getBackupPath(): string
    {
        try {
            $customPath = Settings::get('backup_path');

            return $customPath ?: $this->defaultBackupPath;
        } catch (\Throwable $e) {
            return $this->defaultBackupPath;
        }
    }

    public function getPaginatedBackups(int $page = 1, int $perPage = 10): LengthAwarePaginator
    {
        $path = $this->getBackupPath();
        $backups = Backup::all($path);

        return new LengthAwarePaginator(
            $backups->slice(($page - 1) * $perPage, $perPage)->values(),
            $backups->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    public function getIndexData(int $page = 1): array
    {
        return [
            'files' => $this->getPaginatedBackups($page),
            'databasePath' => $this->databasePath,
            'backupsPath' => $this->getBackupPath(),
        ];
    }

    public function createBackup(): string
    {
        try {
            $path = $this->getBackupPath();
            $filename = Backup::createSnapshot($this->databasePath, $path);

            return $filename;
        } catch (\Throwable $e) {
            Log::error('No se pudo crear el respaldo.', ['message' => $e->getMessage()]);
            throw new \Exception('No se pudo crear el respaldo: '.$e->getMessage(), 0, $e);
        }
    }

    public function getBackupFile(BackupOperationDTO $operation): string
    {
        if (! $operation->fileExists()) {
            throw new \Exception('Archivo de respaldo no encontrado.', 404);
        }

        return $operation->getFullPath();
    }

    public function restoreBackup(BackupOperationDTO $operation): void
    {
        try {
            Backup::restoreFromBackup(
                $operation->backupPath,
                $operation->filename,
                $operation->databasePath
            );
        } catch (\Throwable $e) {
            Log::error('Error al restaurar respaldo.', ['message' => $e->getMessage()]);
            throw new \Exception('No se pudo restaurar el respaldo: '.$e->getMessage(), 0, $e);
        }
    }

    public function deleteBackup(BackupOperationDTO $operation): void
    {
        try {
            Backup::deleteBackup($operation->backupPath, $operation->filename);
        } catch (\Throwable $e) {
            Log::error('Error al eliminar respaldo.', ['message' => $e->getMessage()]);
            throw new \Exception('No se pudo eliminar el respaldo: '.$e->getMessage(), 0, $e);
        }
    }
}
