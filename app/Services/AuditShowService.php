<?php

namespace App\Services;

use App\DTOs\AuditShowActivityData;
use App\DTOs\AuditShowChangeRowData;
use App\Models\AccountReceivable;
use App\Models\AccountReceivablePayment;
use App\Models\Audit;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductPriceHistory;
use App\Models\ProductVariant;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Quotation;
use App\Models\QuotationDetail;
use App\Models\Sale;
use App\Models\SaleDetail;
use Deifhelt\ActivityPresenter\Facades\ActivityPresenter;
use Deifhelt\ActivityPresenter\Services\TranslationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

final class AuditShowService
{
    public function build(string $decodedType, string|int $subjectId, array $filters = []): array
    {
        $parentAudits = $this->queryAudits($decodedType, [$subjectId], $filters);
        $childAudits = new Collection;

        if ($decodedType === Sale::class) {
            $childAudits = $this->getSaleChildAudits($subjectId, $filters);
        } elseif ($decodedType === Product::class) {
            $childAudits = $this->getProductChildAudits($subjectId, $filters);
        } elseif ($decodedType === Purchase::class) {
            $childAudits = $this->getPurchaseChildAudits($subjectId, $filters);
        } elseif ($decodedType === Quotation::class) {
            $childAudits = $this->getQuotationChildAudits($subjectId, $filters);
        } elseif ($decodedType === AccountReceivable::class) {
            $childAudits = $this->getAccountReceivableChildAudits($subjectId, $filters);
        } elseif ($decodedType === Inventory::class) {
            $childAudits = $this->getInventoryChildAudits($subjectId, $filters);
        } elseif ($decodedType === ProductAttribute::class) {
            $childAudits = $this->getProductAttributeChildAudits($subjectId, $filters);
        } elseif ($decodedType === ProductVariant::class) {
            $childAudits = $this->getProductVariantChildAudits($subjectId, $filters);
        }

        $allAudits = $parentAudits->merge($childAudits)->sortByDesc('created_at')->values();

        if ($allAudits->isNotEmpty()) {
            $allAudits->loadMorph('subject', [
                Inventory::class => ['productVariant.product'],
                Sale::class => ['client'],
                AccountReceivable::class => ['entity'],
                SaleDetail::class => ['productVariant.product'],
                PurchaseDetail::class => ['productVariant.product'],
                ProductVariant::class => ['product'],
                QuotationDetail::class => ['productVariant.product'],
                InventoryMovement::class => [],
            ]);
        }

        $presented = ActivityPresenter::presentCollection($allAudits);
        $translator = app(TranslationService::class);

        $activityCards = $presented->map(function ($dto) use ($translator) {
            $changes = [];

            foreach ($dto->changes as $change) {
                $label = $translator->translateAttribute($change->key);
                $changes[] = new AuditShowChangeRowData(
                    label: $label,
                    old: $this->formatValue($change->key, $change->old, $change->relatedModel),
                    new: $this->formatValue($change->key, $change->new, $change->relatedModel),
                );
            }

            $eventLabel = $dto->getEventLabel();

            if (
                $dto->activity->subject_type !== Sale::class &&
                $dto->activity->subject_type !== Product::class &&
                $dto->activity->subject_type !== Purchase::class &&
                $dto->activity->subject_type !== Quotation::class &&
                $dto->activity->subject_type !== AccountReceivable::class &&
                $dto->activity->subject_type !== Inventory::class &&
                $dto->activity->subject_type !== ProductAttribute::class &&
                $dto->activity->subject_type !== ProductVariant::class
            ) {
                $context = $translator->translateModel($dto->activity->subject_type);

                if ($dto->activity->subject_type === SaleDetail::class) {
                    $context = 'Detalle de Venta';
                } elseif ($dto->activity->subject_type === PurchaseDetail::class) {
                    $context = 'Detalle de Compra';
                } elseif ($dto->activity->subject_type === QuotationDetail::class) {
                    $context = 'Detalle de Cotización';
                } elseif ($dto->activity->subject_type === AccountReceivablePayment::class) {
                    $context = 'Pago';
                } elseif ($dto->activity->subject_type === InventoryMovement::class) {
                    $context = 'Movimiento';
                } elseif ($dto->activity->subject_type === ProductAttributeValue::class) {
                    $context = 'Valor';
                } elseif ($dto->activity->subject_type === ProductPriceHistory::class) {
                    $context = 'Historial de Precio';
                }

                $eventLabel .= ' ('.$context.')';
            }

            $date = '—';
            if ($dto->activity && $dto->activity->created_at) {
                $date = $dto->activity->created_at->format('d/m/Y H:i:s');
            }

            return new AuditShowActivityData(
                id: (int) $dto->activity->id,
                event: $eventLabel,
                userName: $dto->getCauserLabel(),
                date: $date,
                changes: $changes,
            );
        });

        return [
            'activities' => $activityCards,
            'subjectLabel' => $translator->translateModel($decodedType),
            'encodedSubjectType' => ActivityPresenter::encodeSubjectType($decodedType),
        ];
    }

    private function queryAudits(string $subjectType, array $subjectIds, array $filters): Collection
    {
        if (empty($subjectIds)) {
            return new Collection;
        }

        $query = Audit::with(['causer', 'subject'])
            ->where('subject_type', $subjectType)
            ->whereIn('subject_id', $subjectIds);

        if (! empty($filters['causer_id'])) {
            $query->where('causer_id', $filters['causer_id']);
        }
        if (! empty($filters['event'])) {
            $query->where('event', $filters['event']);
        }
        if (! empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }
        if (! empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        return $query->get();
    }

    private function getSaleChildAudits(int|string $saleId, array $filters): Collection
    {
        $sale = Sale::find($saleId);
        if (! $sale) {
            return new Collection;
        }

        $detailIds = $sale->saleDetails()->pluck('id')->toArray();

        return $this->queryAudits(SaleDetail::class, $detailIds, $filters);
    }

    private function getPurchaseChildAudits(int|string $purchaseId, array $filters): Collection
    {
        $purchase = Purchase::find($purchaseId);
        if (! $purchase) {
            return new Collection;
        }

        $detailIds = $purchase->details()->pluck('id')->toArray();

        return $this->queryAudits(PurchaseDetail::class, $detailIds, $filters);
    }

    private function getQuotationChildAudits(int|string $quotationId, array $filters): Collection
    {
        $quotation = Quotation::find($quotationId);
        if (! $quotation) {
            return new Collection;
        }

        $detailIds = $quotation->QuotationDetails()->pluck('id')->toArray();

        return $this->queryAudits(QuotationDetail::class, $detailIds, $filters);
    }

    private function getAccountReceivableChildAudits(int|string $arId, array $filters): Collection
    {
        $ar = AccountReceivable::find($arId);
        if (! $ar) {
            return new Collection;
        }

        $paymentIds = $ar->payments()->pluck('id')->toArray();

        return $this->queryAudits(AccountReceivablePayment::class, $paymentIds, $filters);
    }

    private function getInventoryChildAudits(int|string $inventoryId, array $filters): Collection
    {
        $inventory = Inventory::find($inventoryId);
        if (! $inventory) {
            return new Collection;
        }

        $movementIds = $inventory->inventoryMovements()->pluck('id')->toArray();

        return $this->queryAudits(InventoryMovement::class, $movementIds, $filters);
    }

    private function getProductChildAudits(int|string $productId, array $filters): Collection
    {
        return new Collection;
    }

    private function getProductVariantChildAudits(int|string $variantId, array $filters): Collection
    {
        $variant = ProductVariant::find($variantId);
        if (! $variant) {
            return new Collection;
        }

        $collections = new Collection;

        $historyIds = ProductPriceHistory::where('product_variant_id', $variantId)->pluck('id')->toArray();

        if (! empty($historyIds)) {
            $collections = $collections->merge(
                $this->queryAudits(ProductPriceHistory::class, $historyIds, $filters)
            );
        }

        return $collections;
    }

    private function getProductAttributeChildAudits(int|string $attributeId, array $filters): Collection
    {
        $attribute = ProductAttribute::find($attributeId);
        if (! $attribute) {
            return new Collection;
        }

        $valueIds = $attribute->values()->pluck('id')->toArray();

        if (! empty($valueIds)) {
            return $this->queryAudits(ProductAttributeValue::class, $valueIds, $filters);
        }

        return new Collection;
    }

    private function formatValue(string $key, mixed $value, ?\Illuminate\Database\Eloquent\Model $relatedModel): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        if (is_bool($value)) {
            return $value ? 'Sí' : 'No';
        }

        if (is_array($value)) {
            if (isset($value['amount']) && isset($value['currency'])) {
                return number_format((float) $value['amount'], 2, '.', ',').' '.$value['currency'];
            }

            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        if ($relatedModel && Str::endsWith($key, '_id')) {
            $config = config('activity-presenter');
            $labelAttributes = $config['label_attribute'] ?? [];
            $modelClass = get_class($relatedModel);

            $labelAttribute = $labelAttributes[$modelClass] ?? 'name';

            if (isset($relatedModel->$labelAttribute)) {
                return (string) $relatedModel->$labelAttribute;
            }

            return (string) (
                $relatedModel->name
                ?? $relatedModel->title
                ?? $relatedModel->label
                ?? $relatedModel->sku
                ?? $relatedModel->code
                ?? $value
            );
        }

        if (is_numeric($value) && ! Str::endsWith($key, '_id')) {
            $floatVal = (float) $value;
            if (floor($floatVal) == $floatVal) {
                return number_format($floatVal, 0, '.', ',');
            }

            return number_format($floatVal, 2, '.', ',');
        }

        return (string) $value;
    }
}
