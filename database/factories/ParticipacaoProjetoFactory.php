<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ParticipacaoProjeto>
 */
class ParticipacaoProjetoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'data_inicio' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'data_fim' => $this->faker->dateTimeBetween('now', '+1 year'),
            'status' => $this->faker->randomElement(['ATIVO', 'INATIVO']),
            'carga_horaria' => $this->faker->numberBetween(1, 40),
        ];
    }

    public function withColaborador($colaboradorId): static
    {
        return $this->state(function (array $attributes) use ($colaboradorId) {
            return [
                'colaborador_id' => $colaboradorId,
            ];
        });
    }
    public function withProjeto($projetoId): static
    {
        return $this->state(function (array $attributes) use ($projetoId) {
            return [
                'projeto_id' => $projetoId,
            ];
        });
    }
}
