<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Horarios>
 */
class HorariosFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dia_semana' => $this->faker->randomElement(['SEGUNDA', 'TERCA', 'QUARTA', 'QUINTA', 'SEXTA', 'SABADO', 'DOMINGO']),
            'hora_inicio' => $this->faker->time(),
            'hora_fim' => $this->faker->time(),
            'tipo' => $this->faker->randomElement(['AULA', 'TRABALHO', 'AUSENTE']),
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
}
