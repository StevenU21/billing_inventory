<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class UpdateAvailableNotification extends Notification
{
    public function __construct(private readonly array $payload)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->formatPayload($notifiable);
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->formatPayload($notifiable));
    }

    private function formatPayload(object $notifiable): array
    {
        $availableVersion = $this->payload['available_version'] ?? 'desconocida';
        $currentVersion = $this->payload['current_version'] ?? config('nativephp.version');

        return [
            'type' => 'update_available',
            'icon' => 'fa-download',
            'category' => 'system',
            'title' => 'Actualización disponible',
            'message' => "Nueva versión {$availableVersion} disponible para descargar (actual: {$currentVersion})",
            'occurred_at' => now()->toIso8601String(),
            'url' => route('updates.index'),
            'metadata' => $this->payload,
            'notification_id' => $this->id,
            'notifiable_id' => $notifiable->id,
            'notifiable_type' => $notifiable::class,
        ];
    }
}
