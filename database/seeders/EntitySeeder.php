<?php

namespace Database\Seeders;

use App\Models\Entity;
use Illuminate\Database\Seeder;

class EntitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $entities = [
            [
                'first_name' => 'Proveedor',
                'last_name' => 'Uno',
                'identity_card' => '001-010101-0001A',
                'ruc' => '0010101010001A',
                'email' => 'proveedor1@ejemplo.com',
                'phone' => '+505 8000-0001',
                'address' => 'Dirección Proveedor 1',
                'description' => 'Proveedor genérico 1',
                'is_client' => false,
                'is_supplier' => true,
                'is_active' => true,
                'municipality_id' => 1,
            ],
            [
                'first_name' => 'Cliente',
                'last_name' => 'Uno',
                'identity_card' => '001-020202-0002B',
                'ruc' => '0010202020002B',
                'email' => 'cliente1@ejemplo.com',
                'phone' => '+505 8000-0002',
                'address' => 'Dirección Cliente 1',
                'description' => 'Cliente genérico 1',
                'is_client' => true,
                'is_supplier' => false,
                'is_active' => true,
                'municipality_id' => 1,
            ],
            [
                'first_name' => 'Mixto',
                'last_name' => 'Uno',
                'identity_card' => '001-030303-0003C',
                'ruc' => '0010303030003C',
                'email' => 'mixto1@ejemplo.com',
                'phone' => '+505 8000-0003',
                'address' => 'Dirección Mixto 1',
                'description' => 'Cliente y proveedor genérico 1',
                'is_client' => true,
                'is_supplier' => true,
                'is_active' => true,
                'municipality_id' => 1,
            ],
            [
                'first_name' => 'Proveedor',
                'last_name' => 'Dos',
                'identity_card' => '001-040404-0004D',
                'ruc' => '0010404040004D',
                'email' => 'proveedor2@ejemplo.com',
                'phone' => '+505 8000-0004',
                'address' => 'Dirección Proveedor 2',
                'description' => 'Proveedor genérico 2',
                'is_client' => false,
                'is_supplier' => true,
                'is_active' => true,
                'municipality_id' => 1,
            ],
            [
                'first_name' => 'Proveedor',
                'last_name' => 'Tres',
                'identity_card' => '001-050505-0005E',
                'ruc' => '0010505050005E',
                'email' => 'proveedor3@ejemplo.com',
                'phone' => '+505 8000-0005',
                'address' => 'Dirección Proveedor 3',
                'description' => 'Proveedor genérico 3',
                'is_client' => false,
                'is_supplier' => true,
                'is_active' => true,
                'municipality_id' => 1,
            ],
            [
                'first_name' => 'Mixto',
                'last_name' => 'Dos',
                'identity_card' => '001-060606-0006F',
                'ruc' => '0010606060006F',
                'email' => 'mixto2@ejemplo.com',
                'phone' => '+505 8000-0006',
                'address' => 'Dirección Mixto 2',
                'description' => 'Cliente y proveedor genérico 2',
                'is_client' => true,
                'is_supplier' => true,
                'is_active' => true,
                'municipality_id' => 1,
            ],
            [
                'first_name' => 'Cliente',
                'last_name' => 'de Contado',
                'identity_card' => '',
                'ruc' => '',
                'email' => '',
                'phone' => '',
                'address' => '',
                'description' => '',
                'is_client' => true,
                'is_supplier' => false,
                'is_active' => true,
                'municipality_id' => 1,
            ],
        ];

        foreach ($entities as $entity) {
            Entity::firstOrCreate($entity);
        }
    }
}
