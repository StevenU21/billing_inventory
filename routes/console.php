<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Native\Desktop\Facades\Settings;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

try {
    $backupFrequency = Settings::get('backup_frequency', '2hours');
} catch (\Throwable $e) {
    $backupFrequency = '2hours';
}

if ($backupFrequency !== 'manual' && $backupFrequency !== 'on_close') {
    $command = Schedule::command('backup:sqlite');

    match ($backupFrequency) {
        '1minute' => $command->everyMinute(),
        'daily' => $command->daily(),
        'weekly' => $command->weekly(),
        default => $command->everyTwoHours(),
    };

    $command->withoutOverlapping()
        ->description('Genera un respaldo comprimido de la base de datos SQLite usada por NativePHP.');
}

Schedule::command('app:check-updates')
    ->everySixHours()
    ->withoutOverlapping()
    ->description('Verifica si hay actualizaciones disponibles de la aplicación.');

Schedule::command('quotations:expire')
    ->everyFourHours()
    ->withoutOverlapping()
    ->description('Expira automáticamente las cotizaciones vencidas.');
