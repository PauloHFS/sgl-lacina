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

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'status_cadastro',
        'curriculo_lattes_url',
        'linkedin_url',
        'github_url',
        'figma_url',
        'foto_url',
        'area_atuacao', // Corrigido de area_atuacao_id e para corresponder ao formulário/tabela
        'tecnologias', // Adicionado
        'genero',
        'data_nascimento',
        'cpf',
        'rg',
        'uf_rg',
        'orgao_emissor_rg',
        'telefone',
        'banco_id', // Adicionado
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

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function uniqueIds()
    {
        return ['id'];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status_cadastro' => StatusCadastro::class,
            'genero' => Genero::class,
            'data_nascimento' => 'date',
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
            ->withPivot('id', 'tipo_vinculo', 'funcao', 'status', 'carga_horaria_semanal', 'data_inicio', 'data_fim') // Added 'id' to withPivot
            ->withTimestamps();
    }

    /**
     * Get the bank associated with the user.
     *
     * This defines a BelongsTo relationship between the User and Banco models.
     *
     * @return BelongsTo
     */
    public function banco(): BelongsTo
    {
        return $this->belongsTo(Banco::class, 'banco_id');
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
}
