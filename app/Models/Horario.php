<?php

namespace App\Models;

use App\Enums\DiaDaSemana;
use App\Enums\TipoHorario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Horario extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'horario',
        'dia_da_semana',
        'tipo',
        'usuario_id',
        'usuario_projeto_id',
        'baia_id',
    ];

    protected $casts = [
        'dia_da_semana' => DiaDaSemana::class,
        'tipo' => TipoHorario::class,
        'horario' => 'integer',
    ];

    // Relacionamentos
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function usuarioProjeto(): BelongsTo
    {
        return $this->belongsTo(UsuarioProjeto::class);
    }

    public function baia(): BelongsTo
    {
        return $this->belongsTo(Baia::class);
    }

    // Scopes úteis
    public function scopePorDia($query, DiaDaSemana $dia)
    {
        return $query->where('dia_da_semana', $dia);
    }

    public function scopePorHorario($query, int $hora)
    {
        return $query->where('horario', $hora);
    }

    public function scopeComTrabalho($query)
    {
        return $query->whereIn('tipo', [TipoHorario::TRABALHO_REMOTO, TipoHorario::TRABALHO_PRESENCIAL]);
    }

    // Helper para formatar hora
    public function getHoraFormatadaAttribute(): string
    {
        return sprintf('%02d:00', $this->horario);
    }
}
