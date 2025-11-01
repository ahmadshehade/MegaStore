<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\ProductManagment\Models\Category;

/**
 * Summary of CategoryFactory
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Category::class;
    public function definition(): array
    {
        return [
            'name'=>$this->faker->name,
            'description'=> $this->faker->sentence,
            
        ];
    }
}
