<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracaoSistema extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'configuracao_sistemas';

    protected $fillable = [
        'chave',
        'valor',
        'descricao',
    ];

    /**
     * Busca uma configuração por chave
     */
    public static function obterValor(string $chave): ?string
    {
        $config = self::where('chave', $chave)->first();

        return $config?->valor;
    }

    /**
     * Define ou atualiza uma configuração
     */
    public static function definirValor(string $chave, string $valor, ?string $descricao = null): void
    {
        self::updateOrCreate(
            ['chave' => $chave],
            [
                'valor' => $valor,
                'descricao' => $descricao,
            ]
        );
    }
}
