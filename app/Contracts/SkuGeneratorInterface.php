<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Model;

interface SkuGeneratorInterface
{
    /**
     * Generate a code or SKU for a given model, optionally using a context model.
     *
     * @param Model $subject The primary model to generate the code for, or the context.
     * @param Model|null $target The specific target model (e.g. Variant) if $subject is the parent.
     * @return string
     */
    public function generate(Model $subject, ?Model $target = null): string;
}
