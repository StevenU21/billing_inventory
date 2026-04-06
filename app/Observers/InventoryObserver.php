<?php

namespace App\Observers;

use App\Events\LowStockReached;
use App\Models\Inventory;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Illuminate\Support\Facades\Route;
use Native\Desktop\Facades\Notification as NativeNotification;

class InventoryObserver
{
    public function saved(Inventory $inventory): void
    {
        if (!$inventory->wasChanged('stock') && !$inventory->wasChanged('min_stock')) {
            return;
        }

        $inventory->loadMissing([
            'productVariant.product.brand',
        ]);

        $isLow = $inventory->stock !== null
            && $inventory->min_stock !== null
            && (float) $inventory->stock <= (float) $inventory->min_stock;

        $originalStock = $inventory->getOriginal('stock');
        $originalMinStock = $inventory->getOriginal('min_stock');

        $wasLow = $originalStock !== null
            && $originalMinStock !== null
            && (float) $originalStock <= (float) $originalMinStock;

        $stockDecreased = $inventory->wasChanged('stock')
            && $inventory->stock !== null
            && $originalStock !== null
            && (float) $inventory->stock < (float) $originalStock;

        $shouldDispatch = false;

        if ($isLow && !$wasLow) {
            $inventory->forceFill([
                'low_stock_notified_at' => now(),
            ])->saveQuietly();

            $shouldDispatch = true;
        } elseif ($isLow && $wasLow && $stockDecreased) {
            $inventory->forceFill([
                'low_stock_notified_at' => now(),
            ])->saveQuietly();

            $shouldDispatch = true;
        }

        if ($shouldDispatch) {
            $payload = $this->buildPayload($inventory);
            $this->dispatchAlerts($payload);
        }

        if (!$isLow && $wasLow) {
            $inventory->forceFill([
                'low_stock_notified_at' => null,
            ])->saveQuietly();
        }
    }

    private function dispatchAlerts(array $payload): void
    {
        if (!\App\Services\NotificationManager::shouldNotify(\App\Enums\NotificationCategory::Inventory)) {
            return;
        }

        $recipients = User::query()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->permission('read notifications')
                    ->orWhereHas('roles', fn($q) => $q->where('name', 'admin'));
            })
            ->get();

        if ($recipients->isNotEmpty()) {
            NotificationFacade::send($recipients, new LowStockNotification($payload));
        }

        rescue(function () use ($payload, $recipients) {
            NativeNotification::title('Stock bajo')
                ->message($payload['message'])
                ->reference((string) $payload['inventory_id'])
                ->event(\App\Events\LowStockNotificationClicked::class)
                ->show();

            if (class_exists(\Native\Desktop\Facades\Window::class)) {
                if ($recipients->isNotEmpty()) {
                    $count = $recipients->first()->unreadNotifications()->count();
                    \Native\Desktop\Facades\Window::setBadgeCount($count);
                }
            }
        }, report: false);
    }

    private function buildPayload(Inventory $inventory): array
    {
        $variant = $inventory->productVariant;
        $product = $variant?->product;
        $productImage = $product?->image_url ?? asset('img/image03.png');
        $variantSku = $variant?->sku ?? $product?->sku;
        $variantCode = $variant?->code ?? $product?->code;

        $variantAttributes = $variant?->attributeValues->pluck('value')->join(' / ');

        $parts = collect([
            $product?->name,
            $variant?->name,
            $variantAttributes,
        ])->filter()->values();

        $label = $parts->isEmpty()
            ? 'Variante #' . $inventory->product_variant_id
            : $parts->implode(' · ');

        return [
            'inventory_id' => $inventory->id,
            'product_variant_id' => $inventory->product_variant_id,
            'product_id' => $product?->id,
            'product_label' => $label,
            'product_name' => $product?->name,
            'variant_name' => $variant?->name,
            'variant_sku' => $variantSku,
            'variant_code' => $variantCode,
            'product_image_url' => $productImage,

            'inventory_url' => Route::has('inventories.show')
                ? route('inventories.show', $inventory->id)
                : null,
            'stock' => (float) ($inventory->stock ?? 0),
            'min_stock' => (float) ($inventory->min_stock ?? 0),
            'occurred_at' => now()->toIso8601String(),
            'title' => 'Stock bajo detectado',
            'message' => 'Alerta de stock bajo. Revisa el inventario para tomar acción.',
            'type' => 'low_stock',
            'category' => 'inventory',
            'icon' => 'fa-box',
        ];
    }
}
