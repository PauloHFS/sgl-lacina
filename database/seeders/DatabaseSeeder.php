<?php

namespace Database\Seeders;

use App\Enums\TipoVinculo;
use App\Enums\Funcao;
use App\Enums\StatusVinculoProjeto;
use App\Enums\TipoProjeto;
use App\Enums\StatusCadastro;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Projeto;
use App\Models\UsuarioProjeto;
use Illuminate\Support\Facades\Hash;
use App\Models\Banco;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application\'s database.
     */
    public function run(): void
    {

        $bancos = [
            ['codigo' => '001', 'nome' => 'Banco do Brasil'],
            ['codigo' => '033', 'nome' => 'Santander'],
            ['codigo' => '104', 'nome' => 'Caixa Econômica Federal'],
            ['codigo' => '237', 'nome' => 'Bradesco'],
            ['codigo' => '341', 'nome' => 'Itaú Unibanco'],
            ['codigo' => '745', 'nome' => 'Citibank'],
            ['codigo' => '399', 'nome' => 'HSBC'],
            ['codigo' => '756', 'nome' => 'Bancoob (Sicoob)'],
            ['codigo' => '748', 'nome' => 'Sicredi'],
            ['codigo' => '077', 'nome' => 'Banco Inter'],
            ['codigo' => '260', 'nome' => 'Nubank'],
            ['codigo' => '290', 'nome' => 'PagSeguro'],
            ['codigo' => '380', 'nome' => 'PicPay'],
            ['codigo' => '136', 'nome' => 'Unicred'],
            ['codigo' => '323', 'nome' => 'Mercado Pago'],
            ['codigo' => '341', 'nome' => 'Iti (Itaú)'],
            ['codigo' => '197', 'nome' => 'Stone Pagamentos'],
            ['codigo' => '102', 'nome' => 'XP Investimentos'],
            ['codigo' => '129', 'nome' => 'UBS Brasil'],
        ];

        foreach ($bancos as $banco) {
            Banco::create([
                'codigo' => $banco['codigo'],
                'nome' => $banco['nome'],
            ]);
        }

        $projeto1 = Projeto::factory()->create([
            'nome' => 'Projeto ABC',
            'descricao' => 'Descrição do projeto ABC',
            'data_inicio' => now()->subYear(),
            'data_termino' => now()->addYear(),
            'cliente' => 'Dell',
            'tipo' => TipoProjeto::PDI,
        ]);
        $projeto2 = Projeto::factory()->create([
            'nome' => 'Projeto DEF',
            'descricao' => 'Descrição do projeto DEF',
            'data_inicio' => now()->subYear(),
            'data_termino' => now()->addYear(),
            'cliente' => 'Keepee',
            'tipo' => TipoProjeto::SUPORTE,
        ]);
        $projeto3 = Projeto::factory()->create([
            'nome' => 'Projeto GHI',
            'descricao' => 'Descrição do projeto GHI',
            'data_inicio' => now()->subYear(),
            'data_termino' => now()->addYear(),
            'cliente' => 'Google',
            'tipo' => TipoProjeto::SUPORTE,
        ]);
        $projeto4 = Projeto::factory()->create([
            'nome' => 'Projeto JKL',
            'descricao' => 'Descrição do projeto JKL',
            'data_inicio' => now()->subYear(),
            'data_termino' => now()->addYear(),
            'cliente' => 'Microsoft',
            'tipo' => TipoProjeto::PDI,
        ]);
        $projeto5 = Projeto::factory()->create([
            'nome' => 'Projeto MNO',
            'descricao' => 'Descrição do projeto MNO',
            'data_inicio' => now()->subYear(),
            'data_termino' => now()->addYear(),
            'cliente' => 'Apple',
            'tipo' => TipoProjeto::PDI,
        ]);

        $maxwellUser = User::factory()->cadastroCompleto()->create([
            'name' => 'Maxwell Guimarães de Oliveira',
            'email' => 'maxwell@computacao.ufcg.edu.br',
            'password' => Hash::make('Ab@12312'),
            'status_cadastro' => StatusCadastro::ACEITO,
        ]);
        UsuarioProjeto::factory()->create([
            'usuario_id' => $maxwellUser->id,
            'projeto_id' => $projeto1->id,
            'tipo_vinculo' => TipoVinculo::COORDENADOR,
            'funcao' => Funcao::COORDENADOR,
            'carga_horaria_semanal' => 20,
            'data_inicio' => $projeto1->data_inicio,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);
        UsuarioProjeto::factory()->create([
            'usuario_id' => $maxwellUser->id,
            'projeto_id' => $projeto2->id,
            'tipo_vinculo' => TipoVinculo::COORDENADOR,
            'funcao' => Funcao::COORDENADOR,
            'carga_horaria_semanal' => 10,
            'data_inicio' => $projeto2->data_inicio,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        $campeloUser = User::factory()->cadastroCompleto()->create([
            'name' => 'Campelo',
            'email' => 'campelo@computacao.ufcg.edu.br',
            'password' => Hash::make('Ab@12312'),
            'status_cadastro' => StatusCadastro::ACEITO,
        ]);
        UsuarioProjeto::factory()->create([
            'usuario_id' => $campeloUser->id,
            'projeto_id' => $projeto1->id,
            'tipo_vinculo' => TipoVinculo::COORDENADOR,
            'funcao' => Funcao::COORDENADOR,
            'carga_horaria_semanal' => 20,
            'data_inicio' => $projeto1->data_inicio,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);
        UsuarioProjeto::factory()->create([
            'usuario_id' => $campeloUser->id,
            'projeto_id' => $projeto2->id,
            'tipo_vinculo' => TipoVinculo::COORDENADOR,
            'funcao' => Funcao::COORDENADOR,
            'carga_horaria_semanal' => 10,
            'data_inicio' => $projeto2->data_inicio,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        // usuario colaborador de um projeto (Ativo)
        $usuario_ativo = User::factory()->cadastroCompleto()->create([
            'name' => 'Usuário Ativo',
            'email' => 'usuario_ativo@ccc.ufcg.edu.br',
            'password' => Hash::make('Ab@12312'),
            'status_cadastro' => StatusCadastro::ACEITO,
        ]);
        UsuarioProjeto::factory()->create([
            'usuario_id' => $usuario_ativo->id,
            'projeto_id' => $projeto1->id,
            'tipo_vinculo' => TipoVinculo::COLABORADOR,
            'funcao' => Funcao::ALUNO,
            'carga_horaria_semanal' => 15,
            'data_inicio' => $projeto1->data_inicio->addWeek(),
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        // usuario com cadastro pendente no laboratório
        User::factory()->cadastroCompleto()->create([
            'name' => 'Usuário Cadastro Pendente',
            'email' => 'usuario_pendente@ccc.ufcg.edu.br',
            'password' => Hash::make('Ab@12312'),
            'status_cadastro' => StatusCadastro::PENDENTE,
        ]);

        // usuario aceito no laboratório com solicitação de vínculo pendente em um projeto
        $usuarioVinculoPendente = User::factory()->cadastroCompleto()->create([
            'name' => 'Usuário Vínculo Pendente',
            'email' => 'usuariovinculopendente@gmail.com',
            'password' => Hash::make('Ab@12312'),
            'status_cadastro' => StatusCadastro::ACEITO,
        ]);
        UsuarioProjeto::factory()->create([
            'usuario_id' => $usuarioVinculoPendente->id,
            'projeto_id' => $projeto1->id,
            'tipo_vinculo' => TipoVinculo::COLABORADOR,
            'funcao' => Funcao::ALUNO,
            'carga_horaria_semanal' => 10,
            'data_inicio' => now(),
            'status' => StatusVinculoProjeto::PENDENTE,
        ]);

        // usuário inativo (inativo no ultimo projeto)
        $usuario_inativo = User::factory()->cadastroCompleto()->create([
            'name' => 'Usuário Inativo',
            'email' => 'usuario_inativo@ccc.ufcg.edu.br',
            'password' => Hash::make('Ab@12312'),
            'status_cadastro' => StatusCadastro::ACEITO,
        ]);
        UsuarioProjeto::factory()->create([
            'usuario_id' => $usuario_inativo->id,
            'projeto_id' => $projeto1->id,
            'tipo_vinculo' => TipoVinculo::COLABORADOR,
            'funcao' => Funcao::ALUNO,
            'carga_horaria_semanal' => 10,
            'data_inicio' => now()->subMonth(),
            'data_fim' => now(),
            'status' => StatusVinculoProjeto::INATIVO,
        ]);

        // usuario antigo com varias (5) trocas de projeto
        $usuario_antigo = User::factory()->cadastroCompleto()->create([
            'name' => 'Usuário Antigo',
            'email' => 'usuario_antigo@ccc.ufcg.edu.br',
            'password' => Hash::make('Ab@12312'),
            'status_cadastro' => StatusCadastro::ACEITO,
        ]);
        UsuarioProjeto::factory()->create([
            'usuario_id' => $usuario_antigo->id,
            'projeto_id' => $projeto1->id,
            'tipo_vinculo' => TipoVinculo::COLABORADOR,
            'funcao' => Funcao::ALUNO,
            'carga_horaria_semanal' => 10,
            'data_inicio' => now()->subYears(5),
            'data_fim' => now()->subYears(4),
            'status' => StatusVinculoProjeto::INATIVO,
        ]);
        UsuarioProjeto::factory()->create([
            'usuario_id' => $usuario_antigo->id,
            'projeto_id' => $projeto2->id,
            'tipo_vinculo' => TipoVinculo::COLABORADOR,
            'funcao' => Funcao::DESENVOLVEDOR,
            'carga_horaria_semanal' => 15,
            'data_inicio' => now()->subYears(4),
            'data_fim' => now()->subYears(3),
            'status' => StatusVinculoProjeto::INATIVO,
        ]);
        UsuarioProjeto::factory()->create([
            'usuario_id' => $usuario_antigo->id,
            'projeto_id' => $projeto3->id,
            'tipo_vinculo' => TipoVinculo::COLABORADOR,
            'funcao' => Funcao::PESQUISADOR,
            'carga_horaria_semanal' => 20,
            'data_inicio' => now()->subYears(3),
            'data_fim' => now()->subYears(2),
            'status' => StatusVinculoProjeto::INATIVO,
        ]);
        UsuarioProjeto::factory()->create([
            'usuario_id' => $usuario_antigo->id,
            'projeto_id' => $projeto4->id,
            'tipo_vinculo' => TipoVinculo::COLABORADOR,
            'funcao' => Funcao::TECNICO,
            'carga_horaria_semanal' => 10,
            'data_inicio' => now()->subYears(2),
            'data_fim' => now()->subYear(),
            'status' => StatusVinculoProjeto::INATIVO,
        ]);
        UsuarioProjeto::factory()->create([
            'usuario_id' => $usuario_antigo->id,
            'projeto_id' => $projeto5->id,
            'tipo_vinculo' => TipoVinculo::COLABORADOR,
            'funcao' => Funcao::ALUNO,
            'carga_horaria_semanal' => 12,
            'data_inicio' => now()->subYear(),
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        User::factory(20)->cadastroCompleto()->create(['status_cadastro' => StatusCadastro::ACEITO]);
        User::factory(5)->cadastroCompleto()->create(['status_cadastro' => StatusCadastro::PENDENTE]);
        User::factory(2)->cadastroCompleto()->create(['status_cadastro' => StatusCadastro::RECUSADO]);
    }
}
