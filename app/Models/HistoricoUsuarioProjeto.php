<?php

namespace App\Models;

use App\Enums\Funcao;
use App\Enums\StatusVinculoProjeto;
use App\Enums\TipoVinculo;
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
        'trocar',
        'tipo_vinculo',
        'funcao',
        'status',
        'carga_horaria',
        'valor_bolsa',
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
        'tipo_vinculo' => TipoVinculo::class,
        'funcao' => Funcao::class,
        'status' => StatusVinculoProjeto::class,
        'carga_horaria' => 'integer',
        'valor_bolsa' => 'integer',
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
