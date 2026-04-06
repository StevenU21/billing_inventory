<?php

namespace App\Services;

use App\DTOs\InventoryMovementData;
use App\DTOs\SaleData;
use App\Enums\InventoryMovementType;
use App\Enums\QuotationStatus;
use App\Enums\SaleStatus;
use App\Events\SaleCancelled;
use App\Events\SaleCreated;
use App\Exceptions\BusinessLogicException;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleDetail;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Brick\Money\Money;
use Illuminate\Support\Facades\DB;

class SaleService
{
    public const RELATIONS = [
        'saleDetails.productVariant.product.tax',
        'user',
        'client',
        'paymentMethod',
    ];

    public function __construct(
        protected TaxCalculatorService $taxCalculator,
        protected InventoryService $inventoryService,
        protected AccountReceivableService $accountReceivableService,
        protected CashRegisterService $cashRegisterService
    ) {}

    public function createSale(SaleData $data): Sale
    {
        return DB::transaction(function () use ($data) {
            $detailsData = $this->calculateDetails($data->items, $data->isCredit);
            $totals = $this->calculateTotals($detailsData);

            $sale = $this->createSaleRecord($data, $data->userId, $totals);

            foreach ($detailsData as $d) {
                $detail = $this->createSaleDetail($sale, $d);

                $movementData = new InventoryMovementData(
                    inventoryId: $d['inventory_id'],
                    type: InventoryMovementType::Sale,
                    quantity: $d['quantity'],
                    currency: $d['currency'],
                    notes: "Venta #{$sale->id}"
                );

                $this->inventoryService->processMovement(
                    data: $movementData,
                    userId: $data->userId,
                    sourceType: SaleDetail::class,
                    sourceId: $detail->id
                );
            }

            if ($sale->is_credit) {
                $this->accountReceivableService->createFromSale($sale);
            } else {
                $this->cashRegisterService->recordSaleMovement(
                    saleId: $sale->id,
                    amount: $sale->total,
                    userId: $data->userId,
                    paymentMethodId: $data->paymentMethodId
                );
            }

            SaleCreated::dispatch($sale);

            $sale->load(self::RELATIONS);

            return $sale;
        });
    }

    public function cancelSale(Sale $sale): void
    {
        DB::transaction(function () use ($sale) {
            /** @var Sale $lockedSale */
            $lockedSale = Sale::with('accountReceivable.payments')->lockForUpdate()->find($sale->id);

            if ($lockedSale->status === SaleStatus::Cancelled) {
                throw new BusinessLogicException('La venta ya está anulada.', 'status');
            }

            if ($lockedSale->id !== Sale::max('id')) {
                throw new BusinessLogicException('Solo se puede anular la última venta registrada.', 'id');
            }

            if ($lockedSale->is_credit) {
                $this->accountReceivableService->deleteForSale($lockedSale);
            } else {
                $this->cashRegisterService->recordRefundMovement(
                    saleId: $lockedSale->id,
                    amount: $lockedSale->total,
                    userId: auth()->id() ?? $sale->user_id,
                    paymentMethodId: $lockedSale->payment_method_id
                );
            }

            foreach ($lockedSale->saleDetails as $detail) {
                $inventory = $this->inventoryService->getInventoryForVariant($detail->product_variant_id);

                $originalCost = $detail->unit_cost ? Money::ofMinor($detail->unit_cost, $detail->currency) : null;
                $reentryCost = $originalCost ?? ($inventory?->average_cost ?? Money::zero($detail->currency));

                $movementData = new InventoryMovementData(
                    type: InventoryMovementType::SaleCancellation,
                    quantity: BigDecimal::of($detail->quantity),
                    currency: $detail->currency,
                    inventoryId: $inventory?->id,
                    productVariantId: $detail->product_variant_id,
                    unitPrice: BigDecimal::of($reentryCost->getAmount()),
                    notes: "Anulación Venta #{$lockedSale->id}"
                );

                $this->inventoryService->processMovement(
                    data: $movementData,
                    userId: auth()->id() ?? $sale->user_id,
                    sourceType: SaleDetail::class,
                    sourceId: $detail->id
                );
            }

            $lockedSale->update(['status' => SaleStatus::Cancelled]);

            if ($lockedSale->quotation_id) {
                $lockedSale->quotation()->update(['status' => QuotationStatus::Pending]);
            }

            SaleCancelled::dispatch($lockedSale);
        });
    }

    // --- Helpers Privados ---
    private function calculateDetails(iterable $items, bool $isCredit = false): array
    {
        $itemsCollection = collect($items);
        $variantIds = $itemsCollection->pluck('productVariantId')->toArray();
        $variants = ProductVariant::with(['product.tax', 'product.unitMeasure'])
            ->whereIn('id', $variantIds)
            ->get()
            ->keyBy('id');
        $inventories = $this->inventoryService->getInventoriesForVariants($variantIds, false);

        $detailsData = [];

        foreach ($items as $item) {
            $variant = $variants->get($item->productVariantId);

            if (! $variant) {
                throw new BusinessLogicException("Variante ID {$item->productVariantId} inválida o no encontrada.", 'items');
            }

            $inventory = $inventories
                ->where('product_variant_id', $variant->id)
                ->first();

            if (! $inventory) {
                throw new BusinessLogicException("No existe registro de inventario para {$variant->product?->name}.", 'items');
            }

            $this->inventoryService->checkStock($inventory, $item->quantity, $variant->product?->name);

            if ($item->customUnitPrice !== null) {
                if ($item->customUnitPrice->getCurrency()->getCurrencyCode() !== $inventory->currency) {
                    throw new BusinessLogicException("Error de moneda en precio personalizado: {$item->customUnitPrice->getCurrency()->getCurrencyCode()} vs {$inventory->currency}", 'items');
                }
                $unitSale = $item->customUnitPrice;
            } else {
                if ($isCredit) {
                    $unitSale = $variant->credit_price ?? ($variant->price ?? Money::zero($inventory->currency));
                } else {
                    $unitSale = $inventory->sale_price ?? ($variant->price ?? Money::zero($inventory->currency));
                }
            }

            $product = $variant->product;
            $taxPercentage = $product?->tax ? (float) $product->tax->percentage : 0;

            $discountAmount = Money::zero($unitSale->getCurrency()->getCurrencyCode());

            if ($item->discount && $item->discountPercentage) {
                $discountAmount = $unitSale->multipliedBy($item->discountPercentage->toFloat() / 100, RoundingMode::HALF_UP);
            }

            $calc = $this->taxCalculator->calculateLineItem(
                $unitSale,
                $item->quantity,
                $discountAmount,
                $taxPercentage
            );

            $detailsData[] = [
                'variant' => $variant,
                'inventory_id' => $inventory->id,
                'quantity' => $item->quantity,
                'unit_price' => $unitSale,
                'sub_total' => $calc['sub_total'],
                'discount' => $item->discount,
                'discount_percentage' => $item->discountPercentage,
                'discount_amount' => $discountAmount,
                'unit_tax_amount' => $calc['unit_tax_amount'],
                'tax_percentage' => $taxPercentage,
                'tax_amount_total' => $calc['tax_amount'],
                'currency' => $unitSale->getCurrency()->getCurrencyCode(),
                'unit_cost' => $inventory->average_cost ?? Money::zero($inventory->currency),
            ];
        }

        return $detailsData;
    }

    private function calculateTotals(array $detailsData): array
    {
        return $this->taxCalculator->calculateTotals($detailsData);
    }

    private function createSaleRecord(SaleData $data, $userId, array $totals): Sale
    {
        return Sale::create([
            'total' => $totals['total'],
            'is_credit' => $data->isCredit,
            'sub_total' => $totals['subTotal'],
            'tax_amount' => $totals['totalTax'],
            'status' => SaleStatus::Completed,
            'sale_date' => $data->saleDate,
            'currency' => $data->currency,
            'user_id' => $userId,
            'client_id' => $data->clientId,
            'payment_method_id' => $data->paymentMethodId,
            'quotation_id' => $data->quotationId,
        ]);
    }

    private function createSaleDetail(Sale $sale, array $d): SaleDetail
    {
        return $sale->saleDetails()->create([
            'quantity' => $d['quantity'],
            'unit_price' => $d['unit_price'],
            'sub_total' => $d['sub_total'],
            'discount' => $d['discount'],
            'discount_percentage' => $d['discount_percentage']?->toFloat(),
            'discount_amount' => $d['discount_amount'],
            'tax_percentage' => $d['tax_percentage'],
            'tax_amount' => $d['tax_amount_total'],
            'currency' => $d['currency'],
            'product_variant_id' => $d['variant']->id,
            'unit_cost' => $d['unit_cost']->getMinorAmount()->toInt(),
        ]);
    }
}
