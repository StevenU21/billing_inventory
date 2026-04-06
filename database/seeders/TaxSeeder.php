<?php

namespace Database\Seeders;

use App\Models\Tax;
use Illuminate\Database\Seeder;

class TaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $taxes = [
            ['name' => 'IVA', 'percentage' => 15],
            ['name' => 'Exento', 'percentage' => 0],
        ];

        foreach ($taxes as $tax) {
            Tax::firstOrCreate([
                'name' => $tax['name'],
                'percentage' => $tax['percentage'],
            ]);
        }
    }
}
