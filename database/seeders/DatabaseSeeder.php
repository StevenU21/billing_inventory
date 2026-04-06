<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use Deifhelt\LaravelPermissionsManager\Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
        ]);

        $adminUser = User::factory()->create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        Profile::factory()->create([
            'user_id' => $adminUser->id,
        ]);
        $adminUser->assignRole('admin');

        $cashierUser = User::factory()->create([
            'first_name' => 'Cashier',
            'last_name' => 'User',
            'email' => 'cashier@example.com',
            'password' => bcrypt('password'),
        ]);
        Profile::factory()->create([
            'user_id' => $cashierUser->id,
        ]);
        $cashierUser->assignRole('cashier');

        // Catalogs
        $this->call([
            DepartmentSeeder::class,
            TaxSeeder::class,
            UnitMeasureSeeder::class,
            PaymentMethodSeeder::class,

            CategorySeeder::class,
            BrandSeeder::class,
            // CompanySeeder::class,
            EntitySeeder::class,
        ]);

        // Core Business Data
        $this->call([
            // AttributeSeeder::class,
            // ProductSeeder::class,
            // PurchaseSeeder::class,
            // SaleSeeder::class,
        ]);
    }
}
