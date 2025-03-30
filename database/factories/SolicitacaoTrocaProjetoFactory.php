<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SolicitacaoTrocaProjeto>
 */
class SolicitacaoTrocaProjetoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'motivo' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(['PENDENTE', 'APROVADO', 'REJEITADO']),
            'resposta' => $this->faker->sentence(),
            'data_resposta' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function withParticacaoProjeto($participacaoProjetoId): static
    {
        return $this->state(function (array $attributes) use ($participacaoProjetoId) {
            return [
                'participacao_projeto_id' => $participacaoProjetoId,
            ];
        });
    }

    public function withProjetoNovo($projetoNovoId): static
    {
        return $this->state(function (array $attributes) use ($projetoNovoId) {
            return [
                'projeto_novo_id' => $projetoNovoId,
            ];
        });
    }
}
