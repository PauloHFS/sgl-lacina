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
        'data_fim',
        'status',
        'link_github',
        'link_figma',
        'link_trello',
        'link_drive',
        'link_site',
        'link_video',
        'link_documentacao'
    ];
}
