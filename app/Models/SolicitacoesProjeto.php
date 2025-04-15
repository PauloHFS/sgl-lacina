<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Enums\StatusParticipacaoProjeto;
use Illuminate\Database\Eloquent\Model;

class SolicitacoesProjeto extends Model
{

  protected $table = 'solicitacoes_projeto';

  protected $fillable = [
    'id',
    'usuario_id',
    'projeto_id',
    'data_inicio',
    'data_fim',
    'carga_horaria',
    'status',
    'created_at',
    'updated_at',
  ];

  protected $casts = [
    'status' => StatusParticipacaoProjeto::class,
    'data_inicio' => 'datetime',
    'data_fim' => 'datetime',
    'carga_horaria' => 'integer',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
  ];

  public function usuario()
  {
    return $this->belongsTo(User::class, 'usuario_id');
  }

  public function projeto()
  {
    return $this->belongsTo(Projeto::class, 'projeto_id');
  }
}
