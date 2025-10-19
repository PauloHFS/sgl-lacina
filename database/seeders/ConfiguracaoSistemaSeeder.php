<?php

namespace Database\Seeders;

use App\Models\ConfiguracaoSistema;
use Illuminate\Database\Seeder;

class ConfiguracaoSistemaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ConfiguracaoSistema::updateOrCreate(
            ['chave' => 'senha_laboratorio'],
            [
                'valor' => '1234',
                'descricao' => 'Senha para cadastro no laboratório',
            ]
        );

        ConfiguracaoSistema::updateOrCreate(
            ['chave' => 'limites_de_campos_extras'],
            [
                'valor' => '5',
                'descricao' => 'Limite de campos extras no perfil de usuários',
            ]
        );
    }
}
