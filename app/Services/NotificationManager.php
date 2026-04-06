<?php

namespace App\Services;

use App\Enums\NotificationCategory;
use Native\Desktop\Facades\Settings;

class NotificationManager
{
    public static function shouldNotify(NotificationCategory $category): bool
    {
        $globalEnabled = Settings::get('notifications_global', true);
        if (!$globalEnabled) {
            return false;
        }

        $categoryKey = "notifications_{$category->value}";

        return Settings::get($categoryKey, true);
    }
}
