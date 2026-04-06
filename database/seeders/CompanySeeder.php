<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Company::firstOrCreate([
            'name' => 'Inventario y Facturación',
            'ruc' => null,
            'logo' => null,
            'description' => 'Sistema de Inventario y Facturación.',
            'address' => null,
            'phone' => null,
            'email' => null,
        ]);
    }
}
