<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ConfiguracaoSistema;

class ConfiguracaoSistemaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Configuração da senha do laboratório
        ConfiguracaoSistema::updateOrCreate(
            ['chave' => 'senha_laboratorio'],
            [
                'valor' => bcrypt('1234'),
                'descricao' => 'Senha para cadastro no laboratório',
            ]
        );
    }
}
