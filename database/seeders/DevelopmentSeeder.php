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

        // Exibe estatísticas finais
        $this->showFinalStats();

        $this->command->info('✅ Seeders de desenvolvimento executados com sucesso!');
    }

    /**
     * Exibe estatísticas finais dos dados criados
     */
    private function showFinalStats(): void
    {
        $this->command->info('📊 Estatísticas finais:');

        // Usuários por status
        $totalUsuarios = User::count();
        $aceitos = User::where('status_cadastro', StatusCadastro::ACEITO)->whereNull('deleted_at')->count();
        $pendentes = User::where('status_cadastro', StatusCadastro::PENDENTE)->count();
        $recusados = User::where('status_cadastro', StatusCadastro::RECUSADO)->count();
        $inativos = User::where('status_cadastro', StatusCadastro::ACEITO)->whereNotNull('deleted_at')->count();

        $this->command->info("   👤 Usuários: {$totalUsuarios} total");
        $this->command->info("      - Aceitos (ativos): {$aceitos}");
        $this->command->info("      - Pendentes: {$pendentes}");
        $this->command->info("      - Recusados: {$recusados}");
        $this->command->info("      - Inativos: {$inativos}");

        // Projetos por status temporal
        $totalProjetos = Projeto::count();
        $projetosAtivos = Projeto::where('data_inicio', '<=', now())
            ->where('data_termino', '>=', now())->count();
        $projetosFinalizados = Projeto::where('data_termino', '<', now())->count();
        $projetosFuturos = Projeto::where('data_inicio', '>', now())->count();

        $this->command->info("   📋 Projetos: {$totalProjetos} total");
        $this->command->info("      - Ativos: {$projetosAtivos}");
        $this->command->info("      - Finalizados: {$projetosFinalizados}");
        $this->command->info("      - Futuros: {$projetosFuturos}");

        // Vínculos por status
        $totalVinculos = UsuarioProjeto::count();
        $vinculosAprovados = UsuarioProjeto::where('status', StatusVinculoProjeto::APROVADO)->count();
        $vinculosPendentes = UsuarioProjeto::where('status', StatusVinculoProjeto::PENDENTE)->count();
        $vinculosRecusados = UsuarioProjeto::where('status', StatusVinculoProjeto::RECUSADO)->count();

        $this->command->info("   🔗 Vínculos: {$totalVinculos} total");
        $this->command->info("      - Aprovados: {$vinculosAprovados}");
        $this->command->info("      - Pendentes: {$vinculosPendentes}");
        $this->command->info("      - Recusados: {$vinculosRecusados}");
    }

    /**
     * Cria usuários de teste para desenvolvimento
     */
    private function createTestUsers(): void
    {
        // Meta: ~100 usuários totais distribuídos uniformemente
        $targetTotal = 100;
        $usuariosExistentes = User::count();

        if ($usuariosExistentes >= $targetTotal) {
            $this->command->info("Já existem {$usuariosExistentes} usuários (meta: {$targetTotal}), pulando criação...");
            return;
        }

        // Docentes coordenadores (sempre criar)
        $this->createDocentes();

        // Distribuição uniforme dos usuários restantes
        $usuariosRestantes = $targetTotal - User::count();

        if ($usuariosRestantes <= 0) {
            return;
        }

        // Distribuição:
        // 60% - Usuários aceitos (ativos)
        // 25% - Usuários pendentes (solicitações de cadastro)
        // 10% - Usuários recusados
        // 5% - Usuários aceitos mas inativos (ex-participantes)

        $aceitos = (int) ($usuariosRestantes * 0.60);
        $pendentes = (int) ($usuariosRestantes * 0.25);
        $recusados = (int) ($usuariosRestantes * 0.10);
        $inativos = $usuariosRestantes - $aceitos - $pendentes - $recusados; // Resto para inativos

        $this->command->info("Criando {$aceitos} usuários aceitos, {$pendentes} pendentes, {$recusados} recusados, {$inativos} inativos...");

        // Usuários aceitos e ativos
        if ($aceitos > 0) {
            User::factory()->cadastroCompleto()->count($aceitos)->create([
                'status_cadastro' => StatusCadastro::ACEITO,
            ]);
        }

        // Usuários com cadastros pendentes
        if ($pendentes > 0) {
            User::factory()->count($pendentes)->create([
                'status_cadastro' => StatusCadastro::PENDENTE,
            ]);
        }

        // Usuários rejeitados
        if ($recusados > 0) {
            User::factory()->count($recusados)->create([
                'status_cadastro' => StatusCadastro::RECUSADO,
            ]);
        }

        // Usuários aceitos mas que já saíram do laboratório (histórico)
        if ($inativos > 0) {
            User::factory()->cadastroCompleto()->count($inativos)->create([
                'status_cadastro' => StatusCadastro::ACEITO,
                'deleted_at' => now()->subDays(rand(30, 365)), // Soft delete para simular saída
            ]);
        }
    }

    /**
     * Cria os docentes coordenadores principais
     */
    private function createDocentes(): void
    {
        $docentes = [
            [
                'email' => 'maxwell@computacao.ufcg.edu.br',
                'name' => 'Maxwell Guimarães de Oliveira',
                'cpf' => '12345678901',
                'telefone' => '(83) 9999-9999',
                'data_nascimento' => '1980-01-01',
            ],
            [
                'email' => 'campelo@computacao.ufcg.edu.br',
                'name' => 'Cláudio Campelo',
                'cpf' => '12345678902',
                'telefone' => '(83) 8888-8888',
                'data_nascimento' => '1970-01-01',
            ],
            [
                'email' => 'paulo.hernane.silva@ccc.ufcg.edu.br',
                'name' => 'Paulo Hernane Fontes e Silva',
                'cpf' => '12345678903',
                'telefone' => '(83) 7777-7777',
                'data_nascimento' => '1975-01-01',
            ],
        ];

        foreach ($docentes as $docenteData) {
            User::firstOrCreate(
                ['email' => $docenteData['email']],
                array_merge($docenteData, [
                    'password' => Hash::make('password123'),
                    'status_cadastro' => StatusCadastro::ACEITO,
                    'genero' => 'MASCULINO',
                ])
            );
        }
    }

    /**
     * Cria projetos de exemplo para desenvolvimento
     */
    private function createTestProjects(): void
    {
        // Meta: ~25 projetos distribuídos uniformemente por status e tipo
        $targetTotal = 25;
        $projetosExistentes = Projeto::count();

        if ($projetosExistentes >= $targetTotal) {
            $this->command->info("Já existem {$projetosExistentes} projetos (meta: {$targetTotal}), pulando criação...");
            return;
        }

        $projetosRestantes = $targetTotal - $projetosExistentes;

        // Distribuição por status temporal:
        // 40% - Projetos ativos (em andamento)
        // 35% - Projetos finalizados (histórico)
        // 15% - Projetos futuros (planejados)
        // 10% - Projetos cancelados/suspensos

        $ativos = (int) ($projetosRestantes * 0.40);
        $finalizados = (int) ($projetosRestantes * 0.35);
        $futuros = (int) ($projetosRestantes * 0.15);
        $cancelados = $projetosRestantes - $ativos - $finalizados - $futuros;

        $this->command->info("Criando {$ativos} projetos ativos, {$finalizados} finalizados, {$futuros} futuros, {$cancelados} cancelados...");

        // Tipos de projeto para variedade
        $tipos = [
            TipoProjeto::PDI,
            TipoProjeto::TCC,
            TipoProjeto::MESTRADO,
            TipoProjeto::DOUTORADO,
            TipoProjeto::SUPORTE,
        ];

        // Projetos ativos (em andamento)
        if ($ativos > 0) {
            foreach (range(1, $ativos) as $i) {
                Projeto::factory()->create([
                    'tipo' => $tipos[array_rand($tipos)],
                    'data_inicio' => now()->subMonths(rand(1, 12)),
                    'data_termino' => now()->addMonths(rand(3, 18)),
                ]);
            }
        }

        // Projetos finalizados
        if ($finalizados > 0) {
            foreach (range(1, $finalizados) as $i) {
                $dataInicio = now()->subMonths(rand(12, 36));
                Projeto::factory()->create([
                    'tipo' => $tipos[array_rand($tipos)],
                    'data_inicio' => $dataInicio,
                    'data_termino' => $dataInicio->copy()->addMonths(rand(6, 24)),
                ]);
            }
        }

        // Projetos futuros
        if ($futuros > 0) {
            foreach (range(1, $futuros) as $i) {
                $dataInicio = now()->addMonths(rand(1, 6));
                Projeto::factory()->create([
                    'tipo' => $tipos[array_rand($tipos)],
                    'data_inicio' => $dataInicio,
                    'data_termino' => $dataInicio->copy()->addMonths(rand(6, 24)),
                ]);
            }
        }

        // Projetos cancelados/suspensos (com data de término no passado e curta duração)
        if ($cancelados > 0) {
            foreach (range(1, $cancelados) as $i) {
                $dataInicio = now()->subMonths(rand(6, 24));
                Projeto::factory()->create([
                    'tipo' => $tipos[array_rand($tipos)],
                    'data_inicio' => $dataInicio,
                    'data_termino' => $dataInicio->copy()->addMonths(rand(1, 3)),
                ]);
            }
        }

        // Projeto específico para demonstração (sempre criar)
        Projeto::firstOrCreate(
            ['nome' => 'Sistema RH LaCInA'],
            [
                'descricao' => 'Sistema de gestão de recursos humanos do laboratório para gerenciar colaboradores, projetos e vínculos de participação.',
                'cliente' => 'LaCInA - UFCG',
                'tipo' => TipoProjeto::PDI,
                'data_inicio' => now()->subMonths(6),
                'data_termino' => now()->addMonths(12),
                'slack_url' => 'https://lacina.slack.com/channels/rh-sistema',
                'discord_url' => 'https://discord.gg/lacina-rh',
                'board_url' => 'https://trello.com/b/lacina-rh-sistema',
                'git_url' => 'https://github.com/lacina/rh-sistema',
            ]
        );
    }

    /**
     * Cria vínculos de teste entre usuários e projetos
     */
    private function createTestVinculos(): void
    {
        $usuariosAtivos = User::where('status_cadastro', StatusCadastro::ACEITO)
            ->whereNull('deleted_at')
            ->get();

        $projetos = Projeto::all();

        if ($usuariosAtivos->count() === 0 || $projetos->count() === 0) {
            $this->command->warn('Não há usuários ativos ou projetos suficientes para criar vínculos.');
            return;
        }

        // Limpa vínculos existentes para evitar duplicatas
        UsuarioProjeto::truncate();

        $this->command->info("Criando vínculos entre {$usuariosAtivos->count()} usuários e {$projetos->count()} projetos...");

        // Para cada projeto ativo, criar vínculos
        $projetosAtivos = $projetos->filter(function ($projeto) {
            return $projeto->data_inicio <= now() && $projeto->data_termino >= now();
        });

        $projetosFinalizados = $projetos->filter(function ($projeto) {
            return $projeto->data_termino < now();
        });

        $projetosFuturos = $projetos->filter(function ($projeto) {
            return $projeto->data_inicio > now();
        });

        // Vínculos para projetos ativos
        foreach ($projetosAtivos as $projeto) {
            $numParticipantes = rand(2, 8); // 2-8 participantes por projeto
            $participantes = $usuariosAtivos->random(min($numParticipantes, $usuariosAtivos->count()));

            foreach ($participantes as $usuario) {
                $this->createVinculo($usuario, $projeto, StatusVinculoProjeto::APROVADO, $projeto->data_inicio);
            }

            // Adiciona algumas solicitações pendentes para projetos ativos
            if (rand(1, 100) <= 30) { // 30% chance de ter solicitações pendentes
                $solicitantes = $usuariosAtivos->diff($participantes)->random(rand(1, 3));
                foreach ($solicitantes as $solicitante) {
                    $this->createVinculo($solicitante, $projeto, StatusVinculoProjeto::PENDENTE, now());
                }
            }
        }

        // Vínculos para projetos finalizados (apenas aprovados no passado)
        foreach ($projetosFinalizados as $projeto) {
            $numParticipantes = rand(1, 6);
            $participantes = $usuariosAtivos->random(min($numParticipantes, $usuariosAtivos->count()));

            foreach ($participantes as $usuario) {
                $this->createVinculo(
                    $usuario,
                    $projeto,
                    StatusVinculoProjeto::APROVADO,
                    $projeto->data_inicio,
                    $projeto->data_termino
                );
            }
        }

        // Vínculos para projetos futuros (mix de pendentes e alguns já aprovados)
        foreach ($projetosFuturos as $projeto) {
            $numSolicitacoes = rand(1, 5);
            $solicitantes = $usuariosAtivos->random(min($numSolicitacoes, $usuariosAtivos->count()));

            foreach ($solicitantes as $solicitante) {
                // 70% pendente, 30% já aprovado
                $status = rand(1, 100) <= 70 ? StatusVinculoProjeto::PENDENTE : StatusVinculoProjeto::APROVADO;
                $this->createVinculo($solicitante, $projeto, $status, now());
            }
        }

        // Cria algumas solicitações recusadas para realismo
        for ($i = 0; $i < 8; $i++) {
            $usuario = $usuariosAtivos->random();
            $projeto = $projetos->random();

            $this->createVinculo($usuario, $projeto, StatusVinculoProjeto::RECUSADO, now()->subDays(rand(1, 30)));
        }

        $totalVinculos = UsuarioProjeto::count();
        $this->command->info("✅ Criados {$totalVinculos} vínculos de usuário-projeto");
    }

    /**
     * Cria um vínculo específico entre usuário e projeto
     */
    private function createVinculo(User $usuario, Projeto $projeto, StatusVinculoProjeto $status, $dataInicio, $dataFim = null): void
    {
        // Evita vínculos duplicados
        $vinculoExistente = UsuarioProjeto::where('usuario_id', $usuario->id)
            ->where('projeto_id', $projeto->id)
            ->first();

        if ($vinculoExistente) {
            return;
        }

        $funcoes = Funcao::cases();
        $tiposVinculo = TipoVinculo::cases();

        UsuarioProjeto::create([
            'usuario_id' => $usuario->id,
            'projeto_id' => $projeto->id,
            'status' => $status,
            'funcao' => $funcoes[array_rand($funcoes)],
            'tipo_vinculo' => $tiposVinculo[array_rand($tiposVinculo)],
            'carga_horaria_semanal' => rand(10, 40),
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim,
            'trocar' => rand(1, 100) <= 5, // 5% chance de querer trocar
        ]);
    }
}
