<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\OrderManagement\Models\Discount;

class DiscountFactory extends Factory
{
    protected $model = Discount::class;

    public function definition(): array
    {
   // اختيار نوع الخصم عشوائياً
        $type = $this->faker->randomElement(['percentage', 'fixed']);

        return [
            'code' => $this->faker->bothify('??########'),

            'type' => $type,

            'value' => $type === 'percentage'
                ? $this->faker->numberBetween(1, 90)
                : $this->faker->randomFloat(2, 1, 99999.99),

            'description' => $this->faker->text(100),

            'start_date' => $this->faker->dateTimeBetween('now', '+30 days'),

            'end_date' => $this->faker->dateTimeBetween('+31 days', '+60 days'),

            'status' => $this->faker->boolean(),

            'created_by' => \App\Models\User::factory(),
        ];
    }
}
