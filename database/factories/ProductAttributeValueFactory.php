<?php

namespace Database\Factories;

use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductAttributeValueFactory extends Factory
{
    protected $model = ProductAttributeValue::class;

    public function definition(): array
    {
        return [
            'product_attribute_id' => ProductAttribute::factory(),
            'value' => $this->faker->word(),
            'abbreviation' => $this->faker->lexify('??'),
        ];
    }
}
