<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Projeto extends Model
{
    use HasFactory;

    protected $table = 'projetos';

    protected $fillable = [
        'id',
        'nome',
        'descricao',
        'data_inicio',
        'data_termino',
        'cliente',
        'slack_url',
        'discord_url',
        'board_url',
        'git_url',
        'tipo'
    ];

    protected $casts = [
        'data_inicio' => 'datetime',
        'data_termino' => 'datetime',
    ];

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'usuario_vinculo', 'projeto_id', 'usuario_id')
            ->withPivot('tipo_vinculo', 'funcao', 'data_inicio', 'data_fim')
            ->withTimestamps();
    }
}
