<?php

namespace App\Models;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

class Backup
{
    public $name;

    public $size;

    public $formatted_size;

    public $created_at;

    public $backup_path;

    public $extension;

    public function __construct($name, $size, $formatted_size, $created_at, $backup_path, $extension)
    {
        $this->name = $name;
        $this->size = $size;
        $this->formatted_size = $formatted_size;
        $this->created_at = $created_at;
        $this->backup_path = $backup_path;
        $this->extension = $extension;
    }

    public static function all($path)
    {
        if (! File::exists($path)) {
            return collect();
        }

        $files = collect(File::files($path))->map(function ($file) {
            $name = $file->getFilename();
            $size = $file->getSize();
            $formatted_size = self::formatSize($size);
            $created_at = date('Y-m-d H:i:s', $file->getMTime()); // Using modification time as creation time wrapper
            $backup_path = $file->getRealPath();
            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

            return new self($name, $size, $formatted_size, $created_at, $backup_path, $extension);
        });

        return $files->sortByDesc('created_at')->values();
    }

    public static function paginate(Collection $backups, $perPage = 10)
    {
        $page = request()->input('page', 1);
        $total = $backups->count();
        $results = $backups->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $results,
            $total,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }

    public static function createSnapshot(string $databasePath, string $backupsPath): string
    {
        Log::info('Backup::createSnapshot called', ['db' => $databasePath, 'target' => $backupsPath]);

        if (! File::exists($databasePath)) {
            throw new RuntimeException('No se encontró el archivo de base de datos en '.$databasePath);
        }

        File::ensureDirectoryExists($backupsPath);

        $timestamp = now()->format('Ymd_His');
        $filename = 'database-backup-'.$timestamp.'.zip';
        $zipPath = $backupsPath.DIRECTORY_SEPARATOR.$filename;

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('No se pudo crear el archivo ZIP para el respaldo.');
        }

        $filesToBackup = [$databasePath];
        foreach (['-shm', '-wal'] as $suffix) {
            $relatedFile = $databasePath.$suffix;
            if (File::exists($relatedFile)) {
                $filesToBackup[] = $relatedFile;
            }
        }

        foreach ($filesToBackup as $file) {
            $zip->addFile($file, basename($file));
        }

        $zip->close();

        return $filename;
    }

    public static function restoreFromBackup(string $backupsPath, string $filename, string $databasePath): void
    {
        $source = $backupsPath.DIRECTORY_SEPARATOR.$filename;
        if (! File::exists($source)) {
            throw new RuntimeException('El respaldo seleccionado no existe.');
        }

        File::ensureDirectoryExists(dirname($databasePath));

        if (File::exists($databasePath)) {
            $safetyName = 'database-before-restore-'.now()->format('Ymd_His').'.sqlite';
            File::copy($databasePath, $backupsPath.DIRECTORY_SEPARATOR.$safetyName);
        }

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($extension === 'zip') {
            self::restoreFromZip($source, $databasePath);

            return;
        }

        File::copy($source, $databasePath);
    }

    public static function deleteBackup(string $backupsPath, string $filename): void
    {
        $file = $backupsPath.DIRECTORY_SEPARATOR.$filename;
        if (File::exists($file)) {
            File::delete($file);
        }
    }

    public static function formatSize(int $bytes): string
    {
        if ($bytes === 0) {
            return '0 B';
        }
        $sizeUnits = ['B', 'KB', 'MB', 'GB'];
        $power = (int) floor(log($bytes, 1024));
        $power = min($power, count($sizeUnits) - 1);
        $value = $bytes / pow(1024, $power);

        return round($value, 2).' '.$sizeUnits[$power];
    }

    protected static function restoreFromZip(string $zipFile, string $databasePath): void
    {
        $zip = new ZipArchive;
        if ($zip->open($zipFile) !== true) {
            throw new RuntimeException('No se pudo abrir el archivo ZIP de respaldo.');
        }

        $tempDir = storage_path('app/native-backups/tmp/'.Str::uuid());
        File::ensureDirectoryExists($tempDir);

        if (! $zip->extractTo($tempDir)) {
            $zip->close();
            File::deleteDirectory($tempDir);
            throw new RuntimeException('No se pudo extraer el archivo ZIP de respaldo.');
        }
        $zip->close();

        $baseName = basename($databasePath);
        $files = [
            $tempDir.DIRECTORY_SEPARATOR.$baseName => $databasePath,
            $tempDir.DIRECTORY_SEPARATOR.$baseName.'-shm' => $databasePath.'-shm',
            $tempDir.DIRECTORY_SEPARATOR.$baseName.'-wal' => $databasePath.'-wal',
        ];

        foreach ($files as $source => $destination) {
            if (File::exists($source)) {
                File::copy($source, $destination);
            } else {
                if (File::exists($destination)) {
                    File::delete($destination);
                }
            }
        }

        File::deleteDirectory($tempDir);
    }
}
