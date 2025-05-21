<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HistoricoUsuarioProjeto extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'historico_usuario_projeto';

    protected $fillable = [
        'usuario_id',
        'projeto_id',
        'projeto_antigo_id',
        'tipo_vinculo',
        'funcao',
        'status',
        'carga_horaria_semanal',
        'data_inicio',
        'data_fim',
    ];

    /**
     * Os atributos que devem ser convertidos para tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'data_inicio' => 'datetime',
        'data_fim' => 'datetime',
        'tipo_vinculo' => \App\Enums\TipoVinculo::class,
        'funcao' => \App\Enums\Funcao::class,
        'status' => \App\Enums\StatusVinculoProjeto::class,
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function projeto()
    {
        return $this->belongsTo(Projeto::class, 'projeto_id');
    }

    public function projetoAntigo()
    {
        return $this->belongsTo(Projeto::class, 'projeto_antigo_id');
    }
}
