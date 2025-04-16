<?php

namespace App\Models;

use App\Enums\TipoVinculo;
use App\Enums\Funcao;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioVinculo extends Model
{
    /** @use HasFactory<\Database\Factories\UsuarioVinculoFactory> */
    use HasFactory;

    protected $table = 'usuario_vinculo';

    protected $primaryKey = ['projeto_id', 'usuario_id', 'data_fim'];
    public $incrementing = false;

    protected $fillable = [
        'projeto_id',
        'usuario_id',
        'tipo_vinculo',
        'funcao',
        'data_inicio',
        'data_fim',
    ];

    protected $casts = [
        'tipo_vinculo' => TipoVinculo::class,
        'funcao' => Funcao::class,
        'data_inicio' => 'datetime',
        'data_fim' => 'datetime',
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
