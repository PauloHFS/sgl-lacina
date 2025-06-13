<?php

namespace Database\Factories;

use App\Models\Sala;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sala>
 */
class SalaFactory extends Factory
{
    protected $model = Sala::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'nome' => $this->faker->word(),
            'descricao' => $this->faker->sentence(),
            'ativa' => $this->faker->boolean(80), // 80% de chance de ser ativa
        ];
    }

    public function comBaias(int $quantidade = 5): static
    {
        return $this->hasAttached(
            \App\Models\Baia::factory()->count($quantidade),
            [],
            'baias'
        );
    }

    public function inativa(): static
    {
        return $this->state(['ativa' => false]);
    }
}
