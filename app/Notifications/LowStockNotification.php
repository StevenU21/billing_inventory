<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification implements ShouldQueue
{
    use Queueable;

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
            'type' => 'low_stock',
            'icon' => 'fa-box',
            'category' => 'inventory',
            'title' => $this->payload['title'] ?? 'Stock bajo detectado',
            'message' => $this->payload['message'] ?? 'Alerta de stock bajo. Revisa el inventario para tomar acción.',
            'occurred_at' => $this->payload['occurred_at'] ?? now()->toIso8601String(),
            'url' => $this->payload['inventory_url'] ?? null,
            'metadata' => $this->payload,
            'notification_id' => $this->id,
            'notifiable_id' => $notifiable->id,
            'notifiable_type' => $notifiable::class,
        ];
    }

}
