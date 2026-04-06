<?php

namespace App\Services;

use Brick\Math\RoundingMode;
use Brick\Money\Money;

class TaxCalculatorService
{
    /**
     * Calcula los montos de una línea de producto/servicio.
     *
     * @return array{base: Money, tax_amount: Money, sub_total: Money, unit_tax_amount: Money}
     */
    public function calculateLineItem(Money $unitPrice, $quantity, Money $discountAmount, ?float $taxPercentage): array
    {
        $currencyContext = $unitPrice->getContext();
        $currencyCode = $unitPrice->getCurrency()->getCurrencyCode();

        $grossAmount = $unitPrice->multipliedBy($quantity, RoundingMode::HALF_UP);

        $lineBase = $grossAmount->minus($discountAmount);
        if ($lineBase->isNegative()) {
            $lineBase = Money::zero($currencyCode, $currencyContext);
        }

        $lineTax = Money::zero($currencyCode, $currencyContext);
        if ($taxPercentage) {
            $lineTax = $lineBase->multipliedBy($taxPercentage / 100, RoundingMode::HALF_UP);
        }

        $lineSubtotal = $lineBase->plus($lineTax);

        $unitTaxAmount = Money::zero($currencyCode, $currencyContext);
        if ($taxPercentage) {
            $unitTaxAmount = $unitPrice->multipliedBy($taxPercentage / 100, RoundingMode::HALF_UP);
        }

        return [
            'base' => $lineBase,
            'tax_amount' => $lineTax,
            'sub_total' => $lineSubtotal,
            'unit_tax_amount' => $unitTaxAmount,
        ];
    }

    /**
     * Calcula los totales finales de todas las líneas.
     *
     * @return array{total: Money, subTotal: Money, totalTax: Money, taxPercentageApplied: ?float}
     */
    public function calculateTotals(array $lines): array
    {
        if (empty($lines)) {
            return [
                'total' => Money::zero('NIO'),
                'totalTax' => Money::zero('NIO'),
                'taxPercentageApplied' => null,
            ];
        }

        $firstAmount = $lines[0]['sub_total'] ?? Money::zero('NIO');
        $currencyCode = $firstAmount instanceof Money ? $firstAmount->getCurrency()->getCurrencyCode() : 'NIO';

        $total = Money::zero($currencyCode);
        $totalTax = Money::zero($currencyCode);
        $taxPercentageApplied = null;

        foreach ($lines as $line) {
            $total = $total->plus($line['sub_total']);
            $totalTax = $totalTax->plus($line['tax_amount_total'] ?? $line['tax_amount']);

            if (isset($line['tax_percentage']) && $line['tax_percentage']) {
                $taxPercentageApplied = $line['tax_percentage'];
            }
        }

        return [
            'total' => $total,
            'subTotal' => $total->minus($totalTax),
            'totalTax' => $totalTax,
            'taxPercentageApplied' => $taxPercentageApplied,
        ];
    }
}
