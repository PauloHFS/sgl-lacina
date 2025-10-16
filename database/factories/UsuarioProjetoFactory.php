<?php

namespace Database\Factories;

use App\Enums\Funcao;
use App\Enums\TipoVinculo;
use App\Models\UsuarioProjeto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UsuarioProjeto>
 */
class UsuarioProjetoFactory extends Factory
{
    protected $model = UsuarioProjeto::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tipo_vinculo' => $this->faker->randomElement([TipoVinculo::COORDENADOR, TipoVinculo::COLABORADOR]),
            'funcao' => $this->faker->randomElement([Funcao::COORDENADOR, Funcao::PESQUISADOR, Funcao::DESENVOLVEDOR, Funcao::TECNICO, Funcao::ALUNO]),
            'carga_horaria' => $this->faker->numberBetween(10, 40),
            'data_inicio' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'data_fim' => null,
            'trocar' => false,
        ];
    }
}
