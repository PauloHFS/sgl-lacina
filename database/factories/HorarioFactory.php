<?php

namespace Database\Factories;

use App\Enums\DiaDaSemana;
use App\Enums\TipoHorario;
use App\Models\Baia;
use App\Models\Horario;
use App\Models\User;
use App\Models\UsuarioProjeto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Horario>
 */
class HorarioFactory extends Factory
{
    protected $model = Horario::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'horario' => $this->faker->numberBetween(8, 18), // Horário comercial
            'dia_da_semana' => $this->faker->randomElement(DiaDaSemana::cases()),
            'tipo' => $this->faker->randomElement(TipoHorario::cases()),
            'usuario_id' => User::factory(),
            'usuario_projeto_id' => null,
            'baia_id' => null,
        ];
    }

    public function paraUsuario(User $user): static
    {
        return $this->state(['usuario_id' => $user->id]);
    }

    public function comProjeto(UsuarioProjeto $usuarioProjeto = null): static
    {
        return $this->state([
            'usuario_projeto_id' => $usuarioProjeto?->id ?? UsuarioProjeto::factory(),
            'tipo' => $this->faker->randomElement([
                TipoHorario::TRABALHO_PRESENCIAL,
                TipoHorario::TRABALHO_REMOTO
            ]),
        ]);
    }

    public function comBaia(Baia $baia = null): static
    {
        return $this->state([
            'baia_id' => $baia?->id ?? Baia::factory(),
            'tipo' => TipoHorario::TRABALHO_PRESENCIAL,
        ]);
    }

    public function ausente(): static
    {
        return $this->state([
            'tipo' => TipoHorario::AUSENTE,
            'usuario_projeto_id' => null,
            'baia_id' => null,
        ]);
    }

    public function emAula(): static
    {
        return $this->state([
            'tipo' => TipoHorario::EM_AULA,
            'usuario_projeto_id' => null,
            'baia_id' => null,
        ]);
    }

    public function trabalhoPresencial(): static
    {
        return $this->state([
            'tipo' => TipoHorario::TRABALHO_PRESENCIAL,
        ]);
    }

    public function trabalhoRemoto(): static
    {
        return $this->state([
            'tipo' => TipoHorario::TRABALHO_REMOTO,
            'baia_id' => null,
        ]);
    }

    public function noDia(DiaDaSemana $dia): static
    {
        return $this->state(['dia_da_semana' => $dia]);
    }

    public function noHorario(int $hora): static
    {
        return $this->state(['horario' => $hora]);
    }
}
