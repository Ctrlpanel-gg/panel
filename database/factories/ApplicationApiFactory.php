<?php

namespace Database\Factories;

use App\Models\ApplicationApi;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicationApiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'memo' => $this->faker->word(),
        ];
    }
}
