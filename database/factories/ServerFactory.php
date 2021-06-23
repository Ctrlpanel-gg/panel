<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Server;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ServerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Server::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'description' => $this->faker->text(60),
            'identifier' => Str::random(30),
            'pterodactyl_id' => $this->faker->numberBetween(1000000,1000000000),
            'product_id' => Product::factory()
        ];
    }
}
