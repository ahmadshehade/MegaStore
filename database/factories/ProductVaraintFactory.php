<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\ProductManagement\Models\Product;
use Modules\ProductManagement\Models\ProductVariant;

class ProductVaraintFactory extends Factory
{
    protected $model = ProductVariant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = $this->faker->randomFloat(2, 10, 500); // سعر المنتج بين 10 و 500
        $comparePrice = $this->faker->boolean(50)
            ? $this->faker->randomFloat(2, $price, 600) // أحيانًا أعلى من السعر الأساسي
            : null;

        return [
            'product_id'          => Product::factory(),
            'sku'                 => strtoupper($this->faker->unique()->bothify('SKU-####??')),
            'price'               => $price,
            'compare_price'       => $comparePrice,
            'stock_quantity'      => $this->faker->numberBetween(0, 100),
            'low_stock_threshold' => $this->faker->numberBetween(1, 10),
            'weight'              => $this->faker->randomFloat(2, 0.1, 10.00), // بالكيلوغرام
            'is_active'           => $this->faker->boolean(90), // غالبًا فعال
        ];
    }
}
