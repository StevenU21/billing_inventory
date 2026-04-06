<?php

namespace Database\Factories;

use App\Models\ProductPriceHistory;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductPriceHistoryFactory extends Factory
{
    protected $model = ProductPriceHistory::class;

    public function definition(): array
    {
        $oldPrice = $this->faker->numberBetween(100, 500);
        $newPrice = $oldPrice + $this->faker->numberBetween(-50, 50);

        return [
            'product_variant_id' => ProductVariant::factory(),
            'user_id' => User::factory(),
            'old_price' => $oldPrice,
            'new_price' => $newPrice,
            'old_cost' => $oldPrice * 0.7,
            'new_cost' => $newPrice * 0.7,
            'notes' => $this->faker->sentence(),
            'currency' => 'NIO',
        ];
    }
}
