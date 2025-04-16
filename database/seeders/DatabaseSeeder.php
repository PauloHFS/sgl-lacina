<?php

namespace Database\Seeders;

use App\Enums\TipoVinculo;
use App\Enums\Funcao;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Projeto;
use App\Models\UsuarioVinculo;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // $colaborador = Colaborador::factory()->withUser([
        //     'name' => 'Paulo Hernane Fontes e Silva',
        //     'email' => 'paulo.hernane.silva@ccc.ufcg.edu.br',
        //     'password' => Hash::make('Ab@12312')
        // ])->create();

        // $docente = Docente::factory()->withUser([
        //     'name' => 'Maxwell Guimarães de Oliveira ',
        //     'email' => 'maxwell@computacao.ufcg.edu.br'
        // ])->create();

        // $projeto = Projeto::factory()->create([
        //     'nome' => 'Projeto 1',
        //     'descricao' => 'Descrição do projeto 1',
        //     'data_inicio' => now(),
        //     'data_termino' => now()->addDays(30),
        // ]);

        // DocenteProjeto::factory()->create([
        //     'docente_id' => $docente->id,
        //     'projeto_id' => $projeto->id,
        // ]);

        $pauloUser = User::factory()->cadastroCompleto()->create([
            'name' => 'Paulo Hernane Fontes e Silva',
            'email' => 'paulo.hernane.silva@ccc.ufcg.edu.br',
            'password' => Hash::make('Ab@12312'),
            'statusCadastro' => 'ACEITO',
        ]);

        $maxwellUser = User::factory()->cadastroCompleto()->create([
            'name' => 'Maxwell Guimarães de Oliveira',
            'email' => 'maxwell@computacao.ufcg.edu.br',
            'password' => Hash::make('Ab@12312'),
            'statusCadastro' => 'ACEITO',
        ]);

        $projeto1 = Projeto::factory()->create([
            'nome' => 'Projeto ABC',
            'descricao' => 'Descrição do projeto ABC',
            'data_inicio' => now(),
            'data_termino' => now()->addYear(),
        ]);

        $projeto2 = Projeto::factory()->create([
            'nome' => 'Projeto DEF',
            'descricao' => 'Descrição do projeto DEF',
            'data_inicio' => now(),
            'data_termino' => now()->addYear(),
        ]);

        UsuarioVinculo::factory()->create([
            'usuario_id' => $maxwellUser->id,
            'projeto_id' => $projeto1->id,
            'tipo_vinculo' => TipoVinculo::COORDENADOR,
            'funcao' => Funcao::COORDENADOR,
            'data_inicio' => now(),
            'status' => 'APROVADO',
        ]);

        UsuarioVinculo::factory()->create([
            'usuario_id' => $maxwellUser->id,
            'projeto_id' => $projeto2->id,
            'tipo_vinculo' => TipoVinculo::COORDENADOR,
            'funcao' => Funcao::COORDENADOR,
            'data_inicio' => now(),
            'status' => 'APROVADO',
        ]);

        UsuarioVinculo::factory()->create([
            'usuario_id' => $pauloUser->id,
            'projeto_id' => $projeto1->id,
            'tipo_vinculo' => TipoVinculo::COLABORADOR,
            'funcao' => Funcao::ALUNO,
            'data_inicio' => now(),
            'status' => 'APROVADO',
        ]);
    }
}
