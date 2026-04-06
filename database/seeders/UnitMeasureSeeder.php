<?php

namespace Database\Seeders;

use App\Models\UnitMeasure;
use Illuminate\Database\Seeder;

class UnitMeasureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            // Unidades discretas (no permiten decimales)
            ['name' => 'Unidad', 'symbol' => 'und', 'allows_decimals' => false],
            ['name' => 'Caja', 'symbol' => 'caja', 'allows_decimals' => false],
            ['name' => 'Paquete', 'symbol' => 'paq', 'allows_decimals' => false],

            // Unidades continuas de peso (permiten decimales)
            ['name' => 'Kilogramo', 'symbol' => 'kg', 'allows_decimals' => true],
            ['name' => 'Libra', 'symbol' => 'lb', 'allows_decimals' => true],

            // Unidades continuas de volumen (permiten decimales)
            ['name' => 'Litro', 'symbol' => 'L', 'allows_decimals' => true],
            ['name' => 'Mililitro', 'symbol' => 'ml', 'allows_decimals' => true],

            // Unidades continuas de longitud (permiten decimales)
            ['name' => 'Metro', 'symbol' => 'm', 'allows_decimals' => true],
            ['name' => 'Centímetro', 'symbol' => 'cm', 'allows_decimals' => true],
        ];

        foreach ($units as $unit) {
            UnitMeasure::firstOrCreate(
                ['name' => $unit['name']],
                [
                    'symbol' => $unit['symbol'],
                    'allows_decimals' => $unit['allows_decimals'],
                ]
            );
        }
    }
}
