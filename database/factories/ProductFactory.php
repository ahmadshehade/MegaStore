<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\ProductManagment\Models\Category;
use Modules\ProductManagment\Models\Product;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Product::class;
    public function definition(): array
    {
        return [
            "name"=> $this->faker->name,
            "description"=>$t = $this->faker->text,
            "price"=> $this->faker->randomFloat(2,1,99999.99),
            'stock'=>$this->faker->randomNumber(),
            'category_id'=>Category::factory(),
            'seller_id'=>User::factory()
        ];
    }
}
