<?php

namespace Database\Factories;

use App\Models\ApplicationApi;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicationApiFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ApplicationApi::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'memo' => $this->faker->word()
        ];
    }
}
