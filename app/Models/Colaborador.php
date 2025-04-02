<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

/**
 * 
 * Se mudar o modelo, lembrar de mudar também em: /resources/js/types/index.d.ts
 */
class Colaborador extends Model
{
    use HasFactory, Searchable;

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


    public function toSearchableArray()
    {
        $array = $this->toArray();

        $user = $this->user;

        if ($user) {
            $array['name'] = $user->name;
            $array['email'] = $user->email;
        }

        return $array;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }
}
