<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\OrderManagement\Models\Shipping;
use Modules\ProductManagment\Models\Category;
use Modules\ProductManagment\Models\Product;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesTableSeeder::class,
            AdminTableSeeder::class,
            daysTableSeeder::class,
        ]);
        User::factory(10)->create();
        Category::factory(10)->create();
        Shipping::factory(10)->create();
        Product::factory(10)->create();
    }
}
