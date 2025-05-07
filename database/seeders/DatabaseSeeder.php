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
            'data_inicio' => now()->subMonth(),
            'data_termino' => now()->addYear(),
            'cliente' => 'Cliente Exemplo 1',
            'tipo' => TipoProjeto::PDI,
        ]);

        $projeto2 = Projeto::factory()->create([
            'nome' => 'Projeto DEF',
            'descricao' => 'Descrição do projeto DEF',
            'data_inicio' => now(),
            'data_termino' => now()->addMonths(6),
            'cliente' => 'Cliente Exemplo 2',
            'tipo' => TipoProjeto::TCC,
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

        //usuário aceito no laboratório sem vinculo
        User::factory()->cadastroCompleto()->create([
            'name' => 'Usuário Aceito Sem Vínculo',
            'email' => 'usuario_aceito@ccc.ufcg.edu.br',
            'password' => Hash::make('Ab@12312'),
            'status_cadastro' => StatusCadastro::ACEITO,
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
            'data_inicio' => now(), // Request date
            'status' => StatusVinculoProjeto::PENDENTE,
        ]);
    }
}
