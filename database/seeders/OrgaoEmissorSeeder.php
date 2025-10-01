<?php

namespace Database\Seeders;

use App\Models\OrgaoEmissor;
use Illuminate\Database\Seeder;

class OrgaoEmissorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orgaos = [
            [
                'sigla' => 'Não se aplica',
                'nome' => 'Não se aplica',
            ],
            [
                'sigla' => 'SSP',
                'nome' => 'Secretaria de Segurança Pública',
            ],
            [
                'sigla' => 'SESDS',
                'nome' => 'Secretaria Estadual de Segurança e Defesa Social',
            ],
            ['sigla' => 'DPF', 'nome' => 'Departamento de Polícia Federal'],
            ['sigla' => 'MRE', 'nome' => 'Ministério das Relações Exteriores'],
            ['sigla' => 'COMAER', 'nome' => 'Comando da Aeronáutica'],
            ['sigla' => 'COLOG', 'nome' => 'Comando Logístico do Exército'],
            ['sigla' => 'DGePM', 'nome' => 'Diretoria-Geral do Pessoal da Marinha'],
        ];

        foreach ($orgaos as $orgao) {
            OrgaoEmissor::firstOrCreate(['sigla' => $orgao['sigla']], $orgao);
        }
    }
}