<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Docente>
 */
class DocenteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->unique()->numberBetween(1, 1000),
            'created_at' => now(),
        ];
    }

    /**
     * Configure o factory para usar um usuário específico existente
     */
    public function forUser(User $user)
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'id' => $user->id,
            ];
        });
    }

    /**
     * Configure o factory para criar automaticamente um novo usuário
     */
    public function withUser(array $userAttributes = [])
    {
        return $this->state(function (array $attributes) use ($userAttributes) {
            $user = User::factory()->create($userAttributes);

            return [
                'id' => $user->id,
            ];
        });
    }
}
