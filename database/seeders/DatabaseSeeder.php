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

        // usuario coordenador de um projeto e colaborador de outro
        $maxwellUser = User::factory()->cadastroCompleto()->create([
            'name' => 'Maxwell Guimarães de Oliveira',
            'email' => 'maxwell@computacao.ufcg.edu.br',
            'password' => Hash::make('Ab@12312'),
            'statusCadastro' => 'ACEITO',
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

        // usuario colaborador de um projeto (Ativo)
        $usuario_ativo = User::factory()->cadastroCompleto()->create([
            'name' => 'Usuário Ativo',
            'email' => 'usuario_ativo@ccc.ufcg.edu.br',
            'password' => Hash::make('Ab@12312'),
            'statusCadastro' => 'ACEITO',
        ]);
        UsuarioVinculo::factory()->create([
            'usuario_id' => $usuario_ativo->id,
            'projeto_id' => $projeto1->id,
            'tipo_vinculo' => TipoVinculo::COLABORADOR,
            'funcao' => Funcao::ALUNO,
            'data_inicio' => now(),
            'status' => 'APROVADO',
        ]);

        // usuario pendente no laboratório (vinculo_pendente)
        $usuario_vinculo_pendente = User::factory()->cadastroCompleto()->create([
            'name' => 'Usuário Pendente',
            'email' => 'usuario_pendente@ccc.ufcg.edu.br',
            'password' => Hash::make('Ab@12312'),
            'statusCadastro' => 'PENDENTE',
        ]);

        //usuário aceito no laboratório sem vinculo (inativo?)
        $usuarioAceito = User::factory()->cadastroCompleto()->create([
            'name' => 'Usuário Aceito',
            'email' => 'usuario_aceito@ccc.ufcg.edu.br',
            'password' => Hash::make('Ab@12312'),
            'statusCadastro' => 'ACEITO',
        ]);

        // usuario aceito no laboratório com solicitação de entrar num projeto (aprovacao_pendente)
        $usuarioAprovacaoPendente = User::factory()->cadastroCompleto()->create([
            'name' => 'Usuário Aprovacao Pendente',
            'email' => 'usuarioaprovacaopendente@gmail.com',
            'password' => Hash::make('Ab@12312'),
            'statusCadastro' => 'ACEITO',
        ]);
        UsuarioVinculo::factory()->create([
            'usuario_id' => $usuarioAprovacaoPendente->id,
            'projeto_id' => $projeto1->id,
            'tipo_vinculo' => TipoVinculo::COLABORADOR,
            'funcao' => Funcao::ALUNO,
            'data_inicio' => now(),
            'status' => 'PENDENTE',
        ]);
    }
}
