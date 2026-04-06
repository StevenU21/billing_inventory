<?php

namespace Database\Seeders;

use App\Enums\ProductStatus;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductVariant;
use App\Models\Tax;
use App\Models\UnitMeasure;
use Brick\Money\Money;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $taxes = Tax::pluck('id', 'name')->toArray();
        $units = UnitMeasure::pluck('id', 'name')->toArray();

        // Categoría y Marca por defecto
        $defaultCategory = Category::firstOrCreate(['name' => 'General']);
        $genericBrand = Brand::firstOrCreate(
            ['name' => 'Genérico'],
            ['category_id' => $defaultCategory->id]
        );

        $getBrand = function ($name) use ($genericBrand, $defaultCategory) {
            if (! $name) {
                return $genericBrand->id;
            }

            return Brand::firstOrCreate(
                ['name' => $name],
                ['category_id' => $defaultCategory->id]
            )->id;
        };

        // =====================================================================
        // PASO 1: Cargar Atributos Existentes (creados por AttributeSeeder)
        // =====================================================================
        $attributeValues = []; // Cache: $attributeValues['Talla']['S'] = ProductAttributeValue model

        // Cargar todos los atributos con sus valores
        $attributes = ProductAttribute::with('values')->get();

        foreach ($attributes as $attribute) {
            $attributeValues[$attribute->name] = [];

            foreach ($attribute->values as $value) {
                $attributeValues[$attribute->name][$value->value] = $value;
            }
        }

        // =====================================================================
        // PASO 2: Productos Simples (Sin Variantes) - UNIDADES FRACCIONARIAS
        // =====================================================================
        $simpleProducts = [
            [
                'name' => 'Arroz',
                'description' => 'Arroz de grano largo de alta calidad.',
                'brand' => 'La Garza',
                'unit_measure' => 'Libra',
                'tax' => 'Exento',
                'price' => 18,
                'cost' => 12,
            ],
            [
                'name' => 'Azúcar Blanca',
                'description' => 'Azúcar refinada de caña.',
                'brand' => 'San Antonio',
                'unit_measure' => 'Libra',
                'tax' => 'Exento',
                'price' => 12,
                'cost' => 8,
            ],
            [
                'name' => 'Aceite Vegetal',
                'description' => 'Aceite vegetal 100% puro para cocinar.',
                'brand' => 'Mazola',
                'unit_measure' => 'Litro',
                'tax' => 'Exento',
                'price' => 85,
                'cost' => 60,
            ],
            [
                'name' => 'Leche Entera',
                'description' => 'Leche pasteurizada entera.',
                'brand' => 'Parmalat',
                'unit_measure' => 'Litro',
                'tax' => 'Exento',
                'price' => 45,
                'cost' => 30,
            ],
            [
                'name' => 'Frijoles Rojos',
                'description' => 'Frijoles rojos de primera calidad.',
                'brand' => null,
                'unit_measure' => 'Libra',
                'tax' => 'Exento',
                'price' => 22,
                'cost' => 15,
            ],
        ];

        foreach ($simpleProducts as $pData) {
            $namePrefix = $this->getSafePrefix($pData['name']);

            $product = Product::firstOrCreate([
                'name' => $pData['name'],
            ], [
                'description' => $pData['description'],
                'code' => 'PRO-'.$namePrefix.'-'.rand(100, 999),
                'status' => ProductStatus::Available,
                'brand_id' => $getBrand($pData['brand']),
                'tax_id' => $taxes[$pData['tax']] ?? 1,
                'unit_measure_id' => $units[$pData['unit_measure']] ?? $units['Unidad'] ?? 1,
            ]);

            $priceMoney = Money::of($pData['price'], 'NIO');
            $costMoney = Money::of($pData['cost'], 'NIO');
            $creditPriceMoney = Money::of($pData['price'] * 1.25, 'NIO');

            ProductVariant::firstOrCreate([
                'product_id' => $product->id,
            ], [
                'sku' => $namePrefix.'-'.rand(10000, 99999),
                'barcode' => fake()->ean13(),
                'price' => $priceMoney,
                'cost' => $costMoney,
                'credit_price' => $creditPriceMoney,
                'currency' => 'NIO',
            ]);
        }

        // =====================================================================
        // PASO 3: Productos CON Variantes
        // =====================================================================
        $variantProducts = [
            [
                'name' => 'Camisa Formal',
                'description' => 'Camisa de vestir de alta calidad.',
                'brand' => 'Gucci',
                'unit_measure' => 'Unidad',
                'tax' => 'IVA',
                'base_price' => 1200,
                'base_cost' => 700,
                'variants' => [
                    ['attrs' => ['Talla' => 'S', 'Color' => 'Blanco'], 'price_mod' => 0],
                    ['attrs' => ['Talla' => 'M', 'Color' => 'Blanco'], 'price_mod' => 0],
                    ['attrs' => ['Talla' => 'L', 'Color' => 'Blanco'], 'price_mod' => 0],
                    ['attrs' => ['Talla' => 'S', 'Color' => 'Azul'], 'price_mod' => 50],
                    ['attrs' => ['Talla' => 'M', 'Color' => 'Azul'], 'price_mod' => 50],
                    ['attrs' => ['Talla' => 'L', 'Color' => 'Azul'], 'price_mod' => 50],
                ],
            ],
            [
                'name' => 'Zapatillas Deportivas',
                'description' => 'Zapatillas para alto rendimiento.',
                'brand' => 'Nike',
                'unit_measure' => 'Unidad',
                'tax' => 'IVA',
                'base_price' => 3500,
                'base_cost' => 2100,
                'variants' => [
                    ['attrs' => ['Talla' => 'S', 'Color' => 'Rojo'], 'price_mod' => 0],
                    ['attrs' => ['Talla' => 'M', 'Color' => 'Rojo'], 'price_mod' => 0],
                    ['attrs' => ['Talla' => 'L', 'Color' => 'Negro'], 'price_mod' => 100],
                    ['attrs' => ['Talla' => 'XL', 'Color' => 'Negro'], 'price_mod' => 150],
                ],
            ],
            [
                'name' => 'Tela para Confección',
                'description' => 'Tela de alta calidad para costura.',
                'brand' => null,
                'unit_measure' => 'Metro',
                'tax' => 'IVA',
                'base_price' => 120,
                'base_cost' => 75,
                'variants' => [
                    ['attrs' => ['Material' => 'Algodón', 'Color' => 'Blanco'], 'price_mod' => 0],
                    ['attrs' => ['Material' => 'Algodón', 'Color' => 'Azul'], 'price_mod' => 0],
                    ['attrs' => ['Material' => 'Poliéster', 'Color' => 'Negro'], 'price_mod' => 20],
                    ['attrs' => ['Material' => 'Seda', 'Color' => 'Rojo'], 'price_mod' => 180],
                ],
            ],
            [
                'name' => 'Coca-Cola',
                'description' => 'Refresco de cola original.',
                'brand' => 'Coca-Cola',
                'unit_measure' => 'Unidad',
                'tax' => 'IVA',
                'base_price' => 25,
                'base_cost' => 15,
                'variants' => [
                    ['attrs' => ['Volumen' => '355ml'], 'price_mod' => 0],
                    ['attrs' => ['Volumen' => '500ml'], 'price_mod' => 5],
                    ['attrs' => ['Volumen' => '1L'], 'price_mod' => 20],
                    ['attrs' => ['Volumen' => '2L'], 'price_mod' => 45],
                    ['attrs' => ['Volumen' => '3L'], 'price_mod' => 70],
                ],
            ],
            [
                'name' => 'Galletas Waffle',
                'description' => 'Paquete de galletas tipo waffle crujientes.',
                'brand' => 'Waffle',
                'unit_measure' => 'Unidad',
                'tax' => 'IVA',
                'base_price' => 45,
                'base_cost' => 28,
                'variants' => [
                    ['attrs' => ['Contenido' => '20 unidades'], 'price_mod' => 0],
                    ['attrs' => ['Contenido' => '26 unidades'], 'price_mod' => 15],
                    ['attrs' => ['Contenido' => '32 unidades'], 'price_mod' => 30],
                ],
            ],
            [
                'name' => 'Galletas Oreo',
                'description' => 'Galletas de chocolate con relleno de crema.',
                'brand' => 'Oreo',
                'unit_measure' => 'Unidad',
                'tax' => 'IVA',
                'base_price' => 35,
                'base_cost' => 22,
                'variants' => [
                    ['attrs' => ['Contenido' => '20 unidades'], 'price_mod' => 0],
                    ['attrs' => ['Contenido' => '26 unidades'], 'price_mod' => 12],
                    ['attrs' => ['Contenido' => '32 unidades'], 'price_mod' => 25],
                ],
            ],
            [
                'name' => 'Caja de Espirales',
                'description' => 'Caja de espirales decorativos para manualidades.',
                'brand' => null,
                'unit_measure' => 'Unidad',
                'tax' => 'IVA',
                'base_price' => 60,
                'base_cost' => 35,
                'variants' => [
                    ['attrs' => ['Color' => 'Verde'], 'price_mod' => 0],
                    ['attrs' => ['Color' => 'Morado'], 'price_mod' => 0],
                ],
            ],
        ];

        foreach ($variantProducts as $pData) {
            $namePrefix = $this->getSafePrefix($pData['name']);

            $product = Product::firstOrCreate([
                'name' => $pData['name'],
            ], [
                'description' => $pData['description'],
                'code' => 'PRO-'.$namePrefix.'-'.rand(100, 999),
                'status' => ProductStatus::Available,
                'brand_id' => $getBrand($pData['brand']),
                'tax_id' => $taxes[$pData['tax']] ?? 1,
                'unit_measure_id' => $units[$pData['unit_measure']] ?? $units['Unidad'] ?? 1,
            ]);

            foreach ($pData['variants'] as $variantConfig) {
                // Check if duplicate variant based on attributes is tricky,
                // but usually seeding runs on empty DBs or we accept simple duplication if run multiple times.
                // However, ProductVariant::create will duplicate if we don't check.
                // Let's rely on Create for now as checking attributes match logic is complex here.
                // Or better, we can just Create, assuming it's a seed.

                $finalPrice = $pData['base_price'] + ($variantConfig['price_mod'] ?? 0);
                $finalCost = $pData['base_cost'] + (($variantConfig['price_mod'] ?? 0) * 0.7);

                $priceMoney = Money::of($finalPrice, 'NIO');
                $costMoney = Money::of($finalCost, 'NIO');
                $creditPriceMoney = Money::of($finalPrice * 1.25, 'NIO');

                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $namePrefix.'-'.rand(10000, 99999),
                    'barcode' => fake()->ean13(),
                    'price' => $priceMoney,
                    'cost' => $costMoney,
                    'credit_price' => $creditPriceMoney,
                    'currency' => 'NIO',
                ]);

                foreach ($variantConfig['attrs'] as $attrName => $attrValueName) {
                    if (isset($attributeValues[$attrName][$attrValueName])) {
                        $attrValueModel = $attributeValues[$attrName][$attrValueName];
                        $variant->attributeValues()->attach($attrValueModel->id);
                    }
                }
            }
        }
    }

    private function getSafePrefix(string $name): string
    {
        $prefix = mb_substr($name, 0, 3, 'UTF-8');
        $prefix = mb_strtoupper($prefix, 'UTF-8');

        return iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $prefix) ?: 'PRD';
    }
}
