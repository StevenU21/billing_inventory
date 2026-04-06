<?php

namespace App\Jobs;

use App\Models\Backup;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateDatabaseBackup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $databasePath = config('native-backups.sqlite_path');
        $backupsPath = config('native-backups.backups_path');

        try {
            Backup::createSnapshot($databasePath, $backupsPath);
        } catch (\Throwable $e) {
            Log::error('Fallo en job de respaldo automático.', [
                'message' => $e->getMessage(),
            ]);
        }
    }
}
