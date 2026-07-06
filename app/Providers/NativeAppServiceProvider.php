<?php

namespace App\Providers;

use App\Classes\NativeManager;
use Native\Desktop\Contracts\ProvidesPhpIni;
use Native\Desktop\Facades\Window;
use Illuminate\Support\Facades\Event;
use App\Events\LowStockNotificationClicked;
use App\Listeners\HandleLowStockNotificationClick;
use App\Events\BackupNotificationClicked;
use App\Listeners\HandleBackupNotificationClick;
use App\Events\BackupCreated;
use App\Listeners\SendNativeBackupNotification;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    public function boot(): void
    {
        // Abrir la ventana principal de la app
        Window::open()
            ->width(1280)
            ->height(720)
            ->minHeight(680)
            ->minWidth(820)
            ->hideMenu();

        // Ejecutar la semilla si no se ha hecho
        app(NativeManager::class)->ensureDatabaseSeeded();

        Event::listen(LowStockNotificationClicked::class, HandleLowStockNotificationClick::class);
        Event::listen(BackupNotificationClicked::class, HandleBackupNotificationClick::class);
        Event::listen(BackupCreated::class, SendNativeBackupNotification::class);
    }

    public function phpIni(): array
    {
        return [
            'memory_limit' => '1024M',
            'max_execution_time' => '0',
            'opcache.enable' => '0',
            'opcache.enable_cli' => '0',
            // OPcache and JIT are disabled because they cause Access Violation (0xC0000005) crashes in PHP 8.4 CLI on Windows.
        ];
    }
}
