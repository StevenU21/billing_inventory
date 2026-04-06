<?php

namespace Database\Seeders;

use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attributesData = [
            'Talla' => ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL'],
            'Color' => [
                'Rojo',
                'Azul',
                'Verde',
                'Amarillo',
                'Negro',
                'Blanco',
                'Gris',
                'Naranja',
                'Morado',
                'Rosa',
                'Café',
                'Beige',
                'Dorado',
                'Plateado',
                'Bronce',
                'Turquesa',
                'Magenta',
            ],
            'Material' => [
                'Algodón',
                'Poliéster',
                'Lana',
                'Seda',
                'Cuero',
                'Mezclilla',
                'Lino',
                'Nylon',
                'Terciopelo',
                'Sintético',
                'Plástico',
                'Madera',
                'Metal',
                'Vidrio',
                'Cerámica',
                'Cartón',
                'Papel',
            ],
            'Tipo' => ['Blanco', 'Integral', 'Premium'],
            'Presentacion' => ['Botella', 'Garrafa'],
            'Volumen' => ['355ml', '500ml', '1L', '2L', '3L'],
            'Contenido' => ['20 unidades', '26 unidades', '32 unidades'],
            'Estilo' => ['Moderno', 'Clásico', 'Vintage', 'Industrial', 'Rústico', 'Minimalista'],
            'Género' => ['Hombre', 'Mujer', 'Unisex', 'Niño', 'Niña', 'Bebé'],
            'Temporada' => ['Primavera', 'Verano', 'Otoño', 'Invierno', 'Todo el año'],
            'Marca Compatible' => ['Samsung', 'Apple', 'Huawei', 'Xiaomi', 'Sony', 'LG', 'Motorola'],
            'Capacidad' => ['32GB', '64GB', '128GB', '256GB', '512GB', '1TB', '2TB'],
            'Memoria RAM' => ['4GB', '8GB', '16GB', '32GB', '64GB'],
            'Conectividad' => ['WiFi', 'Bluetooth', 'NFC', 'USB-C', 'Lightning', 'HDMI'],
            'Voltaje' => ['110V', '220V', '12V', '24V', '5V'],
            'Potencia' => ['10W', '20W', '50W', '100W', '500W', '1000W'],
            'Peso' => ['1kg', '5kg', '10kg', '25kg', '50kg', '100g', '500g'],
            'Dimensiones' => ['Pequeño', 'Mediano', 'Grande', 'Extra Grande'],
        ];

        foreach ($attributesData as $name => $values) {
            $attribute = ProductAttribute::firstOrCreate(
                ['name' => $name],
                ['slug' => Str::slug($name)]
            );

            foreach ($values as $value) {
                ProductAttributeValue::firstOrCreate([
                    'product_attribute_id' => $attribute->id,
                    'value' => $value,
                ]);
            }
        }
    }
}
