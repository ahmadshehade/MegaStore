<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\ProductManagement\Models\Category;
use Modules\ProductManagement\Models\Product;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->words(3, true);
        $slug = Str::slug($name) . '-' . Str::random(6);

        return [
            'name'              => $name,
            'slug'              => $slug,
            'sku'               => strtoupper(Str::random(8)), // SKU عشوائي وفريد
            'short_description' => $this->faker->sentence(6),
            'description'       => $this->faker->paragraph,
            'brand'             => $this->faker->company,
            'status'            => $this->faker->randomElement(['draft', 'active', 'archived']),
            'is_featured'       => $this->faker->boolean(20), // 20% من المنتجات مميزة
            'meta'              => [
                'color' => $this->faker->safeColorName,
                'size'  => $this->faker->randomElement(['S', 'M', 'L', 'XL']),
            ],
            'category_id'       => Category::factory(),
            'created_by'        => User::factory(),
        ];
    }
}
