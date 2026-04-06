<?php

namespace App\Services;

/**
 * DashboardService
 * 
 * Basic service for dashboard information.
 */
class DashboardService
{
    /**
     * Get basic dashboard information.
     */
    public function getBasicInfo(): array
    {
        return [
            'app_name' => config('app.name'),
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'server_time' => now()->toDateTimeString(),
        ];
    }
}
