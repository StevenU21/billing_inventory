<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BackupCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public array $payload)
    {
    }

    public function broadcastOn(): array
    {
        return [new Channel('nativephp')];
    }

    public function broadcastAs(): string
    {
        return 'BackupCreated';
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}