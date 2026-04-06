<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Profile;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function profileAvatar(Profile $profile)
    {
        if (!$profile->avatar || !Storage::disk('local')->exists($profile->avatar)) {
            abort(404);
        }
        return response()->file(Storage::disk('local')->path($profile->avatar));
    }

    public function companyLogo(Company $company)
    {
        if (!$company->logo || !Storage::disk('local')->exists($company->logo)) {
            abort(404);
        }
        return response()->file(Storage::disk('local')->path($company->logo));
    }

    public function productImage(Product $product)
    {
        if (!$product->image || !Storage::disk('local')->exists($product->image)) {
            abort(404);
        }
        return response()->file(Storage::disk('local')->path($product->image));
    }

    public function variantImage(ProductVariant $productVariant)
    {
        if (!$productVariant->image || !Storage::disk('local')->exists($productVariant->image)) {
            abort(404);
        }
        return response()->file(Storage::disk('local')->path($productVariant->image));
    }
}
