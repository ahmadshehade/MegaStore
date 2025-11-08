<?php

namespace Database\Factories;

use App\Models\Day;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\OrderManagement\Models\Shipping;

/**
 * Summary of ShippingFactory
 */
class ShippingFactory extends Factory
{
    protected $model=Shipping::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'=> $this->faker->name,
            'day_id'=>Day::inRandomOrder()->value('id') ?? Day::factory()->create()->id,
            'cost'     => $this->faker->randomFloat(2, 0, 250),
            'is_active'=>$this->faker->boolean(),
        ];
    }
}
