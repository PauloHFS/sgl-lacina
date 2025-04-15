<?php

namespace App\Models;

use App\Enums\StatusSolicitacaoTrocaProjeto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitacaoTrocaProjeto extends Model
{
    /** @use HasFactory<\Database\Factories\SolicitacaoTrocaProjetoFactory> */
    use HasFactory;

    protected $table = 'solicitacao_troca_projeto';

    protected $primaryKey = ['usuario_id', 'projeto_atual_id', 'projeto_novo_id'];
    public $incrementing = false;

    protected $fillable = [
        'usuario_id',
        'projeto_atual_id',
        'projeto_novo_id',
        'motivo',
        'resposta',
        'status',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'status' => StatusSolicitacaoTrocaProjeto::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function projetoAtual()
    {
        return $this->belongsTo(Projeto::class, 'projeto_atual_id');
    }

    public function projetoNovo()
    {
        return $this->belongsTo(Projeto::class, 'projeto_novo_id');
    }
}
