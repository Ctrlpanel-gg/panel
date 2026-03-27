<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'payment_id' => Str::random(30),
            'user_id' => User::factory(),
            'payment_method' => 'Stripe',
            'type' => 'Credits',
            'status' => \App\Enums\PaymentStatus::PAID->value,
            'amount' => $this->faker->numberBetween(10, 10000),
            'price' => $this->faker->numberBetween(100, 10000),
            'tax_value' => 0,
            'tax_percent' => 0,
            'total_price' => $this->faker->numberBetween(100, 10000),
            'currency_code' => ['EUR', 'USD'][rand(0, 1)],
            'shop_item_product_id' => null,
        ];
    }
}
