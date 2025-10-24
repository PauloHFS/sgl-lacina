<?php

namespace App\Rules;

use App\Enums\DiaDaSemana;
use App\Enums\TipoHorario;
use App\Models\Horario;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class TotalHorasAusencia implements DataAwareRule, ValidationRule
{
    /**
     * All of the data under validation.
     *
     * @var array<string, mixed>
     */
    protected $data = [];

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $dataInicio = Carbon::parse($this->data['data_inicio']);
        $dataFim = Carbon::parse($this->data['data_fim']);
        $usuarioId = $this->data['usuario_id'];
        $horasACompensar = (int) $value;

        $totalHorasTrabalho = $this->calcularHorasDeTrabalhoNoPeriodo($usuarioId, $dataInicio, $dataFim);

        if ($totalHorasTrabalho !== $horasACompensar) {
            $fail("O total de horas a compensar ({$horasACompensar}h) não é igual ao total de horas de trabalho no período selecionado ({$totalHorasTrabalho}h).");
        }
    }

    /**
     * Set the data under validation.
     *
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    private function calcularHorasDeTrabalhoNoPeriodo(string $usuarioId, Carbon $dataInicio, Carbon $dataFim): int
    {
        $usuario = User::find($usuarioId);
        if (! $usuario) {
            return 0;
        }

        // Eager load all schedules for the user to avoid querying in a loop
        $horariosDoUsuario = $usuario->horarios()
            ->whereIn('tipo', [TipoHorario::TRABALHO_PRESENCIAL, TipoHorario::TRABALHO_REMOTO])
            ->get()
            ->groupBy(fn ($horario) => $horario->dia_da_semana->value);

        $totalHoras = 0;
        $periodo = CarbonPeriod::create($dataInicio, $dataFim);

        foreach ($periodo as $data) {
            $diaDaSemana = DiaDaSemana::fromCarbon($data);
            $totalHoras += $horariosDoUsuario->get($diaDaSemana->value, collect())->count();
        }

        return $totalHoras;
    }
}