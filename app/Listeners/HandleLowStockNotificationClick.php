<?php

namespace App\Listeners;

use App\Events\LowStockNotificationClicked;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Native\Desktop\Facades\Window;

class HandleLowStockNotificationClick
{
    public function handle(LowStockNotificationClicked $event): void
    {
        $inventoryId = $event->reference;

        // Ensure we have a valid ID before attempting to open
        if ($inventoryId) {
            Window::open()->url(route('inventories.show', $inventoryId));
        }
    }
}
