<?php

namespace Database\Seeders;

use App\Enums\TipoVinculo;
use App\Enums\Funcao;
use App\Enums\StatusVinculoProjeto; // Added
use App\Enums\TipoProjeto; // Added
use App\Enums\StatusCadastro; // Added
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Projeto;
use App\Models\UsuarioProjeto;
use App\Models\UsuarioVinculo;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application\'s database.
     */
    public function run(): void
    {
        // TODO: Seed Areas de Atuacao, Bancos, Tecnologias if needed

        $projeto1 = Projeto::factory()->create([
            'nome' => 'Projeto ABC',
            'descricao' => 'Descrição do projeto ABC',
            'data_inicio' => now()->subMonth(),
            'data_termino' => now()->addYear(),
            'cliente' => 'Cliente Exemplo 1', // Added
            'tipo' => TipoProjeto::PDI, // Added
            // Optional fields: slack_url, discord_url, board_url, git_url
        ]);

        $projeto2 = Projeto::factory()->create([
            'nome' => 'Projeto DEF',
            'descricao' => 'Descrição do projeto DEF',
            'data_inicio' => now(),
            'data_termino' => now()->addMonths(6),
            'cliente' => 'Cliente Exemplo 2', // Added
            'tipo' => TipoProjeto::TCC, // Added
            // Optional fields: slack_url, discord_url, board_url, git_url
        ]);

        // Assuming UserFactory::cadastroCompleto() fills required fields like cpf, area_atuacao_id etc.
        // usuario coordenador de dois projetos
        $maxwellUser = User::factory()->cadastroCompleto()->create([
            'name' => 'Maxwell Guimarães de Oliveira',
            'email' => 'maxwell@computacao.ufcg.edu.br',
            'password' => Hash::make('Ab@12312'),
            'status_cadastro' => StatusCadastro::ACEITO, // Use Enum
        ]);
        UsuarioProjeto::factory()->create([
            'usuario_id' => $maxwellUser->id,
            'projeto_id' => $projeto1->id,
            'tipo_vinculo' => TipoVinculo::COORDENADOR,
            'funcao' => Funcao::COORDENADOR,
            'carga_horaria_semanal' => 20, // Added
            'data_inicio' => $projeto1->data_inicio,
            'status' => StatusVinculoProjeto::APROVADO, // Use Enum
        ]);
        UsuarioProjeto::factory()->create([
            'usuario_id' => $maxwellUser->id,
            'projeto_id' => $projeto2->id,
            'tipo_vinculo' => TipoVinculo::COORDENADOR,
            'funcao' => Funcao::COORDENADOR,
            'carga_horaria_semanal' => 10, // Added
            'data_inicio' => $projeto2->data_inicio,
            'status' => StatusVinculoProjeto::APROVADO, // Use Enum
        ]);

        // usuario colaborador de um projeto (Ativo)
        $usuario_ativo = User::factory()->cadastroCompleto()->create([
            'name' => 'Usuário Ativo',
            'email' => 'usuario_ativo@ccc.ufcg.edu.br',
            'password' => Hash::make('Ab@12312'),
            'status_cadastro' => StatusCadastro::ACEITO, // Use Enum
        ]);
        UsuarioProjeto::factory()->create([
            'usuario_id' => $usuario_ativo->id,
            'projeto_id' => $projeto1->id,
            'tipo_vinculo' => TipoVinculo::COLABORADOR,
            'funcao' => Funcao::ALUNO,
            'carga_horaria_semanal' => 15, // Added
            'data_inicio' => $projeto1->data_inicio->addWeek(), // Start after project start
            'status' => StatusVinculoProjeto::APROVADO, // Use Enum
        ]);

        // usuario com cadastro pendente no laboratório
        User::factory()->cadastroCompleto()->create([ // Renamed variable for clarity
            'name' => 'Usuário Cadastro Pendente',
            'email' => 'usuario_pendente@ccc.ufcg.edu.br',
            'password' => Hash::make('Ab@12312'),
            'status_cadastro' => StatusCadastro::PENDENTE, // Use Enum
        ]);

        //usuário aceito no laboratório sem vinculo
        User::factory()->cadastroCompleto()->create([ // Renamed variable for clarity
            'name' => 'Usuário Aceito Sem Vínculo',
            'email' => 'usuario_aceito@ccc.ufcg.edu.br',
            'password' => Hash::make('Ab@12312'),
            'status_cadastro' => StatusCadastro::ACEITO, // Use Enum
        ]);

        // usuario aceito no laboratório com solicitação de vínculo pendente em um projeto
        $usuarioVinculoPendente = User::factory()->cadastroCompleto()->create([ // Renamed variable for clarity
            'name' => 'Usuário Vínculo Pendente',
            'email' => 'usuariovinculopendente@gmail.com', // Changed email for uniqueness
            'password' => Hash::make('Ab@12312'),
            'status_cadastro' => StatusCadastro::ACEITO, // Use Enum
        ]);
        UsuarioProjeto::factory()->create([
            'usuario_id' => $usuarioVinculoPendente->id,
            'projeto_id' => $projeto1->id,
            'tipo_vinculo' => TipoVinculo::COLABORADOR,
            'funcao' => Funcao::ALUNO,
            'carga_horaria_semanal' => 10, // Added
            'data_inicio' => now(), // Request date
            'status' => StatusVinculoProjeto::PENDENTE, // Use Enum
        ]);
    }
}
