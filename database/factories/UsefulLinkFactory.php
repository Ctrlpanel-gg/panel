<?php

namespace Database\Factories;

use App\Models\UsefulLink;
use Illuminate\Database\Eloquent\Factories\Factory;

class UsefulLinkFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UsefulLink::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'icon' => 'fas fa-user',
            'title' => $this->faker->text(30),
            'link' => $this->faker->url,
            'description' => $this->faker->text,
        ];
    }
}
