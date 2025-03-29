<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Colaborador extends Model
{
    use HasFactory;

    protected $table = 'colaboradores';

    protected $fillable = [
        'id',
        'linkedin',
        'github',
        'figma',
        'foto',
        'curriculo',
        'area_atuacao',
        'tecnologias',
        'cpf',
        'rg',
        'uf_rg',
        'conta_bancaria',
        'agencia',
        'codigo_banco',
        'telefone'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }
}
