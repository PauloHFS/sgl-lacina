<?php

namespace Database\Seeders;

use App\Models\IntervenienteFinanceiro;
use Illuminate\Database\Seeder;

class IntervenienteFinanceiroSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $intervenientes = [
            ['nome' => 'Nenhum'],
            ['nome' => 'Fundação Parque Tecnológico da Paraíba (PaqTcPB)'],
            // ['nome' => 'Conselho Nacional de Desenvolvimento Científico e Tecnológico (CNPq)'],
            // ['nome' => 'Coordenação de Aperfeiçoamento de Pessoal de Nível Superior (CAPES)'],
            // ['nome' => 'Financiadora de Estudos e Projetos (FINEP)'],
            // ['nome' => 'Fundação de Apoio à Pesquisa do Estado da Paraíba (FAPESQ)'],
        ];

        foreach ($intervenientes as $interveniente) {
            IntervenienteFinanceiro::firstOrCreate($interveniente);
        }
    }
}
