<?php

namespace App\Policies;

use Deifhelt\LaravelPermissionsManager\Traits\HasPermissionCheck;
use Illuminate\Notifications\DatabaseNotification;
use App\Models\User;

class NotificationPolicy
{
    use HasPermissionCheck;

    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'read notifications');
    }

    public function view(User $user, DatabaseNotification $notification): bool
    {
        return $this->ownsNotification($user, $notification)
            && $this->checkPermission($user, 'read notifications');
    }

    public function delete(User $user, DatabaseNotification $notification): bool
    {
        return $this->ownsNotification($user, $notification)
            && $this->checkPermission($user, 'destroy notifications');
    }

    public function mark(User $user, DatabaseNotification $notification): bool
    {
        return $this->ownsNotification($user, $notification)
            && $this->checkPermission($user, 'mark as read');
    }

    public function markAll(User $user): bool
    {
        return $this->checkPermission($user, 'mark all as read');
    }

    protected function ownsNotification(User $user, DatabaseNotification $notification): bool
    {
        return (int) $notification->notifiable_id === (int) $user->getKey();
    }
}

