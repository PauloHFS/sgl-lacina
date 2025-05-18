<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\UsuarioProjeto;
use App\Enums\TipoVinculo;
use App\Enums\Funcao;

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
            'data_inicio' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
