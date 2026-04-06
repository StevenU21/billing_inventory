<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $methods = [
            [
                'name' => 'Efectivo',
                'is_cash' => true,
            ],
            [
                'name' => 'Tarjeta de crédito',
                'is_cash' => false,
            ],
            [
                'name' => 'Tarjeta de débito',
                'is_cash' => false,
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::firstOrCreate(
                ['name' => $method['name']],
                $method
            );
        }
    }
}
