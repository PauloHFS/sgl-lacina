<?php

namespace App\Models;

use App\Enums\StatusCadastro;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'statusCadastro',

        'genero',
        'data_nascimento',

        'cpf',
        'rg',
        'uf_rg',
        'orgao_emissor_rg',

        'conta_bancaria',
        'agencia',
        'codigo_banco',

        'telefone',

        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',

        'linkedin_url',
        'github_url',
        'figma_url',
        'foto_url',
        'curriculo',
        'area_atuacao',
        'tecnologias',
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

            'statusCadastro' => StatusCadastro::class,

            'data_nascimento' => 'datetime',
        ];
    }

    public function vinculos()
    {
        return $this->hasMany(UsuarioVinculo::class, 'usuario_id');
    }

    public function projetos()
    {
        return $this->belongsToMany(Projeto::class, 'usuario_vinculo', 'usuario_id', 'projeto_id')
            ->withPivot('tipo_vinculo', 'funcao', 'data_inicio', 'data_fim')
            ->withTimestamps();
    }

    public function solicitacoes()
    {
        return $this->hasMany(SolicitacoesProjeto::class, 'usuario_id');
    }

    public function isCoordenador(?Projeto $projeto = null)
    {
        if ($projeto === null) {
            return $this->vinculos()->where('tipo_vinculo', 'COORDENADOR')->exists();
        }
        return $this->projetos()
            ->where('projeto_id', $projeto->id)
            ->where('tipo_vinculo', 'COORDENADOR')
            ->exists();
    }

    public function isColaborador(?Projeto $projeto = null)
    {
        if ($projeto === null) {
            return $this->vinculos()->where('tipo_vinculo', 'COORDENADOR')->exists();
        }
        return $this->projetos()
            ->where('projeto_id', $projeto->id)
            ->where('tipo_vinculo', 'COLABORADOR')
            ->exists();
    }

    public function hasDocumentos()
    {
        return $this->cpf
            && $this->rg
            && $this->uf_rg
            && $this->orgao_emissor_rg;
    }

    public function hasEndereco()
    {
        return $this->cep
            && $this->logradouro
            && $this->numero
            // && $this->complemento // complemento opcional
            && $this->bairro
            && $this->cidade
            && $this->estado;
    }

    public function hasDadosDeContato()
    {
        return (bool)$this->telefone;
    }

    public function hasDadosBancarios()
    {
        return $this->conta_bancaria
            && $this->agencia
            && $this->codigo_banco;
    }

    // TODO: Checar com professor se é obrigatório ou não
    // public function hasDadosProfissionais()
    // {
    //     return $this->curriculo
    //         && $this->area_atuacao
    //         && $this->tecnologias;
    // }

    public function getNameAttribute()
    {
        return $this->nome;
    }
}
