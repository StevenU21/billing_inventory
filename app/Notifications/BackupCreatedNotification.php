<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class BackupCreatedNotification extends Notification
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
        return [
            'type' => 'backup_created',
            'icon' => 'fa-database',
            'category' => 'system',
            'title' => $this->payload['title'] ?? 'Respaldo creado exitosamente',
            'message' => $this->payload['message'] ?? 'Se ha creado un nuevo respaldo de la base de datos.',
            'occurred_at' => $this->payload['created_at'] ?? now()->toIso8601String(),
            'url' => null,
            'metadata' => $this->payload,
            'notification_id' => $this->id,
            'notifiable_id' => $notifiable->id,
            'notifiable_type' => $notifiable::class,
        ];
    }
}
