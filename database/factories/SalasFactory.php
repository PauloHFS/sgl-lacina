<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Salas>
 */
class SalasFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => $this->faker->word(),
            'senha_porta' => $this->faker->numberBetween(1000, 9999),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
