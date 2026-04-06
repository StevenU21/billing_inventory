<?php

namespace App\Services;

use App\Contracts\SkuGeneratorInterface;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SkuGeneratorService implements SkuGeneratorInterface
{
    public function generate(Model $subject, ?Model $target = null): string
    {
        $prefix = 'PROD';

        if ($target instanceof ProductVariant && $subject->code) {
            $prefix = $subject->code;
        } elseif (!empty($subject->name)) {
            $prefix = strtoupper(substr(Str::slug($subject->name), 0, 3));
        }

        $random = strtoupper(Str::random(4));

        return "{$prefix}-{$random}";
    }
}
