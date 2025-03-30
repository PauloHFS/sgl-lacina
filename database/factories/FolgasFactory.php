<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Folgas>
 */
class FolgasFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tipo_folga' => $this->faker->randomElement(['COLETIVA', 'INDIVIDUAL']),
            'data_inicio' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'data_fim' => $this->faker->dateTimeBetween('now', '+1 year'),
            'status' => $this->faker->randomElement(['PENDENTE', 'APROVADO', 'REJEITADO']),
            'justificativa' => $this->faker->sentence(),
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
    public function withTipoFolga($tipoFolga): static
    {
        return $this->state(function (array $attributes) use ($tipoFolga) {
            return [
                'tipo_folga' => $tipoFolga,
            ];
        });
    }
}
