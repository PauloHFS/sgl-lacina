<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\UsuarioVinculo;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UsuarioVinculo>
 */
class UsuarioVinculoFactory extends Factory
{
    protected $model = UsuarioVinculo::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // TODO Editar para pegar do enum esses elementos
            'tipo_vinculo' => $this->faker->randomElement(['COORDENADOR', 'COLABORADOR']),
            'funcao' => $this->faker->randomElement(['COORDENADOR', 'PESQUISADOR', 'DESENVOLVEDOR', 'TECNICO', 'ALUNO']),
            'data_inicio' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'data_fim' => $this->faker->dateTimeBetween('now', '+1 year'),
        ];
    }

    public function withUsuarioAndProjeto($usuarioId, $projetoId): static
    {
        return $this->state(function (array $attributes) use ($usuarioId, $projetoId) {
            return [
                'usuario_id' => $usuarioId,
                'projeto_id' => $projetoId,
            ];
        });
    }
}
