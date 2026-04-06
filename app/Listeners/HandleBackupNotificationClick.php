<?php

namespace App\Listeners;

use App\Events\BackupNotificationClicked;
use Native\Desktop\Facades\Window;

class HandleBackupNotificationClick
{
    public function handle(BackupNotificationClicked $event): void
    {
        Window::open()->url(route('backups.index'));
    }
}
