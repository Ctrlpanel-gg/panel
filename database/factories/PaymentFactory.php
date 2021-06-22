<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PaymentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'payment_id' => Str::random(30),
            'payer_id' => Str::random(30),
            'user_id' => User::factory(),
            'type' => "Credits",
            'status' => "Completed",
            'amount' => $this->faker->numberBetween(10, 10000),
            'price' => '€' . $this->faker->numerify('##.##'),
            'payer' => '{}',
        ];
    }
}
