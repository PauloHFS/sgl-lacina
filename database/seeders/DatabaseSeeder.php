<?php

namespace Database\Seeders;

use App\Models\Colaborador;
use App\Models\Docente;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DocenteProjeto;
use App\Models\Projeto;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $colaborador = Colaborador::factory()->withUser([
            'name' => 'Paulo Hernane Fontes e Silva',
            'email' => 'paulo.hernane.silva@ccc.ufcg.edu.br',
            'password' => Hash::make('Ab@12312')
        ])->create();

        $docente = Docente::factory()->withUser([
            'name' => 'Maxwell Guimarães de Oliveira ',
            'email' => 'maxwell@computacao.ufcg.edu.br'
        ])->create();

        $projeto = Projeto::factory()->create([
            'nome' => 'Projeto 1',
            'descricao' => 'Descrição do projeto 1',
            'data_inicio' => now(),
            'data_termino' => now()->addDays(30),
        ]);

        DocenteProjeto::factory()->create([
            'docente_id' => $docente->id,
            'projeto_id' => $projeto->id,
        ]);
    }
}
