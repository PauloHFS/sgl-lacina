<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParticipacaoProjeto extends Model
{
    /** @use HasFactory<\Database\Factories\ParticipacaoProjetoFactory> */
    use HasFactory;

    protected $table = 'participacao_projeto';

    protected $fillable = [
        'id',
        'colaborador_id',
        'projeto_id',
        'data_inicio',
        'data_fim',
        'carga_horaria',
        'status',
    ];

    protected $casts = [
        'data_inicio' => 'datetime',
        'data_fim' => 'datetime',
        'carga_horaria' => 'integer',
    ];

    public function colaborador()
    {
        return $this->belongsTo(Colaborador::class, 'colaborador_id');
    }

    public function projeto()
    {
        return $this->belongsTo(Projeto::class, 'projeto_id');
    }
}
