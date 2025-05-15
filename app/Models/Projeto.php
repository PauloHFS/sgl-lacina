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
        return $this->belongsToMany(User::class, 'usuario_projeto', 'projeto_id', 'usuario_id')
            ->withPivot('tipo_vinculo', 'funcao', 'status', 'carga_horaria_semanal', 'data_inicio', 'data_fim')
            ->withTimestamps();
    }

    public function getUsuarioVinculo(string $usuarioId)
    {
        $vinculo = $this->usuarios()
            ->where('usuario_id', $usuarioId)
            ->first();

        if (!$vinculo) {
            return null;
        }

        return [
            'usuario_id' => $vinculo->pivot->usuario_id,
            'tipo_vinculo' => $vinculo->pivot->tipo_vinculo,
            'funcao' => $vinculo->pivot->funcao,
            'status' => $vinculo->pivot->status,
            'carga_horaria_semanal' => $vinculo->pivot->carga_horaria_semanal,
            'data_inicio' => $vinculo->pivot->data_inicio,
            'data_fim' => $vinculo->pivot->data_fim,
        ];
    }
}
