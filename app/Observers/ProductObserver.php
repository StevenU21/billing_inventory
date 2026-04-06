<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\Brand;
use Illuminate\Support\Str;

class ProductObserver
{
    public function creating(Product $product): void
    {
        if (empty($product->code)) {
            $brand = Brand::find($product->brand_id);
            $prefix = $brand ? strtoupper(substr(Str::slug($brand->name), 0, 4)) : 'PROD';

            if (strlen($prefix) < 3) {
                $prefix = str_pad($prefix, 3, 'X');
            }

            $latestProduct = Product::where('code', 'like', "{$prefix}-%")
                ->orderByRaw('LENGTH(code) DESC')
                ->orderBy('code', 'desc')
                ->first();

            $nextNumber = 1;
            if ($latestProduct) {
                $parts = explode('-', $latestProduct->code);
                if (count($parts) >= 2 && is_numeric(end($parts))) {
                    $nextNumber = (int) end($parts) + 1;
                }
            }

            $product->code = $prefix . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        }
    }
}
