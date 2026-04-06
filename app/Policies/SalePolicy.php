<?php

namespace App\Policies;

use App\Models\Sale;
use App\Models\User;
use Deifhelt\LaravelPermissionsManager\Traits\HasPermissionCheck;

class SalePolicy
{
    use HasPermissionCheck;

    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'read sales');
    }

    public function view(User $user): bool
    {
        return $this->checkPermission($user, 'read sales');
    }

    public function create(User $user): bool
    {
        return $this->checkPermission($user, 'create sales');
    }

    public function update(User $user): bool
    {
        return $this->checkPermission($user, 'update sales');
    }

    public function delete(User $user, Sale $sale): bool
    {
        // Business Rule: Only the most recent sale can be deleted, and it must not be cancelled
        $isLatestSale = $sale->id === Sale::max('id');
        $isNotCancelled = ! $sale->is_cancelled;

        if (! $isLatestSale || ! $isNotCancelled) {
            return false;
        }

        return $this->checkPermission($user, 'destroy sales');
    }

    public function export(User $user): bool
    {
        return $this->checkPermission($user, 'export sales');
    }

    public function generateInvoice(User $user): bool
    {
        return $this->checkPermission($user, 'generate invoice');
    }
}
