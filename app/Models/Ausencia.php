<?php

namespace App\Models;

use App\Enums\StatusAusencia;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ausencia extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'usuario_id',
        'projeto_id',

        'titulo',
        'data_inicio',
        'data_fim',
        'justificativa',

        'horas_a_compensar',
        'compensacao_data_inicio',
        'compensacao_data_fim',
        'compensacao_horarios',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',

        'compensacao_data_inicio' => 'date',
        'compensacao_data_fim' => 'date',
        'compensacao_horarios' => 'array',

        'horas_a_compensar' => 'integer',
    ];

    public function uniqueIds(): array
    {
        return ['id'];
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::updating(function (Ausencia $ausencia) {
            if ($ausencia->getOriginal('status') === StatusAusencia::REJEITADO->value) {
                $ausencia->status = StatusAusencia::PENDENTE->value;
            }
        });
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
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function projeto(): BelongsTo
    {
        return $this->belongsTo(Projeto::class, 'projeto_id');
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
