<?php

namespace App\Classes;

use App\Models\Config;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Native\Desktop\Facades\Window;

class NativeManager
{
    /**
     * Ensure the native database is seeded on first run.
     */
    public function ensureDatabaseSeeded(): void
    {
        try {
            if (Config::where('seeded', true)->exists()) {
                return;
            }

            if (User::count() > 0) {
                Log::info('Database appears already populated. Marking as seeded.');
                Config::updateOrCreate(['id' => 1], ['seeded' => true]);
                return;
            }

            Config::updateOrCreate(['id' => 1], ['seeded' => true]);

            Artisan::call('db:seed', [
                '--no-interaction' => true,
                '--force' => true,
            ]);

            Log::info('Database seeded successfully on first run.');

        } catch (\Throwable $e) {
            Log::error('Error seeding native database', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if (User::count() === 0) {
                Config::where('id', 1)->update(['seeded' => false]);
            }
        }
    }

    /**
     * Open a PDF preview window.
     *
     * @param string $route Route name for the PDF preview
     * @param array $params Route parameters
     * @param string $title Window title
     * @return void
     */
    public function openPdfWindow(string $route, array $params, string $title): void
    {
        Window::open('pdf-preview-' . uniqid())
            ->route($route, $params)
            ->width(900)
            ->height(700)
            ->minWidth(600)
            ->minHeight(400)
            ->title($title)
            ->resizable(true)
            ->hideMenu()
            ->hideDevTools();
    }
}
