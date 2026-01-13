<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\OrderManagement\Models\Discount;
use Modules\PaymentManagement\Models\PaymentMethod;
use Modules\ProductManagement\Models\Category;
use Modules\ProductManagement\Models\Product;
use Modules\ProductManagement\Models\ProductVariant;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            RolesTableSeeder::class,
            AdminTableSeeder::class,
        ]);

        Category::factory(10)->create();
        Product::factory(10)->create();
        ProductVariant::factory(10)->create();
        Discount::factory(10)->create();
        PaymentMethod::factory()->count(5)->sequence(
            ['name' => 'credit card'],
            ['name' => 'paypal'],
            ['name' => 'bank transfer'],
            ['name' => 'cash'],
            ['name' => 'stripe'],
        )->create();
    }
}
