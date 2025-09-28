<?php

namespace Database\Seeders;

use App\Enums\TipoVinculo;
use App\Enums\Funcao;
use App\Enums\StatusVinculoProjeto;
use App\Enums\TipoProjeto;
use App\Enums\StatusCadastro;
use App\Enums\Genero;
use App\Enums\TipoHorario;
use App\Models\Banco;
use App\Models\Baia;
use App\Models\HistoricoUsuarioProjeto;
use App\Models\Horario;
use App\Models\Projeto;
use App\Models\Sala;
use App\Models\User;
use App\Models\UsuarioProjeto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
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

        $maxwell = $this->createUser([
            'name' => 'Maxwell Guimarães de Oliveira',
            'email' => 'maxwell@computacao.ufcg.edu.br',
            'cpf' => '12345678901',
            'data_nascimento' => '2000-01-01',
            'telefone' => '83999990001',
            'rg' => '1234567',
            'conta_bancaria' => '12345-6',
        ]);

        $maxwell->is_coordenador_master = true;
        $maxwell->save();

        $campelo = $this->createUser([
            'name' => 'Campelo',
            'email' => 'campelo@computacao.ufcg.edu.br',
            'cpf' => '98765432100',
            'data_nascimento' => '2000-01-01',
            'telefone' => '83999990002',
            'rg' => '7654321',
            'conta_bancaria' => '54321-0',
        ]);

        $paulo = $this->createUser([
            'name' => 'Paulo Hernane Silva',
            'email' => 'paulo.hernane.silva@ccc.ufcg.edu.br',
            'cpf' => '61562374370',
            'data_nascimento' => '2000-01-01',
            'telefone' => '99984297519',
            'rg' => '1112223',
            'conta_bancaria' => '11111-1',
        ]);

        $this->createTestUsers();

        $this->command->info('📋 Criando projetos de exemplo...');
        // Criar projetos
        $projetoTCC = $this->createProject([
            'nome' => 'Sistema de Gerenciamento de Laboratório - LaCInA',
            'descricao' => 'Desenvolvimento de sistema web para gestão de recursos humanos, projetos e colaboradores do Laboratório de Computação Inteligente Aplicada da UFCG.',
            'data_inicio' => '2024-03-01',
            'data_termino' => '2025-08-27',
            'cliente' => 'LaCInA - UFCG',
            'tipo' => TipoProjeto::TCC,
        ]);

        $projetoPDI = $this->createProject([
            'nome' => 'TS ETL - Sistema de Extração e Transformação de Dados',
            'descricao' => 'Desenvolvimento de pipeline ETL para processamento de dados temporais usando TypeScript e tecnologias modernas de Big Data.',
            'data_inicio' => '2024-01-15',
            'data_termino' => '2025-12-31',
            'cliente' => 'CQS',
            'tipo' => TipoProjeto::PDI,
        ]);
        $this->createTestProjects();

        $this->command->info('🔗 Criando vínculos de teste...');

        // Criar vínculos de coordenação
        $this->createProjectLink([
            'usuario_id' => $maxwell->id,
            'projeto_id' => $projetoTCC->id,
            'tipo_vinculo' => TipoVinculo::COORDENADOR,
            'funcao' => Funcao::COORDENADOR,
            'carga_horaria' => 8,
            'data_inicio' => '2024-03-01',
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        $this->createProjectLink([
            'usuario_id' => $maxwell->id,
            'projeto_id' => $projetoPDI->id,
            'tipo_vinculo' => TipoVinculo::COORDENADOR,
            'funcao' => Funcao::COORDENADOR,
            'carga_horaria' => 12,
            'data_inicio' => '2024-01-15',
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        // Criar vínculo de colaboração (TCC)
        $this->createProjectLink([
            'usuario_id' => $paulo->id,
            'projeto_id' => $projetoTCC->id,
            'tipo_vinculo' => TipoVinculo::COLABORADOR,
            'funcao' => Funcao::ALUNO,
            'carga_horaria' => 20,
            'data_inicio' => '2024-03-01',
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        // Garantir que Paulo Hernane participe de pelo menos 2 projetos
        $this->createProjectLink([
            'usuario_id' => $paulo->id,
            'projeto_id' => $projetoPDI->id,
            'tipo_vinculo' => TipoVinculo::COLABORADOR,
            'funcao' => Funcao::DESENVOLVEDOR,
            'carga_horaria' => 16,
            'data_inicio' => '2024-01-15',
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        $this->createTestVinculos();

        $this->command->info('⏰ Criando horários de teste...');
        $this->createTestHorarios($paulo, $maxwell, $campelo);

        $this->command->info('🏢 Criando salas e baias de teste...');
        $this->createTestSalasEBaias();

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

        // Estatísticas de coordenadores
        $projetosComCoordenador = Projeto::whereHas('usuarios', function ($query) {
            $query->where('usuario_projeto.funcao', Funcao::COORDENADOR)
                ->where('usuario_projeto.status', StatusVinculoProjeto::APROVADO);
        })->count();

        $projetosComCoordenadorHistorico = Projeto::whereHas('historicoUsuarioProjeto', function ($query) {
            $query->where('funcao', Funcao::COORDENADOR)
                ->where('status', StatusVinculoProjeto::APROVADO);
        })->count();

        $totalComCoordenador = $projetosComCoordenador + $projetosComCoordenadorHistorico;

        $this->command->info("   👑 Coordenadores:");
        $this->command->info("      - Projetos com coordenador ativo: {$projetosComCoordenador}");
        $this->command->info("      - Projetos com coordenador (histórico): {$projetosComCoordenadorHistorico}");
        $this->command->info("      - Total projetos com coordenador: {$totalComCoordenador}");

        // Vínculos por status
        $totalVinculos = UsuarioProjeto::count();
        $vinculosAprovados = UsuarioProjeto::where('status', StatusVinculoProjeto::APROVADO)->count();
        $vinculosPendentes = UsuarioProjeto::where('status', StatusVinculoProjeto::PENDENTE)->count();
        $vinculosRecusados = UsuarioProjeto::where('status', StatusVinculoProjeto::RECUSADO)->count();

        $this->command->info("   🔗 Vínculos Ativos: {$totalVinculos} total");
        $this->command->info("      - Aprovados: {$vinculosAprovados}");
        $this->command->info("      - Pendentes: {$vinculosPendentes}");
        $this->command->info("      - Recusados: {$vinculosRecusados}");

        $this->command->info("   📜 Histórico de Vínculos: " . HistoricoUsuarioProjeto::count() . " registros");

        // Salas e baias
        $totalSalas = Sala::count();
        $totalBaias = Baia::count();
        $this->command->info("   🏢 Salas: {$totalSalas} total");
        $this->command->info("      - Baias: {$totalBaias} total");

        // Horários
        $totalHorarios = Horario::count();
        $this->command->info("   ⏰ Horários: {$totalHorarios} total");
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
     * Cria os docentes principais com perfis completos
     */
    private function createDocentes(): void
    {
        // Garante que existe um banco para associar aos docentes
        $banco = Banco::firstOrCreate(
            ['codigo' => '001'],
            ['nome' => 'Banco do Brasil']
        );

        $docentes = [
            // Maxwell - Coordenador Principal
            [
                'email' => 'maxwell@computacao.ufcg.edu.br',
                'name' => 'Maxwell Guimarães de Oliveira',
                'cpf' => '12345678901',
                'rg' => '1234567',
                'uf_rg' => 'PB',
                'orgao_emissor_rg' => 'SSP',
                'telefone' => '(83) 99999-9999',
                'data_nascimento' => '1980-01-01',
                'genero' => Genero::MASCULINO->value,
                'cep' => '58429900',
                'endereco' => 'Rua dos Docentes',
                'numero' => '100',
                'bairro' => 'Universitário',
                'cidade' => 'Campina Grande',
                'uf' => 'PB',
                'banco_id' => $banco->id,
                'conta_bancaria' => '12345-6',
                'agencia' => '1234',
                'area_atuacao' => 'Inteligência Artificial, Aprendizado de Máquina, Ciência de Dados',
                'tecnologias' => 'Python, R, TensorFlow, PyTorch, Scikit-learn, Jupyter',
                'linkedin_url' => 'https://linkedin.com/in/maxwell-guimaraes',
                'github_url' => 'https://github.com/maxwell-oliveira',
                'curriculo_lattes_url' => 'http://lattes.cnpq.br/1234567890123456',
                'website_url' => 'https://sites.google.com/maxwell-oliveira',
            ],
            // Campelo - Coordenador
            [
                'email' => 'campelo@computacao.ufcg.edu.br',
                'name' => 'Cláudio Campelo',
                'cpf' => '12345678902',
                'rg' => '1234568',
                'uf_rg' => 'PB',
                'orgao_emissor_rg' => 'SSP',
                'telefone' => '(83) 88888-8888',
                'data_nascimento' => '1970-01-01',
                'genero' => Genero::MASCULINO->value,
                'cep' => '58429900',
                'endereco' => 'Rua dos Docentes',
                'numero' => '200',
                'bairro' => 'Universitário',
                'cidade' => 'Campina Grande',
                'uf' => 'PB',
                'banco_id' => $banco->id,
                'conta_bancaria' => '12345-7',
                'agencia' => '1234',
                'area_atuacao' => 'Engenharia de Software, Arquitetura de Software, Sistemas Distribuídos',
                'tecnologias' => 'Java, Spring Boot, Docker, Kubernetes, PostgreSQL, Redis',
                'linkedin_url' => 'https://linkedin.com/in/claudio-campelo',
                'github_url' => 'https://github.com/claudio-campelo',
                'curriculo_lattes_url' => 'http://lattes.cnpq.br/1234567890123457',
                'website_url' => 'https://sites.google.com/claudio-campelo',
            ],
            // Paulo Hernane - Colaborador
            [
                'email' => 'paulo.hernane.silva@ccc.ufcg.edu.br',
                'name' => 'Paulo Hernane Fontes e Silva',
                'cpf' => '12345678903',
                'rg' => '1234569',
                'uf_rg' => 'PB',
                'orgao_emissor_rg' => 'SSP',
                'telefone' => '(83) 77777-7777',
                'data_nascimento' => '1975-01-01',
                'genero' => Genero::MASCULINO->value,
                'cep' => '58429900',
                'endereco' => 'Rua dos Docentes',
                'numero' => '300',
                'bairro' => 'Universitário',
                'cidade' => 'Campina Grande',
                'uf' => 'PB',
                'banco_id' => $banco->id,
                'conta_bancaria' => '12345-8',
                'agencia' => '1234',
                'area_atuacao' => 'Computação Gráfica, Visualização de Dados, Interface Humano-Computador',
                'tecnologias' => 'JavaScript, React, D3.js, Three.js, OpenGL, WebGL',
                'linkedin_url' => 'https://linkedin.com/in/paulo-hernane',
            ],
        ];

        foreach ($docentes as $docenteData) {
            User::updateOrCreate(
                ['email' => $docenteData['email']],
                array_merge($docenteData, [
                    'password' => Hash::make('Ab@12312'),
                    'status_cadastro' => StatusCadastro::ACEITO,
                    'email_verified_at' => now(),
                ])
            );
        }
        $this->command->info("👨‍🏫 Docentes principais criados/atualizados.");
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

        // Distribuição:
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
        $tipos = TipoProjeto::cases();

        // Projetos ativos (em andamento)
        if ($ativos > 0) {
            Projeto::factory()->count($ativos)->create([
                'data_inicio' => fn() => now()->subMonths(rand(1, 12)),
                'data_termino' => fn() => now()->addMonths(rand(6, 24)),
                'tipo' => $tipos[array_rand($tipos)],
            ]);
        }

        // Projetos finalizados
        if ($finalizados > 0) {
            Projeto::factory()->count($finalizados)->create([
                'data_inicio' => fn() => now()->subMonths(rand(12, 36)),
                'data_termino' => fn() => now()->subMonths(rand(1, 11)),
                'tipo' => $tipos[array_rand($tipos)],
            ]);
        }

        // Projetos futuros
        if ($futuros > 0) {
            Projeto::factory()->count($futuros)->create([
                'data_inicio' => fn() => now()->addMonths(rand(1, 6)),
                'data_termino' => fn() => now()->addMonths(rand(7, 30)),
                'tipo' => $tipos[array_rand($tipos)],
            ]);
        }

        // Projetos cancelados/suspensos (com data de término no passado e curta duração)
        if ($cancelados > 0) {
            Projeto::factory()->count($cancelados)->create([
                'data_inicio' => fn() => now()->subMonths(rand(2, 6)),
                'data_termino' => fn() => now()->subMonths(rand(1, 2)),
                'tipo' => $tipos[array_rand($tipos)],
                'deleted_at' => now(), // Soft delete para simular cancelamento
            ]);
        }

        // Projeto específico para demonstração (sempre criar)
        Projeto::firstOrCreate(
            ['nome' => 'Sistema de Gestão Lacina'],
            [
                'descricao' => 'Projeto para gerenciar os recursos humanos e projetos do laboratório.',
                'data_inicio' => now()->subYear(),
                'data_termino' => now()->addYear(),
                'cliente' => 'Laboratório Lacina',
                'tipo' => TipoProjeto::PDI,
                'valor_total' => 50000,
                'meses_execucao' => 24,
            ]
        );
    }

    /**
     * Orquestra a criação de vínculos de teste.
     */
    private function createTestVinculos(): void
    {
        $this->command->info('🔗 Iniciando criação de vínculos de teste...');

        // Limpa tabelas para evitar inconsistências
        UsuarioProjeto::truncate();
        HistoricoUsuarioProjeto::truncate();

        $usuarios = User::where('status_cadastro', StatusCadastro::ACEITO)->whereNull('deleted_at')->get();
        $projetos = Projeto::all();

        if ($usuarios->count() < 3 || $projetos->isEmpty()) {
            $this->command->warn('⚠️  Não há usuários ou projetos suficientes para criar vínculos realistas. Abortando.');
            return;
        }

        // --- Separação de Atores ---
        $maxwell = $usuarios->firstWhere('email', 'maxwell@computacao.ufcg.edu.br');
        $campelo = $usuarios->firstWhere('email', 'campelo@computacao.ufcg.edu.br');
        $paulo = $usuarios->firstWhere('email', 'paulo.hernane.silva@ccc.ufcg.edu.br');
        $coordenadores = collect([$maxwell, $campelo])->filter();
        $outrosColaboradores = $usuarios->diff(collect([$maxwell, $campelo, $paulo])->filter());

        if ($coordenadores->count() < 2 || !$paulo) {
            $this->command->warn('⚠️  Coordenadores principais ou Paulo Hernane não encontrados. O seeder pode não funcionar como esperado.');
        }

        // --- PRIMEIRO: Garante que todos os projetos tenham coordenadores ---
        $this->command->info('👑 Garantindo que todos os projetos tenham coordenadores...');
        $this->ensureAllProjectsHaveCoordenadores($projetos, $coordenadores, $usuarios);

        // --- SEGUNDO: Geração de Histórico ---
        $this->command->info('⏳ Gerando histórico de 3 anos para todos os colaboradores...');
        foreach ($usuarios as $usuario) {
            $this->generateUserHistory($usuario, $projetos);
        }

        // --- TERCEIRO: Vínculos Especiais ---
        $this->createSpecialVinculos($outrosColaboradores, $projetos);

        // --- QUARTO: Validação final ---
        $this->validateProjectCoordinators($projetos);

        // --- Relatório Final ---
        $totalVinculos = UsuarioProjeto::count();
        $pendentes = UsuarioProjeto::where('status', StatusVinculoProjeto::PENDENTE)->count();
        $comTroca = UsuarioProjeto::where('trocar', true)->count();
        $historicoCount = HistoricoUsuarioProjeto::count();

        $this->command->info("✅ Concluído: {$totalVinculos} vínculos ativos e {$historicoCount} registros de histórico criados.");
        $this->command->info("   - {$pendentes} vínculos pendentes.");
        $this->command->info("   - {$comTroca} vínculos marcados para troca.");
    }

    /**
     * Gera um histórico de participação em projetos para um usuário nos últimos 3 anos.
     */
    private function generateUserHistory(User $user, Collection $allProjects): void
    {
        $currentDate = now()->subYears(3);
        $endDate = now();

        // Evita criar histórico para coordenadores principais (eles têm lógica especial)
        $coordenadoresPrincipais = ['maxwell@computacao.ufcg.edu.br', 'campelo@computacao.ufcg.edu.br'];
        if (in_array($user->email, $coordenadoresPrincipais)) {
            return;
        }

        // Paulo Hernane deve sempre ter pelo menos 2 projetos - não gerar histórico que reduza isso
        $isPauloHernane = $user->email === 'paulo.hernane.silva@ccc.ufcg.edu.br';

        while ($currentDate->lessThan($endDate)) {
            $activeVinculos = UsuarioProjeto::where('usuario_id', $user->id)
                ->where('status', StatusVinculoProjeto::APROVADO)
                ->whereNull('data_fim')
                ->get();

            // Chance de sair de um projeto (Paulo só sai se tiver mais de 2 projetos)
            $canLeaveProject = $isPauloHernane ? $activeVinculos->count() > 2 : $activeVinculos->isNotEmpty();
            if ($canLeaveProject && rand(1, 100) <= 15) { // 15% de chance de sair
                $vinculoParaSair = $activeVinculos->random();
                $vinculoParaSair->update(['data_fim' => $currentDate]);
                $this->moveToHistory($vinculoParaSair);
            }

            // Chance de entrar em um novo projeto (Paulo tem prioridade para manter pelo menos 2)
            $activeVinculosCount = UsuarioProjeto::where('usuario_id', $user->id)->whereNull('data_fim')->count();
            $maxProjects = $isPauloHernane ? 3 : 2;
            $entryChance = $isPauloHernane && $activeVinculosCount < 2 ? 80 : 10; // Paulo: 80% se tem menos de 2, outros: 10%

            if ($activeVinculosCount < $maxProjects && rand(1, 100) <= $entryChance) {
                $projetosDisponiveis = $allProjects->filter(function ($projeto) use ($currentDate, $user) {
                    return $projeto->data_inicio <= $currentDate
                        && $projeto->data_termino >= $currentDate
                        && !$user->projetos->contains('id', $projeto->id);
                });

                if ($projetosDisponiveis->isNotEmpty()) {
                    $projetoDisponivel = $projetosDisponiveis->random();
                    $this->createVinculo(
                        $user,
                        $projetoDisponivel,
                        StatusVinculoProjeto::APROVADO,
                        $currentDate,
                        null, // Fica em aberto
                        null,
                        null,
                        false
                    );
                }
            }
            $currentDate->addMonths(rand(1, 4));
        }
    }

    /**
     * Garante que todos os projetos tenham pelo menos um coordenador
     */
    private function ensureAllProjectsHaveCoordenadores(Collection $projetos, Collection $coordenadores, Collection $usuarios): void
    {
        foreach ($projetos as $projeto) {
            // Verifica se o projeto já tem coordenador ativo
            $temCoordenador = UsuarioProjeto::where('projeto_id', $projeto->id)
                ->where('funcao', Funcao::COORDENADOR)
                ->where('status', StatusVinculoProjeto::APROVADO)
                ->exists();

            if (!$temCoordenador) {
                // Atribuir um coordenador disponível
                $coordenadorDisponivel = $coordenadores->random();

                $this->createVinculo(
                    $coordenadorDisponivel,
                    $projeto,
                    StatusVinculoProjeto::APROVADO,
                    $projeto->data_inicio,
                    null,
                    TipoVinculo::COORDENADOR,
                    Funcao::COORDENADOR,
                    false
                );
            }
        }
    }

    /**
     * Valida se todos os projetos têm coordenadores
     */
    private function validateProjectCoordinators(Collection $projetos): void
    {
        $projetosSemCoordenador = 0;

        foreach ($projetos as $projeto) {
            $temCoordenador = UsuarioProjeto::where('projeto_id', $projeto->id)
                ->where('funcao', Funcao::COORDENADOR)
                ->where('status', StatusVinculoProjeto::APROVADO)
                ->exists();

            if (!$temCoordenador) {
                $projetosSemCoordenador++;
            }
        }

        if ($projetosSemCoordenador > 0) {
            $this->command->warn("⚠️  {$projetosSemCoordenador} projetos ainda sem coordenador.");
        }
    }

    /**
     * Cria vínculos pendentes e recusados para dar mais realismo.
     */
    private function createSpecialVinculos(Collection $usuarios, Collection $projetos): void
    {
        $projetosAtivos = $projetos->filter(fn($p) => $p->data_termino >= now());

        // Criar vínculos pendentes
        if ($usuarios->count() >= 5) {
            $usuariosParaPendente = $usuarios->random(min(5, $usuarios->count()));
            foreach ($usuariosParaPendente as $usuario) {
                if ($usuario->projetos()->whereNull('data_fim')->count() < 2) {
                    $projetosDisponiveis = $projetosAtivos->filter(function ($projeto) use ($usuario) {
                        return !$usuario->projetos->contains('id', $projeto->id);
                    });

                    if ($projetosDisponiveis->isNotEmpty()) {
                        $projetoDisponivel = $projetosDisponiveis->random();
                        $this->createVinculo(
                            $usuario,
                            $projetoDisponivel,
                            StatusVinculoProjeto::PENDENTE,
                            now(),
                            null,
                            TipoVinculo::COLABORADOR,
                            Funcao::DESENVOLVEDOR,
                            false
                        );
                    }
                }
            }
        }

        // Criar vínculos recusados no passado
        if ($usuarios->count() >= 3) {
            $usuariosParaRecusado = $usuarios->random(min(3, $usuarios->count()));
            foreach ($usuariosParaRecusado as $usuario) {
                if ($projetos->isNotEmpty()) {
                    $projeto = $projetos->random();
                    $dataPassado = now()->subMonths(rand(1, 6));

                    $this->createVinculo(
                        $usuario,
                        $projeto,
                        StatusVinculoProjeto::RECUSADO,
                        $dataPassado,
                        $dataPassado->copy()->addDays(1), // Recusado rapidamente
                        TipoVinculo::COLABORADOR,
                        Funcao::ALUNO,
                        false
                    );
                }
            }
        }
    }

    /**
     * Move um vínculo para a tabela de histórico e o deleta da tabela ativa.
     */
    private function moveToHistory(UsuarioProjeto $vinculo): void
    {
        if ($vinculo->data_fim === null) {
            $vinculo->data_fim = now();
            $vinculo->save();
        }

        HistoricoUsuarioProjeto::create($vinculo->getAttributes());
        $vinculo->delete();
    }

    /**
     * Cria salas e baias de teste
     */
    private function createTestSalasEBaias(): void
    {
        $this->command->info('Criando salas e baias de teste...');

        $salas = [
            [
                'nome' => 'Sala Principal',
                'descricao' => 'Sala principal do laboratório com estações de trabalho',
                'baias' => [
                    'Baia 01',
                    'Baia 02',
                    'Baia 03',
                    'Baia 04',
                    'Baia 05'
                ]
            ],
            [
                'nome' => 'Sala de Reuniões',
                'descricao' => 'Sala para reuniões e apresentações',
                'baias' => [
                    'Mesa Central',
                    'Estação Apresentação'
                ]
            ],
            [
                'nome' => 'Sala de Servidores',
                'descricao' => 'Sala com equipamentos de rede e servidores',
                'baias' => [
                    'Rack Principal',
                    'Estação Monitoramento'
                ]
            ]
        ];

        foreach ($salas as $salaData) {
            $sala = Sala::firstOrCreate(
                ['nome' => $salaData['nome']],
                [
                    'descricao' => $salaData['descricao'],
                    'ativa' => true
                ]
            );

            foreach ($salaData['baias'] as $baiaNome) {
                Baia::firstOrCreate(
                    ['nome' => $baiaNome, 'sala_id' => $sala->id],
                    [
                        'descricao' => "Baia {$baiaNome} na {$sala->nome}",
                        'ativa' => true
                    ]
                );
            }
        }

        $this->command->info('Salas e baias de teste criadas com sucesso!');
    }

    /**
     * Cria um vínculo entre usuário e projeto
     */
    private function createVinculo(
        User $usuario,
        Projeto $projeto,
        StatusVinculoProjeto $status,
        $dataInicio,
        $dataFim = null,
        TipoVinculo $tipoVinculo = null,
        Funcao $funcao = null,
        bool $trocar = false
    ): ?UsuarioProjeto {
        return UsuarioProjeto::create([
            'usuario_id' => $usuario->id,
            'projeto_id' => $projeto->id,
            'tipo_vinculo' => $tipoVinculo ?? TipoVinculo::COLABORADOR,
            'funcao' => $funcao ?? Funcao::ALUNO,
            'status' => $status,
            'carga_horaria' => rand(8, 20),
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim,
            'trocar' => $trocar,
        ]);
    }

    /**
     * Cria um usuário com dados fornecidos
     */
    private function createUser(array $data, StatusCadastro $status = StatusCadastro::ACEITO): User
    {
        return User::create(array_merge([
            'password' => Hash::make('Ab@12312'),
            'status_cadastro' => $status,
            'email_verified_at' => now(),
        ], $data));
    }

    /**
     * Cria um projeto com dados fornecidos
     */
    private function createProject(array $data): Projeto
    {
        return Projeto::create(array_merge([
            'valor_total' => rand(10000, 100000),
            'meses_execucao' => rand(6, 36),
            'campos_extras' => [],
        ], $data));
    }

    /**
     * Cria um vínculo projeto-usuário usando array de dados
     */
    private function createProjectLink(array $data): UsuarioProjeto
    {
        return UsuarioProjeto::create([
            'usuario_id' => $data['usuario_id'],
            'projeto_id' => $data['projeto_id'],
            'tipo_vinculo' => $data['tipo_vinculo'],
            'funcao' => $data['funcao'],
            'carga_horaria' => $data['carga_horaria'],
            'data_inicio' => $data['data_inicio'],
            'status' => $data['status'],
        ]);
    }

    /**
     * Cria horários de teste para usuários principais
     */
    private function createTestHorarios(User $paulo, User $maxwell, User $campelo): void
    {
        $this->command->info('Criando horários para Paulo Hernane Silva...');

        // Buscar vínculos ativos do Paulo
        $vinculosTCC = UsuarioProjeto::where('usuario_id', $paulo->id)
            ->whereHas('projeto', fn($q) => $q->where('nome', 'like', '%Sistema de Gerenciamento%'))
            ->first();

        $vinculosPDI = UsuarioProjeto::where('usuario_id', $paulo->id)
            ->whereHas('projeto', fn($q) => $q->where('nome', 'like', '%TS ETL%'))
            ->first();

        // Primeira baia para testes (criar se não existir)
        $baia = Baia::first();
        if (!$baia) {
            $sala = Sala::firstOrCreate(['nome' => 'Sala Principal'], [
                'descricao' => 'Sala principal do laboratório',
                'ativa' => true
            ]);
            $baia = Baia::create([
                'nome' => 'Baia 01',
                'descricao' => 'Primeira baia da sala principal',
                'ativa' => true,
                'sala_id' => $sala->id
            ]);
        }

        // Horários de Paulo Hernane - Projeto TCC (Segunda a Sexta, manhã)
        if ($vinculosTCC) {
            $diasSemana = ['SEGUNDA', 'TERCA', 'QUARTA', 'QUINTA', 'SEXTA'];
            foreach ($diasSemana as $dia) {
                // Manhã: 8h às 12h para projeto TCC
                for ($hora = 8; $hora <= 11; $hora++) {
                    Horario::firstOrCreate([
                        'usuario_id' => $paulo->id,
                        'dia_da_semana' => $dia,
                        'horario' => $hora,
                    ], [
                        'tipo' => TipoHorario::TRABALHO_PRESENCIAL,
                        'usuario_projeto_id' => $vinculosTCC->id,
                        'baia_id' => $baia->id,
                    ]);
                }
            }
        }

        // Horários de Paulo Hernane - Projeto PDI (Segunda, Quarta, Sexta - tarde)
        if ($vinculosPDI) {
            $diasPDI = ['SEGUNDA', 'QUARTA', 'SEXTA'];
            foreach ($diasPDI as $dia) {
                // Tarde: 14h às 18h para projeto PDI
                for ($hora = 14; $hora <= 17; $hora++) {
                    Horario::firstOrCreate([
                        'usuario_id' => $paulo->id,
                        'dia_da_semana' => $dia,
                        'horario' => $hora,
                    ], [
                        'tipo' => TipoHorario::TRABALHO_REMOTO,
                        'usuario_projeto_id' => $vinculosPDI->id,
                        'baia_id' => null, // Remoto não precisa de baia
                    ]);
                }
            }
        }

        // Horários de aula para Paulo (Terça e Quinta à tarde)
        $diasAula = ['TERCA', 'QUINTA'];
        foreach ($diasAula as $dia) {
            for ($hora = 14; $hora <= 17; $hora++) {
                Horario::firstOrCreate([
                    'usuario_id' => $paulo->id,
                    'dia_da_semana' => $dia,
                    'horario' => $hora,
                ], [
                    'tipo' => TipoHorario::EM_AULA,
                    'usuario_projeto_id' => null,
                    'baia_id' => null,
                ]);
            }
        }

        $this->command->info('✅ Horários criados para Paulo Hernane Silva');

        // Criar alguns horários básicos para Maxwell e Campelo
        $this->createBasicScheduleForUser($maxwell, 'Maxwell');
        $this->createBasicScheduleForUser($campelo, 'Campelo');

        // Criar horários para outros usuários ativos
        $outrosUsuarios = User::where('status_cadastro', StatusCadastro::ACEITO)
            ->whereNull('deleted_at')
            ->whereNotIn('email', [
                'paulo.hernane.silva@ccc.ufcg.edu.br',
                'maxwell@computacao.ufcg.edu.br',
                'campelo@computacao.ufcg.edu.br'
            ])
            ->limit(10) // Apenas 10 usuários para não sobrecarregar
            ->get();

        foreach ($outrosUsuarios as $usuario) {
            if (rand(1, 100) <= 60) { // 60% de chance de ter horários definidos
                $this->createRandomScheduleForUser($usuario);
            }
        }
    }

    /**
     * Cria horários básicos para coordenadores (Maxwell e Campelo)
     */
    private function createBasicScheduleForUser(User $user, string $nome): void
    {
        $this->command->info("Criando horários básicos para {$nome}...");

        $vinculosAtivos = UsuarioProjeto::where('usuario_id', $user->id)
            ->where('status', StatusVinculoProjeto::APROVADO)
            ->whereNull('data_fim')
            ->get();

        if ($vinculosAtivos->isEmpty()) {
            return;
        }

        $vinculoPrincipal = $vinculosAtivos->first();
        $diasSemana = ['SEGUNDA', 'TERCA', 'QUARTA', 'QUINTA', 'SEXTA'];

        foreach ($diasSemana as $dia) {
            // Horário de trabalho: 9h às 17h
            for ($hora = 9; $hora <= 16; $hora++) {
                // Pular horário de almoço (12h às 13h)
                if ($hora == 12) continue;

                // Coordenadores trabalham remoto para evitar conflitos de baia
                Horario::firstOrCreate([
                    'usuario_id' => $user->id,
                    'dia_da_semana' => $dia,
                    'horario' => $hora,
                ], [
                    'tipo' => TipoHorario::TRABALHO_REMOTO,
                    'usuario_projeto_id' => $vinculoPrincipal->id,
                    'baia_id' => null, // Remoto não usa baia
                ]);
            }
        }
    }

    /**
     * Cria horários aleatórios para um usuário
     */
    private function createRandomScheduleForUser(User $user): void
    {
        $vinculos = UsuarioProjeto::where('usuario_id', $user->id)
            ->where('status', StatusVinculoProjeto::APROVADO)
            ->whereNull('data_fim')
            ->get();

        if ($vinculos->isEmpty()) {
            return;
        }

        $diasSemana = ['SEGUNDA', 'TERCA', 'QUARTA', 'QUINTA', 'SEXTA'];
        $vinculo = $vinculos->random();

        foreach ($diasSemana as $dia) {
            // Chance de ter trabalho neste dia
            if (rand(1, 100) <= 70) { // 70% de chance
                $horasTrabalho = rand(2, 6); // 2 a 6 horas por dia
                $horaInicio = rand(8, 14); // Começar entre 8h e 14h

                for ($i = 0; $i < $horasTrabalho; $i++) {
                    $hora = $horaInicio + $i;
                    if ($hora > 17) break; // Não passar das 17h

                    $tipoTrabalho = rand(1, 100) <= 50 ? TipoHorario::TRABALHO_PRESENCIAL : TipoHorario::TRABALHO_REMOTO;
                    $baia = $tipoTrabalho === TipoHorario::TRABALHO_PRESENCIAL ? $this->findAvailableBaia($dia, $hora) : null;

                    Horario::firstOrCreate([
                        'usuario_id' => $user->id,
                        'dia_da_semana' => $dia,
                        'horario' => $hora,
                    ], [
                        'tipo' => $tipoTrabalho,
                        'usuario_projeto_id' => $vinculo->id,
                        'baia_id' => $baia?->id,
                    ]);
                }
            }
        }
    }

    /**
     * Encontra uma baia disponível para um usuário em um horário específico
     */
    private function findAvailableBaia(string $dia, int $hora): ?Baia
    {
        $baias = Baia::where('ativa', true)->get();

        if ($baias->isEmpty()) {
            // Criar uma baia se não existir nenhuma
            $sala = Sala::firstOrCreate(['nome' => 'Sala Principal'], [
                'descricao' => 'Sala principal do laboratório',
                'ativa' => true
            ]);

            return Baia::create([
                'nome' => 'Baia ' . (Baia::count() + 1),
                'descricao' => 'Baia automaticamente criada',
                'ativa' => true,
                'sala_id' => $sala->id
            ]);
        }

        // Buscar uma baia livre no horário específico
        foreach ($baias as $baia) {
            $horariosOcupados = Horario::where('baia_id', $baia->id)
                ->where('dia_da_semana', $dia)
                ->where('horario', $hora)
                ->exists();

            if (!$horariosOcupados) {
                return $baia;
            }
        }

        // Se todas estão ocupadas, criar uma nova baia
        $sala = Sala::first();
        return Baia::create([
            'nome' => 'Baia ' . (Baia::count() + 1),
            'descricao' => 'Baia criada para evitar conflitos',
            'ativa' => true,
            'sala_id' => $sala->id
        ]);
    }
}
