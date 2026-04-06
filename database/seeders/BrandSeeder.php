<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brandData = [
            // Calzado
            ['name' => 'Nike', 'category' => 'Calzado'],
            ['name' => 'Adidas', 'category' => 'Calzado'],
            ['name' => 'Puma', 'category' => 'Calzado'],
            ['name' => 'Reebok', 'category' => 'Calzado'],
            ['name' => 'New Balance', 'category' => 'Calzado'],

            // Electrónica
            ['name' => 'Samsung', 'category' => 'Electrónica'],
            ['name' => 'Apple', 'category' => 'Electrónica'],

            // Muebles
            ['name' => 'Ikea', 'category' => 'Muebles'],

            // Ropa
            ['name' => 'Gucci', 'category' => 'Ropa'],
            ['name' => 'Prada', 'category' => 'Ropa'],
            ['name' => 'Versace', 'category' => 'Ropa'],
            ['name' => 'Armani', 'category' => 'Ropa'],
            ['name' => 'Zara', 'category' => 'Ropa'],
            ['name' => 'H&M', 'category' => 'Ropa'],

            // Belleza
            ['name' => 'Chanel', 'category' => 'Belleza'],
            ['name' => 'Dior', 'category' => 'Belleza'],
            ['name' => 'MAC', 'category' => 'Belleza'],
            ['name' => 'Lancôme', 'category' => 'Belleza'],
            ['name' => 'Estée Lauder', 'category' => 'Belleza'],

            // Accesorios
            ['name' => 'Louis Vuitton', 'category' => 'Accesorios'],
            ['name' => 'Hermès', 'category' => 'Accesorios'],
            ['name' => 'Rolex', 'category' => 'Accesorios'],
            ['name' => 'Cartier', 'category' => 'Accesorios'],
            ['name' => 'Ray-Ban', 'category' => 'Accesorios'],

            // Bebidas
            ['name' => 'Coca-Cola', 'category' => 'Bebidas'],
            ['name' => 'Pepsi', 'category' => 'Bebidas'],
            ['name' => 'Evian', 'category' => 'Bebidas'],
            ['name' => 'Tropicana', 'category' => 'Bebidas'],
        ];

        foreach ($brandData as $data) {
            $category = Category::where('name', $data['category'])->first();
            Brand::firstOrCreate(
                ['name' => $data['name']],
                ['category_id' => $category ? $category->id : null]
            );
        }
    }
}
