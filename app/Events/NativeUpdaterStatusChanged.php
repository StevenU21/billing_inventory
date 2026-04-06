<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NativeUpdaterStatusChanged implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public array $state, public array $history)
    {
    }

    public function broadcastOn(): array
    {
        return [new Channel('nativephp')];
    }

    public function broadcastAs(): string
    {
        return 'native.updater-status';
    }

    public function broadcastWith(): array
    {
        return [
            'state' => $this->state,
            'history' => $this->history,
        ];
    }
}
