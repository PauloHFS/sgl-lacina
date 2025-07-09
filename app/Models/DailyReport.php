<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class DailyReport extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'data',
        'horas_trabalhadas',
        'o_que_fez_ontem',
        'o_que_vai_fazer_hoje',
        'observacoes',
        'usuario_id',
        'projeto_id',
    ];

    protected $casts = [
        'data' => 'date',
        'horas_trabalhadas' => 'integer',
    ];

    public function uniqueIds(): array
    {
        return ['id'];
    }

    /**
     * Get the value of the model's primary key.
     * Ensures the UUID is returned as a string to avoid issues with NotificationFake.
     */
    public function getKey()
    {
        return (string) $this->getAttribute($this->getKeyName());
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function projeto(): BelongsTo
    {
        return $this->belongsTo(Projeto::class);
    }

    /**
     * Calcula automaticamente as horas baseado no horário cadastrado para o dia da semana
     * Cada registro na tabela 'horarios' representa 1 hora de trabalho
     */
    public function calcularHorasTrabalhadasAutomaticamente(): int
    {
        $diaDaSemana = $this->data->format('l'); // Monday, Tuesday, etc.

        // Mapear dias da semana em inglês para os enum values
        $mapeamentoDias = [
            'Monday' => 'SEGUNDA',
            'Tuesday' => 'TERCA',
            'Wednesday' => 'QUARTA',
            'Thursday' => 'QUINTA',
            'Friday' => 'SEXTA',
            'Saturday' => 'SABADO',
            'Sunday' => 'DOMINGO',
        ];

        $diaDaSemanaEnum = $mapeamentoDias[$diaDaSemana] ?? null;

        if (!$diaDaSemanaEnum) {
            Log::warning("Dia da semana não mapeado: {$diaDaSemana}");
            return 0;
        }

        if (!$this->usuario) {
            Log::warning("Usuário não encontrado para o daily report");
            return 0;
        }

        // Buscar horários de trabalho do usuário para esse dia
        $horariosTrabalho = $this->usuario->horarios()
            ->where('dia_da_semana', $diaDaSemanaEnum)
            ->whereIn('tipo', ['TRABALHO_PRESENCIAL', 'TRABALHO_REMOTO'])
            ->count();

        Log::info("Calculando horas para usuário {$this->usuario_id}, dia {$diaDaSemanaEnum}: {$horariosTrabalho} horas");

        return $horariosTrabalho;
    }

    /**
     * Scope para filtrar por data
     */
    public function scopePorData($query, $data)
    {
        return $query->where('data', $data);
    }

    /**
     * Scope para filtrar por usuário
     */
    public function scopePorUsuario($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    /**
     * Scope para filtrar por projeto
     */
    public function scopePorProjeto($query, $projetoId)
    {
        return $query->where('projeto_id', $projetoId);
    }

    /**
     * Scope para filtrar por período
     */
    public function scopePorPeriodo($query, $dataInicio, $dataFim)
    {
        return $query->whereBetween('data', [$dataInicio, $dataFim]);
    }
}
