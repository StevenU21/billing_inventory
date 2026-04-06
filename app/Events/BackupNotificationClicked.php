<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BackupNotificationClicked
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
}
