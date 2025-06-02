<?php

namespace Database\Seeders;

use App\Enums\TipoVinculo;
use App\Enums\Funcao;
use App\Enums\StatusVinculoProjeto;
use App\Enums\TipoProjeto;
use App\Enums\StatusCadastro;
use App\Models\User;
use App\Models\Projeto;
use App\Models\UsuarioProjeto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DevelopmentSeeder extends Seeder
{
    /**
     * Executa os seeders para o ambiente de desenvolvimento.
     * 
     * Inclui dados de teste, usuários ficcionais e exemplos
     * para facilitar o desenvolvimento e testes.
     */
    public function run(): void
    {
        $this->command->info('🛠️ Executando seeders para DESENVOLVIMENTO...');

        $this->command->info('👥 Criando usuários de teste...');
        $this->createTestUsers();

        $this->command->info('📋 Criando projetos de exemplo...');
        $this->createTestProjects();

        $this->command->info('🔗 Criando vínculos de teste...');
        $this->createTestVinculos();

        $this->command->info('✅ Seeders de desenvolvimento executados com sucesso!');
    }

    /**
     * Cria usuários de teste para desenvolvimento
     */
    private function createTestUsers(): void
    {
        // Docentes coordenadores
        $maxwell = User::firstOrCreate(
            ['email' => 'maxwell@computacao.ufcg.edu.br'],
            [
                'name' => 'Maxwell Guimarães de Oliveira',
                'password' => Hash::make('password123'),
                'status_cadastro' => StatusCadastro::ACEITO,
                'genero' => 'MASCULINO',
                'data_nascimento' => '1980-01-01',
                'cpf' => '12345678901',
                'telefone' => '(83) 9999-9999',
            ]
        );

        $campelo = User::firstOrCreate(
            ['email' => 'campelo@computacao.ufcg.edu.br'],
            [
                'name' => 'Cláudio Campelo',
                'password' => Hash::make('password123'),
                'status_cadastro' => StatusCadastro::ACEITO,
                'genero' => 'MASCULINO',
                'data_nascimento' => '1970-01-01',
                'cpf' => '12345678902',
                'telefone' => '(83) 8888-8888',
            ]
        );

        $paulo = User::firstOrCreate(
            ['email' => 'paulo.hernane.silva@ccc.ufcg.edu.br'],
            [
                'name' => 'Paulo Hernane Fontes e Silva',
                'password' => Hash::make('password123'),
                'status_cadastro' => StatusCadastro::ACEITO,
                'genero' => 'MASCULINO',
                'data_nascimento' => '1975-01-01',
                'cpf' => '12345678903',
                'telefone' => '(83) 7777-7777',
            ]
        );

        // Discentes de teste - só cria se não houver suficientes
        $discentesExistentes = User::where('status_cadastro', StatusCadastro::ACEITO)
            ->whereNotIn('email', [
                'maxwell@computacao.ufcg.edu.br',
                'campelo@computacao.ufcg.edu.br',
                'paulo.hernane.silva@ccc.ufcg.edu.br'
            ])
            ->count();

        if ($discentesExistentes < 10) {
            User::factory()->cadastroCompleto()->count(10 - $discentesExistentes)->create([
                'status_cadastro' => StatusCadastro::ACEITO,
            ]);
        }

        // Usuários com cadastros pendentes - só cria se não houver suficientes
        $pendenteExistentes = User::where('status_cadastro', StatusCadastro::PENDENTE)->count();
        if ($pendenteExistentes < 5) {
            User::factory()->count(5 - $pendenteExistentes)->create([
                'status_cadastro' => StatusCadastro::PENDENTE,
            ]);
        }

        // Usuários rejeitados - só cria se não houver suficientes
        $rejeitadosExistentes = User::where('status_cadastro', StatusCadastro::RECUSADO)->count();
        if ($rejeitadosExistentes < 2) {
            User::factory()->count(2 - $rejeitadosExistentes)->create([
                'status_cadastro' => StatusCadastro::RECUSADO,
            ]);
        }
    }

    /**
     * Cria projetos de exemplo para desenvolvimento
     */
    private function createTestProjects(): void
    {
        // Verifica se já existem projetos suficientes
        $projetosExistentes = Projeto::count();

        if ($projetosExistentes >= 10) {
            $this->command->info('Projetos já existem, pulando criação...');
            return;
        }

        // Projetos ativos
        Projeto::factory()->count(3)->create([
            'tipo' => TipoProjeto::PDI,
            'data_inicio' => now()->subMonths(6),
            'data_termino' => now()->addMonths(6),
        ]);

        // Projetos finalizados
        Projeto::factory()->count(2)->create([
            'tipo' => TipoProjeto::TCC,
            'data_inicio' => now()->subYear(),
            'data_termino' => now()->subMonths(2),
        ]);

        // Projetos futuros
        Projeto::factory()->count(2)->create([
            'tipo' => TipoProjeto::MESTRADO,
            'data_inicio' => now()->addMonth(),
            'data_termino' => now()->addYear(),
        ]);

        // Projeto específico para demonstração
        Projeto::firstOrCreate(
            ['nome' => 'Sistema RH LaCInA'],
            [
                'descricao' => 'Sistema de gestão de recursos humanos do laboratório',
                'cliente' => 'LaCInA - UFCG',
                'tipo' => TipoProjeto::PDI,
                'data_inicio' => now()->subMonths(3),
                'data_termino' => now()->addMonths(9),
                'slack_url' => 'https://lacina.slack.com/channels/rh-sistema',
                'git_url' => 'https://github.com/lacina/rh-sistema',
            ]
        );
    }

    /**
     * Cria vínculos de teste entre usuários e projetos
     */
    private function createTestVinculos(): void
    {
        $usuarios = User::where('status_cadastro', StatusCadastro::ACEITO)->get();
        $projetos = Projeto::all();

        if ($usuarios->count() > 0 && $projetos->count() > 0) {
            // Vincula alguns usuários aos projetos
            foreach ($projetos->take(3) as $projeto) {
                $usuariosVinculados = $usuarios->random(rand(2, 5));

                foreach ($usuariosVinculados as $usuario) {
                    UsuarioProjeto::factory()->create([
                        'usuario_id' => $usuario->id,
                        'projeto_id' => $projeto->id,
                        'status' => StatusVinculoProjeto::APROVADO,
                        'funcao' => Funcao::cases()[array_rand(Funcao::cases())],
                        'tipo_vinculo' => TipoVinculo::cases()[array_rand(TipoVinculo::cases())],
                        'data_inicio' => $projeto->data_inicio,
                    ]);
                }
            }

            // Cria alguns vínculos pendentes
            UsuarioProjeto::factory()->count(3)->create([
                'usuario_id' => $usuarios->random()->id,
                'projeto_id' => $projetos->random()->id,
                'status' => StatusVinculoProjeto::PENDENTE,
            ]);
        }
    }
}
