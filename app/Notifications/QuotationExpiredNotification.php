<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class QuotationExpiredNotification extends Notification implements ShouldQueue
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
            'type' => 'quotation_expired',
            'icon' => 'fa-file-invoice-dollar', // Or 'fa-clock'
            'category' => 'system', // Or 'system'
            'title' => $this->payload['title'] ?? 'Cotizaciones vencidas',
            'message' => $this->payload['message'] ?? "Se han cancelado automáticamente {$this->payload['count']} cotizaciones vencidas.",
            'occurred_at' => $this->payload['occurred_at'] ?? now()->toIso8601String(),
            'url' => route('admin.quotations.index', ['filter' => ['status' => 'rejected']]),
            'metadata' => $this->payload,
            'notification_id' => $this->id,
            'notifiable_id' => $notifiable->id,
            'notifiable_type' => $notifiable::class,
        ];
    }
}
