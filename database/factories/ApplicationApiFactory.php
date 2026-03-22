<?php

namespace Database\Factories;

use App\Models\ApplicationApi;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicationApiFactory extends Factory
{
    protected $model = ApplicationApi::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $secret = fake()->bothify(str_repeat('?', 48));

        return [
            'id' => fake()->bothify(str_repeat('?', 12)),
            'memo' => $this->faker->word(),
            'token_hash' => hash('sha256', $secret),
            'token_hint' => substr($secret, -4),
            'abilities' => ApplicationApi::availableAbilities(),
            'owner_user_id' => null,
            'last_used' => null,
            'expires_at' => null,
            'revoked_at' => null,
        ];
    }
}
