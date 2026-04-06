<?php

namespace App\Console\Commands;

use App\Events\UpdateAvailable;
use App\Models\User;
use App\Notifications\UpdateAvailableNotification;
use App\Services\NativeUpdaterStatusStore;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Native\Desktop\Facades\AutoUpdater;

class CheckForAppUpdates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-updates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica si hay actualizaciones disponibles y notifica a los administradores';

    /**
     * Execute the console command.
     */
    public function handle(NativeUpdaterStatusStore $statusStore): int
    {
        if (!config('nativephp.updater.enabled')) {
            $this->warn('El actualizador está deshabilitado.');
            return Command::SUCCESS;
        }

        try {
            $this->info('Verificando actualizaciones...');

            AutoUpdater::checkForUpdates();

            sleep(3);

            $state = $statusStore->state();

            if ($state['stage'] === 'available' && !empty($state['availableVersion'])) {
                $this->info("Actualización disponible: {$state['availableVersion']}");
                $this->notifyUsers($state);
            } else {
                $this->info('No hay actualizaciones disponibles.');
            }

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            Log::error('Error al verificar actualizaciones automáticas', [
                'error' => $e->getMessage(),
            ]);
            $this->error('Error al verificar actualizaciones: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function notifyUsers(array $state): void
    {
        $availableVersion = $state['availableVersion'];
        $currentVersion = config('nativephp.version');

        $existingNotification = DB::table('notifications')
            ->where('type', UpdateAvailableNotification::class)
            ->whereNull('read_at')
            ->whereRaw("JSON_EXTRACT(data, '$.metadata.available_version') = ?", [$availableVersion])
            ->exists();

        if ($existingNotification) {
            $this->info('Ya existe una notificación para esta versión.');
            return;
        }

        $recipients = User::permission('update app')
            ->where('is_active', true)
            ->get();

        if ($recipients->isEmpty()) {
            $this->warn('No hay usuarios con permiso para actualizar la aplicación.');
            return;
        }

        $payload = [
            'available_version' => $availableVersion,
            'current_version' => $currentVersion,
            'release_name' => $state['availableReleaseName'] ?? null,
        ];

        Notification::send($recipients, new UpdateAvailableNotification($payload));

        event(new UpdateAvailable($payload));

        $this->info("Notificación enviada a {$recipients->count()} usuario(s).");
    }
}
