<?php

namespace App\Listeners;

use App\Events\BackupCreated;
use App\Events\BackupNotificationClicked;
use Native\Desktop\Facades\Notification as NativeNotification;

class SendNativeBackupNotification
{
    public function handle(BackupCreated $event): void
    {
        $payload = $event->payload;

        rescue(function () use ($payload) {
            NativeNotification::title($payload['title'] ?? 'Respaldo creado')
                ->message($payload['message'] ?? 'Se ha creado un nuevo respaldo.')
                ->event(BackupNotificationClicked::class)
                ->show();
        }, report: false);
    }
}
