<?php

namespace App\Console\Commands;

use App\Enums\NotificationCategory;
use App\Events\BackupCreated;
use App\Models\Backup;
use App\Models\User;
use App\Notifications\BackupCreatedNotification;
use App\Services\NotificationManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Native\Desktop\Facades\Settings;

class BackupSqlite extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:sqlite {--keep= : Número máximo de respaldos que se conservarán}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea un respaldo comprimido de la base de datos SQLite usada por NativePHP.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $databasePath = (string) config('native-backups.sqlite_path');

        try {
            $customPath = Settings::get('backup_path');
            $settingRetention = Settings::get('backup_retention', 30);
        } catch (\Throwable $e) {
            $customPath = null;
            $settingRetention = 30;
            Log::warning('Could not retrieve NativePHP settings in console: ' . $e->getMessage());
        }

        $backupsPath = (string) ($customPath ?: config('native-backups.backups_path'));
        $defaultRetention = (int) config('native-backups.max_files', 0);

        $retention = (int) ($this->option('keep') ?? $settingRetention ?? $defaultRetention);

        Log::info('Starting backup', ['path' => $backupsPath, 'retention' => $retention]);

        try {
            $filename = Backup::createSnapshot($databasePath, $backupsPath);
            $this->info('Backup creado: ' . $filename);

            $this->dispatchNotifications($filename, $backupsPath);

            $removed = $this->cleanupOldBackups($backupsPath, $retention);
            if ($removed > 0) {
                $this->line('Se eliminaron ' . $removed . ' backups antiguos.');
            }

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            Log::error('No se pudo crear el respaldo programado.', [
                'message' => $e->getMessage(),
            ]);

            $this->error('No se pudo crear el backup: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }

    private function dispatchNotifications(string $filename, string $backupsPath): void
    {
        try {
            $filePath = $backupsPath . DIRECTORY_SEPARATOR . $filename;
            $fileSize = File::exists($filePath) ? File::size($filePath) : 0;
            $formattedSize = Backup::formatSize($fileSize);

            $payload = [
                'title' => 'Respaldo creado exitosamente',
                'message' => "Se ha creado un nuevo respaldo de la base de datos: {$filename}",
                'filename' => $filename,
                'size' => $fileSize,
                'formatted_size' => $formattedSize,
                'created_at' => now()->toIso8601String(),
                'backup_path' => $backupsPath,
            ];

            if (!NotificationManager::shouldNotify(NotificationCategory::System)) {
                return;
            }

            $recipients = User::query()
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->permission('read backups')
                        ->orWhereHas('roles', fn($q) => $q->where('name', 'admin'));
                })
                ->get();

            if ($recipients->isNotEmpty()) {
                Notification::send($recipients, new BackupCreatedNotification($payload));
            }

            event(new BackupCreated($payload));
        } catch (\Throwable $e) {
            Log::warning('Failed to dispatch backup notifications', [
                'filename' => $filename,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function cleanupOldBackups(string $backupsPath, int $retain): int
    {
        if ($retain < 1 || !File::exists($backupsPath)) {
            return 0;
        }

        $files = collect(File::files($backupsPath))
            ->sortByDesc(fn($file) => $file->getMTime())
            ->values();

        $removed = 0;
        foreach ($files->skip($retain) as $file) {
            File::delete($file->getRealPath());
            $removed++;
        }

        return $removed;
    }
}
