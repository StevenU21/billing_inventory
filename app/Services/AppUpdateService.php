<?php

namespace App\Services;

use App\DTOs\AppUpdateCheckDTO;
use App\Notifications\UpdateAvailableNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Native\Desktop\Facades\AutoUpdater;

class AppUpdateService
{
    public function __construct(
        protected NativeUpdaterStatusStore $statusStore
    ) {}

    public function getStatus(): array
    {
        $this->statusStore->ensureFresh(config('nativephp.version'));

        return [
            'currentVersion' => config('nativephp.version'),
            'updaterEnabled' => (bool) config('nativephp.updater.enabled'),
            'updaterProvider' => config('nativephp.updater.default'),
            'statusHistory' => $this->statusStore->latest(5),
            'updaterState' => $this->statusStore->state(),
        ];
    }

    public function getHistory(): array
    {
        return [
            'history' => $this->statusStore->latest(5),
            'state' => $this->statusStore->state(),
        ];
    }

    public function checkForUpdates(AppUpdateCheckDTO $config): void
    {
        if (! $config->isValid()) {
            throw new \Exception($config->getValidationError());
        }

        try {
            AutoUpdater::checkForUpdates();
        } catch (\Throwable $e) {
            Log::error('NativePHP manual update check failed.', [
                'exception' => $e->getMessage(),
            ]);

            throw new \Exception('No se pudo iniciar la verificación. Revisa el log.', 0, $e);
        }
    }

    public function downloadUpdate(): void
    {
        $state = $this->statusStore->state();

        if (empty($state['canDownload'])) {
            throw new \Exception('Primero busca una actualización disponible.');
        }

        try {
            $this->statusStore->push('download_requested', 'Conectando con el servidor de actualizaciones...', [
                'version' => $state['availableVersion'] ?? null,
                'releaseName' => $state['availableReleaseName'] ?? null,
            ]);

            AutoUpdater::downloadUpdate();
        } catch (\Throwable $e) {
            Log::error('NativePHP manual download failed.', [
                'exception' => $e->getMessage(),
            ]);

            $this->statusStore->push('error', 'No se pudo iniciar la descarga.', [
                'detail' => $e->getMessage(),
            ]);

            throw new \Exception('No se pudo iniciar la descarga. Revisa el log.', 0, $e);
        }
    }

    public function installUpdate(): void
    {
        $state = $this->statusStore->state();

        if (empty($state['canInstall'])) {
            throw new \Exception('Descarga la actualización antes de instalarla.');
        }

        try {
            // Mark update notifications as read before installing
            $this->cleanupUpdateNotifications();

            AutoUpdater::quitAndInstall();
        } catch (\Throwable $e) {
            Log::error('NativePHP install trigger failed.', [
                'exception' => $e->getMessage(),
            ]);

            throw new \Exception('No se pudo solicitar la instalación. Revisa el log.', 0, $e);
        }
    }

    /**
     * Mark all unread update notifications as read
     */
    protected function cleanupUpdateNotifications(): void
    {
        try {
            DB::table('notifications')
                ->where('type', UpdateAvailableNotification::class)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        } catch (\Throwable $e) {
            Log::warning('Failed to cleanup update notifications', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
