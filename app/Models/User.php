<?php

namespace App\Models;

use App\Enums\Genero; // Added Genero enum import
use App\Enums\StatusCadastro;
use App\Enums\StatusVinculoProjeto;
use App\Enums\TipoVinculo;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;


class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUuids;

    public $incrementing = false;

    public $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'status_cadastro',
        'campos_extras',
        'curriculo_lattes_url',
        'linkedin_url',
        'github_url',
        'website_url',
        'foto_url',
        'area_atuacao',
        'tecnologias',
        'genero',
        'data_nascimento',
        'cpf',
        'rg',
        'uf_rg',
        'orgao_emissor_rg',
        'telefone',
        'banco_id',
        'conta_bancaria',
        'agencia',
        'cep',
        'endereco',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'uf',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function uniqueIds()
    {
        return ['id'];
    }

    /**
     * Get the value of the model's primary key.
     * Ensures the UUID is returned as a string to avoid issues with NotificationFake.
     */
    public function getKey()
    {
        return (string) $this->getAttribute($this->getKeyName());
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status_cadastro' => StatusCadastro::class,
            'genero' => Genero::class,
            'data_nascimento' => 'date',
            'campos_extras' => 'array',
            'area_atuacao' => 'array',
            'tecnologias' => 'array',
        ];
    }

    public function vinculos(): HasMany
    {
        return $this->hasMany(UsuarioProjeto::class, 'usuario_id');
    }

    public function projetos(): BelongsToMany
    {
        return $this->belongsToMany(Projeto::class, 'usuario_projeto', 'usuario_id', 'projeto_id')
            ->as('vinculo')
            ->withPivot('id', 'tipo_vinculo', 'funcao', 'status', 'carga_horaria', 'valor_bolsa', 'data_inicio', 'data_fim')
            ->withTimestamps();
    }

    public function banco(): BelongsTo
    {
        return $this->belongsTo(Banco::class, 'banco_id');
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class, 'usuario_id');
    }

    public function historicoUsuarioProjeto(): HasMany
    {
        return $this->hasMany(HistoricoUsuarioProjeto::class, 'usuario_id');
    }

    public function isCoordenador(?Projeto $projeto = null)
    {
        if ($projeto === null) {
            return $this->vinculos()
                ->where('tipo_vinculo', TipoVinculo::COORDENADOR)
                ->where('status', StatusVinculoProjeto::APROVADO)
                ->exists();
        }
        return $this->projetos()
            ->where('projeto_id', $projeto->id)
            ->where('tipo_vinculo', TipoVinculo::COORDENADOR)
            ->where('status', StatusVinculoProjeto::APROVADO)
            ->exists();
    }

    public function isColaborador(?Projeto $projeto = null)
    {
        if ($projeto === null) {
            return $this->vinculos()
                ->where('tipo_vinculo', TipoVinculo::COLABORADOR)
                ->where('status', StatusVinculoProjeto::APROVADO)
                ->exists();
        }
        return $this->projetos()
            ->where('projeto_id', $projeto->id)
            ->where('tipo_vinculo', TipoVinculo::COLABORADOR)
            ->where('status', StatusVinculoProjeto::APROVADO)
            ->exists();
    }

    public function isVinculoProjetoPendente(?Projeto $projeto = null)
    {
        if ($projeto === null) {
            return $this->vinculos()
                ->where('status', StatusVinculoProjeto::PENDENTE)->exists();
        }
        return $this->projetos()
            ->where('projeto_id', $projeto->id)
            ->where('status', StatusVinculoProjeto::PENDENTE)
            ->exists();
    }

    /**
     * Obtém um campo extra
     */
    public function getCampoExtra(string $key, $default = null)
    {
        return data_get($this->campos_extra, $key, $default);
    }

    /**
     *  Insere um campo extra
     */
    public function setCampoExtra(string $key, $value): void
    {
        $campos = $this->campos_extra ?? [];
        data_set($campos, $key, $value);
        $this->campos_extra = $campos;
    }

    /**
     * Remove um valor específico de campos extras
     */
    public function removeCampoExtra(string $key): void
    {
        $campos = $this->campos_extra ?? [];
        data_forget($campos, $key);
        $this->campos_extra = $campos;
    }

    /**
     * Verifica se um campo específico existe nos campos_extras
     */
    public function hasCampoExtra(string $key): bool
    {
        return data_get($this->campos_extras, $key) !== null;
    }

    /**
     * Mescla novos dados com o campo JSONB existente
     */
    public function mergeCamposExtra(array $data): void
    {
        $this->campos_extra = array_merge($this->campos_extra ?? [], $data);
    }

    /**
     * Accessor para foto_url - retorna URL completa da foto
     */
    public function getFotoUrlAttribute($value): ?string
    {
        if (!$value) {
            return null;
        }

        // Se já é uma URL completa, retorna como está
        if (str_starts_with($value, 'http')) {
            return $value;
        }

        // Se já começa com /storage/, retorna como está
        if (str_starts_with($value, '/storage/')) {
            return $value;
        }

        // Se é um caminho relativo, adiciona /storage/
        return '/storage/' . $value;
    }
}
