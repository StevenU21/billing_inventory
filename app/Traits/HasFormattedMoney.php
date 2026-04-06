<?php

namespace App\Traits;

use Brick\Money\Money;
use Illuminate\Support\Str;

trait HasFormattedMoney
{
    /**
     * Intercept attribute access to handle "formatted_" prefix for Money attributes.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (str_starts_with($key, 'formatted_')) {
            $attributeName = Str::after($key, 'formatted_');

            $value = $this->resolveMoneyValue($attributeName);

            if ($value instanceof Money) {
                return $value->formatTo($this->getMoneyLocale());
            }

            if ($this->isMoneyAttribute($attributeName) || $this->isMoneyAttribute(Str::snake($attributeName))) {
                return "{$this->getCurrencySymbol()} 0.00";
            }
        }

        return parent::getAttribute($key);
    }

    /**
     * Resolve the underlying money value, supporting camelCase, snake_case and aliases.
     */
    protected function resolveMoneyValue(string $name): mixed
    {
        $value = parent::getAttribute($name);
        if ($value instanceof Money) {
            return $value;
        }

        $snake = Str::snake($name);
        if ($snake !== $name) {
            $value = parent::getAttribute($snake);
            if ($value instanceof Money) {
                return $value;
            }
        }

        $aliases = [
            'tax' => 'tax_amount',
            'discount' => 'discount_amount',
            'subtotal' => 'sub_total',
        ];

        if (array_key_exists($name, $aliases)) {
            $value = parent::getAttribute($aliases[$name]);
            if ($value instanceof Money) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Get the locale for money formatting.
     */
    protected function getMoneyLocale(): string
    {
        return $this->moneyLocale ?? 'es_NI';
    }

    /**
     * Get the default currency symbol for fallbacks.
     */
    protected function getCurrencySymbol(): string
    {
        return $this->currencySymbol ?? 'C$';
    }

    /**
     * Determine if an attribute is a money attribute via casts.
     */
    protected function isMoneyAttribute(string $attribute): bool
    {
        $cast = $this->getCasts()[$attribute] ?? null;

        if (! $cast) {
            return false;
        }

        if (is_string($cast)) {
            return str_contains($cast, 'MoneyCast');
        }

        return false;
    }

    /**
     * Format a Money object or null value to string
     *
     * @param  Money|null  $money
     */
    public function formatMoney($money): string
    {
        if ($money instanceof Money) {
            return $money->formatTo($this->getMoneyLocale());
        }

        return "{$this->getCurrencySymbol()} 0.00";
    }
}
