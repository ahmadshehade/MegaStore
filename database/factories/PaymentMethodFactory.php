<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\PaymentManagement\Models\PaymentMethod;

class PaymentMethodFactory extends Factory
{
    protected $model = PaymentMethod::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement([
                'credit card',
                'paypal',
                'bank transfer',
                'cash',
                'stripe',
            ]),

            'description' => $this->faker->sentence(),

            'type' => $this->faker->randomElement([
                'card',
                'online',
                'manual',
            ]),

            'fee' => $this->faker->randomFloat(2, 0, 15), // بين 0 و15

            'security_features' => [
                'encryption' => $this->faker->boolean(),
                'two_factor' => $this->faker->boolean(),
                'tokenization' => $this->faker->boolean(),
            ],

            'integration_details' => [
                'api_key' => $this->faker->uuid(),
                'endpoint' => $this->faker->url(),
            ],

            'is_active' => $this->faker->boolean(85), // 85% يكون فعال
        ];
    }
}
