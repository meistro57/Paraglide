<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'display_name' => fake()->name(),
            'encryption_key_wrapped' => random_bytes(64),
            'recovery_code_hash' => fake()->sha256(),
            'hardware_tier' => fake()->randomElement(['tier_1', 'tier_2', 'tier_3']),
            'preferred_backend' => fake()->randomElement(['ollama', 'openrouter']),
            'preferred_model' => fake()->word(),
            'totp_secret_encrypted' => null,
        ];
    }

}
