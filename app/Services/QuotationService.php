<?php

namespace App\Services;

use App\DTOs\QuotationData;
use App\DTOs\SaleData;
use App\DTOs\SaleItemData;
use App\Enums\QuotationStatus;
use App\Exceptions\BusinessLogicException;
use App\Models\Entity;
use App\Models\ProductVariant;
use App\Models\Quotation;
use App\Models\Sale;
use App\Services\InventoryService;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Brick\Money\Money;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Native\Desktop\Facades\Settings;

class QuotationService
{
    public function __construct(
        protected TaxCalculatorService $taxCalculator,
        protected SaleService $saleService,
        protected InventoryService $inventoryService
    ) {
    }

    /**
     * Calcula los detalles y totales de una cotización sin persistirla.
     *
     * @return array{client: Entity, details: array, totals: array, quotation_date: string}
     */
    public function calculateQuotation(QuotationData $data): array
    {
        $details = $this->calculateDetails($data->items, $data->currency);
        $totals = $this->calculateTotals($details);

        $client = Entity::find($data->clientId);
        if (!$client) {
            throw new BusinessLogicException('Cliente no encontrado.', 'client_id');
        }

        $totals['sub_total'] = ($totals['total'] instanceof Money)
            ? $totals['total']->minus($totals['totalTax'] ?? 0)
            : Money::zero($data->currency);

        return [
            'client' => $client,
            'details' => $details,
            'totals' => $totals,
            'quotation_date' => now()->toDateString(),
        ];
    }

    public function createQuotation(QuotationData $data): Quotation
    {
        $details = $this->calculateDetails($data->items, $data->currency);
        $totals = $this->calculateTotals($details);

        $validityDays = (int) Settings::get('quotation_validity_days', 15);
        // Ensure it's valid until the END of that day
        $validUntil = CarbonImmutable::now()->addDays($validityDays)->endOfDay();

        return DB::transaction(function () use ($data, $details, $totals, $validUntil) {
            $quotation = Quotation::create([
                'total' => $totals['total'],
                'sub_total' => $totals['subTotal'],
                'tax_amount' => $totals['totalTax'],
                'status' => QuotationStatus::Pending,
                'valid_until' => $validUntil,
                'date_issued' => CarbonImmutable::now(),
                'user_id' => $data->userId,
                'client_id' => $data->clientId,
                'currency' => $data->currency,
            ]);

            foreach ($details as $d) {
                $quotation->quotationDetails()->create([
                    'product_variant_id' => $d['variant']->id,
                    'quantity' => $d['quantity'],
                    'unit_price' => $d['unit_price'],
                    'discount' => $d['discount'],
                    'discount_percentage' => $d['discount_percentage'],
                    'discount_amount' => $d['discount_amount'],
                    'tax_percentage' => $d['tax_percentage'],
                    'tax_amount' => $d['tax_amount_total'],
                    'sub_total' => $d['sub_total'],
                ]);
            }

            return $quotation->load(['client', 'quotationDetails.productVariant.product', 'user']);
        });
    }

    public function acceptQuotation(Quotation $quotation, int $userId): Sale
    {
        if ($quotation->status !== QuotationStatus::Pending) {
            throw new BusinessLogicException("Solo se pueden aceptar cotizaciones pendientes.", 'status');
        }

        $items = $quotation->quotationDetails->map(fn($d) => new SaleItemData(
            productVariantId: $d->product_variant_id,
            quantity: BigDecimal::of($d->quantity),
            discount: (bool) $d->discount,
            discountPercentage: $d->discount_percentage ? BigDecimal::of($d->discount_percentage) : null,
            customUnitPrice: $d->unit_price,
        ));

        $saleData = new SaleData(
            userId: $userId,
            clientId: $quotation->client_id,
            isCredit: false,
            currency: $quotation->currency,
            saleDate: CarbonImmutable::now(),
            paymentMethodId: null,
            items: $items,
            quotationId: $quotation->id
        );

        return DB::transaction(function () use ($saleData, $quotation) {

            $lockedQuotation = Quotation::where('id', $quotation->id)->lockForUpdate()->first();

            if ($lockedQuotation->status !== QuotationStatus::Pending) {
                throw new BusinessLogicException("La cotización ha cambiado de estado y ya no puede ser aceptada.", 'status');
            }

            $sale = $this->saleService->createSale($saleData);

            $lockedQuotation->update(['status' => QuotationStatus::Accepted]);

            return $sale;
        });
    }

    public function cancelQuotation(Quotation $quotation): void
    {
        DB::transaction(function () use ($quotation) {
            $lockedQuotation = Quotation::where('id', $quotation->id)->lockForUpdate()->first();

            if ($lockedQuotation->status === QuotationStatus::Accepted) {
                throw new BusinessLogicException("No se puede cancelar una cotización ya aceptada.", 'status');
            }

            $lockedQuotation->update(['status' => QuotationStatus::Rejected]);
        });
    }

    // --- Helpers ---

    private function calculateDetails(iterable $items, string $currency): array
    {
        $itemsCollection = collect($items);
        $variantIds = $itemsCollection->pluck('productVariantId')->toArray();

        $variants = ProductVariant::with('product.tax')
            ->whereIn('id', $variantIds)
            ->get()
            ->keyBy('id');

        $details = [];

        foreach ($items as $item) {
            $variant = $variants->get($item->productVariantId);

            if (!$variant) {
                throw new BusinessLogicException("Variante ID {$item->productVariantId} no existe.", 'items');
            }

            $unitPrice = $variant->price ?? Money::zero($currency);

            $taxPercentage = $variant->product?->tax?->percentage ? (float) $variant->product->tax->percentage : 0;

            $discountAmount = Money::zero($currency);
            if ($item->discount && $item->discountPercentage) {
                $discountAmount = $unitPrice->multipliedBy(
                    $item->discountPercentage->toFloat() / 100,
                    RoundingMode::HALF_UP
                );
            }

            $calc = $this->taxCalculator->calculateLineItem(
                $unitPrice,
                $item->quantity->toFloat(),
                $discountAmount,
                $taxPercentage
            );

            $details[] = [
                'variant' => $variant,
                'quantity' => $item->quantity->toFloat(),
                'unit_price' => $unitPrice,
                'sub_total' => $calc['sub_total'],
                'discount' => $item->discount,
                'discount_percentage' => $item->discountPercentage?->toFloat(),
                'discount_amount' => $discountAmount,
                'tax_percentage' => $taxPercentage,
                'tax_amount_total' => $calc['tax_amount'],
            ];
        }

        return $details;
    }

    private function calculateTotals(array $details): array
    {
        return $this->taxCalculator->calculateTotals($details);
    }
}
