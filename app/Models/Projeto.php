<?php

namespace App\Models;

use App\Enums\TipoProjeto;
use App\Enums\TipoVinculo;
use App\Enums\StatusVinculoProjeto;
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
        'numero_convenio',
        'valor_total',
        'meses_execucao',
        'campos_extras',
        'data_inicio',
        'data_termino',
        'cliente',
        'slack_url',
        'discord_url',
        'board_url',
        'git_url',
        'tipo',
        'interveniente_financeiro_id'
    ];

    protected $casts = [
        'campos_extras' => 'array', // Cast JSONB para array
        'data_inicio' => 'date',
        'data_termino' => 'date',
        'tipo' => TipoProjeto::class,
    ];

    public function uniqueIds(): array
    {
        return ['id'];
    }

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'usuario_projeto', 'projeto_id', 'usuario_id')
            ->withPivot('id', 'tipo_vinculo', 'funcao', 'status', 'carga_horaria', 'valor_bolsa', 'data_inicio', 'data_fim')
            ->withTimestamps();
    }

    public function coordenadores(): BelongsToMany
    {
        return $this->usuarios()->wherePivot('tipo_vinculo', TipoVinculo::COORDENADOR)->wherePivot('status', StatusVinculoProjeto::APROVADO);
    }

    public function getUsuarioVinculo(string $usuarioId)
    {
        $vinculo = $this->usuarios()
            ->where('usuario_id', $usuarioId)
            ->first();

        if (!$vinculo) {
            return null;
        }

        return $vinculo->pivot;
    }

    public function intervenienteFinanceiro()
    {
        return $this->belongsTo(IntervenienteFinanceiro::class, 'interveniente_financeiro_id', 'id');
    }

    public function historicoUsuarioProjeto()
    {
        return $this->hasMany(HistoricoUsuarioProjeto::class, 'projeto_id', 'id');
    }
}
