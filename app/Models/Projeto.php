<?php

namespace App\Models;

use App\Enums\TipoProjeto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Projeto extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'projetos';

    public $incrementing = false;

    public $keyType = 'string';

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
        'data_inicio' => 'date',
        'data_termino' => 'date',
        'tipo' => TipoProjeto::class
    ];

    public function uniqueIds(): array
    {
        return ['id'];
    }

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'usuario_vinculo', 'projeto_id', 'usuario_id')
            ->withPivot('tipo_vinculo', 'funcao', 'status', 'carga_horaria_semanal', 'data_inicio', 'data_fim')
            ->withTimestamps();
    }
}
