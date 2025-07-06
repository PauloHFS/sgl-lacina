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
        IntervenienteFinanceiro::firstOrCreate(
            ['nome' => 'Fundação Parque Tecnológico da Paraíba (PaqTcPB)']
        );
    }
}
