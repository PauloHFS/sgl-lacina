<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Baias>
 */
class BaiasFactory extends Factory
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
        ];
    }

    public function withSala(int $salaId): static
    {
        return $this->state([
            'sala_id' => $salaId,
        ]);
    }
}
