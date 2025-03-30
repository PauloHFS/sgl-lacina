<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitacaoTrocaProjeto extends Model
{
    /** @use HasFactory<\Database\Factories\SolicitacaoTrocaProjetoFactory> */
    use HasFactory;

    protected $table = 'solicitacao_troca_projeto';

    protected $fillable = [
        'id',
        'colaborador_id',
        'projeto_atual_id',
        'projeto_novo_id',
        'motivo',
        'resposta',
        'status',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
