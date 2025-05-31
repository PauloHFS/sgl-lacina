<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\HistoricoUsuarioProjeto;
use App\Enums\TipoVinculo;
use App\Enums\Funcao;
use App\Enums\StatusVinculoProjeto;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HistoricoUsuarioProjeto>
 */
class HistoricoUsuarioProjetoFactory extends Factory
{
    protected $model = HistoricoUsuarioProjeto::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $dataInicio = $this->faker->dateTimeBetween('-2 years', '-1 month');
        $dataFim = $this->faker->dateTimeBetween($dataInicio, 'now');

        return [
            'tipo_vinculo' => $this->faker->randomElement([TipoVinculo::COORDENADOR, TipoVinculo::COLABORADOR]),
            'funcao' => $this->faker->randomElement([Funcao::COORDENADOR, Funcao::PESQUISADOR, Funcao::DESENVOLVEDOR, Funcao::TECNICO, Funcao::ALUNO]),
            'status' => $this->faker->randomElement([StatusVinculoProjeto::APROVADO, StatusVinculoProjeto::RECUSADO, StatusVinculoProjeto::ENCERRADO]),
            'carga_horaria_semanal' => $this->faker->numberBetween(10, 40),
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim,
            'trocar' => $this->faker->boolean(20), // 20% chance of being true
        ];
    }

    /**
     * State for active projects (no end date).
     */
    public function ativo()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => StatusVinculoProjeto::APROVADO,
                'data_fim' => null,
            ];
        });
    }

    /**
     * State for completed projects.
     */
    public function concluido()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => StatusVinculoProjeto::ENCERRADO,
                'data_fim' => $this->faker->dateTimeBetween('-1 year', 'now'),
            ];
        });
    }

    /**
     * State for coordinator role.
     */
    public function coordenador()
    {
        return $this->state(function (array $attributes) {
            return [
                'tipo_vinculo' => TipoVinculo::COORDENADOR,
                'funcao' => Funcao::COORDENADOR,
            ];
        });
    }

    /**
     * State for collaborator role.
     */
    public function colaborador()
    {
        return $this->state(function (array $attributes) {
            return [
                'tipo_vinculo' => TipoVinculo::COLABORADOR,
                'funcao' => $this->faker->randomElement([Funcao::PESQUISADOR, Funcao::DESENVOLVEDOR, Funcao::TECNICO, Funcao::ALUNO]),
            ];
        });
    }
}
