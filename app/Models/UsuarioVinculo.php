<?php

namespace App\Models;

use App\Enums\TipoVinculo;
use App\Enums\Funcao;
use App\Enums\StatusVinculoProjeto;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class UsuarioProjeto extends Model
{
    /** @use HasFactory<\Database\Factories\UsuarioProjetoFactory> */
    use HasFactory, HasUuids;

    protected $table = 'usuario_vinculo';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';


    protected $fillable = [
        'id',
        'projeto_id',
        'usuario_id',
        'tipo_vinculo',
        'funcao',
        'status',
        'carga_horaria_semanal',
        'data_inicio',
        'data_fim',
    ];

    protected $casts = [
        'tipo_vinculo' => TipoVinculo::class,
        'funcao' => Funcao::class,
        'status' => StatusVinculoProjeto::class,
        'carga_horaria_semanal' => 'integer',
        'data_inicio' => 'datetime',
        'data_fim' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function projeto(): BelongsTo
    {
        return $this->belongsTo(Projeto::class, 'projeto_id');
    }
}
