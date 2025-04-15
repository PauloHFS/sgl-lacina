<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioVinculo extends Model
{
    /** @use HasFactory<\Database\Factories\UsuarioVinculoFactory> */
    use HasFactory;

    protected $table = 'usuario_vinculo';

    protected $primaryKey = ['projeto_id', 'usuario_id', 'data_fim'];

    protected $fillable = [
        'projeto_id',
        'usuario_id',
        'tipo_vinculo',
        'funcao',
        'data_inicio',
        'data_fim',
    ];

    protected $casts = [
        'data_inicio' => 'datetime',
        'data_fim' => 'datetime',
    ];

    public function projeto()
    {
        return $this->belongsTo(Projeto::class, 'projeto_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
