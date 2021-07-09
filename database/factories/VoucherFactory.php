<?php

namespace Database\Factories;

use App\Models\voucher;
use Illuminate\Database\Eloquent\Factories\Factory;

class VoucherFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = voucher::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'credits'    => $this->faker->numberBetween(100, 1000),
            'expires_at' => $this->faker->dateTimeBetween(now(), '+30 days')
        ];
    }
}
