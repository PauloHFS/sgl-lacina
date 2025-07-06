<?php

namespace Database\Seeders;

use App\Enums\TipoVinculo;
use App\Enums\Funcao;
use App\Enums\StatusVinculoProjeto;
use App\Enums\TipoProjeto;
use App\Enums\StatusCadastro;
use App\Enums\Genero;
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


        $this->createTestVinculos();

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

        $totalHorarios = Horario::count();
        $this->command->info("   ⏰ Horários: {$totalHorarios} total");
        $this->command->info("   📅 Horários semanais: " . Horario::where('tipo', 'semana')->count());

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

        // --- Geração de Histórico ---
        $this->command->info('⏳ Gerando histórico de 3 anos para todos os colaboradores...');
        foreach ($usuarios as $usuario) {
            $this->generateUserHistory($usuario, $projetos);
        }

        // --- Ajustes e Vínculos Especiais ---
        $this->adjustCoordenadoresProjetosAtivos($maxwell, $campelo, $projetos);
        $this->createSpecialVinculos($outrosColaboradores, $projetos);


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

        while ($currentDate->lessThan($endDate)) {
            $activeVinculos = UsuarioProjeto::where('usuario_id', $user->id)
                ->where('status', StatusVinculoProjeto::APROVADO)
                ->whereNull('data_fim')
                ->get();

            // Chance de sair de um projeto
            if ($activeVinculos->isNotEmpty() && rand(1, 100) <= 15) { // 15% de chance de sair
                $vinculoParaSair = $activeVinculos->random();
                $vinculoParaSair->update(['data_fim' => $currentDate]);
                $this->moveToHistory($vinculoParaSair);
            }

            // Chance de entrar em um novo projeto
            $activeVinculosCount = UsuarioProjeto::where('usuario_id', $user->id)->whereNull('data_fim')->count();
            if ($activeVinculosCount < 2 && rand(1, 100) <= 10) { // 10% de chance de entrar
                $projetosDisponiveis = $allProjects->where('data_inicio', '<=', $currentDate)
                    ->where('data_termino', '>=', $currentDate)
                    ->whereNotIn('id', $user->projetos->pluck('id'));

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
     * Garante que os coordenadores tenham 2 projetos ativos cada, 1 em comum.
     */
    private function adjustCoordenadoresProjetosAtivos(?User $maxwell, ?User $campelo, Collection $projetos): void
    {
        if (!$maxwell || !$campelo) {
            return;
        }

        $projetosAtivos = $projetos->filter(fn($p) => $p->data_termino >= now() && $p->data_inicio <= now());

        // Limpa todos os vínculos de coordenação ativos para recomeçar
        UsuarioProjeto::whereIn('usuario_id', [$maxwell->id, $campelo->id])
            ->where('funcao', Funcao::COORDENADOR)
            ->whereNull('data_fim')
            ->get()->each(fn($v) => $this->moveToHistory($v->fresh()));

        // 1. Projeto em comum
        $projetoComum = $projetosAtivos->pop();
        if ($projetoComum) {
            $this->createVinculo($maxwell, $projetoComum, StatusVinculoProjeto::APROVADO, now()->subMonth(), null, Funcao::COORDENADOR, TipoVinculo::COORDENADOR);
            $this->createVinculo($campelo, $projetoComum, StatusVinculoProjeto::APROVADO, now()->subMonth(), null, Funcao::COORDENADOR, TipoVinculo::COORDENADOR);
        }

        // 2. Projeto solo para Maxwell
        $projetoMaxwell = $projetosAtivos->pop();
        if ($projetoMaxwell) {
            $this->createVinculo($maxwell, $projetoMaxwell, StatusVinculoProjeto::APROVADO, now()->subMonth(), null, Funcao::COORDENADOR, TipoVinculo::COORDENADOR);
        }

        // 3. Projeto solo para Campelo
        $projetoCampelo = $projetosAtivos->pop();
        if ($projetoCampelo) {
            $this->createVinculo($campelo, $projetoCampelo, StatusVinculoProjeto::APROVADO, now()->subMonth(), null, Funcao::COORDENADOR, TipoVinculo::COORDENADOR);
        }
    }

    /**
     * Cria vínculos pendentes e recusados para dar mais realismo.
     */
    private function createSpecialVinculos(Collection $usuarios, Collection $projetos): void
    {
        $projetosAtivos = $projetos->where('data_termino', '>=', now());

        // Criar vínculos pendentes
        if ($usuarios->count() >= 5) {
            $usuariosParaPendente = $usuarios->random(5);
            foreach ($usuariosParaPendente as $usuario) {
                if ($usuario->projetos()->whereNull('data_fim')->count() < 2) {
                    $projetosDisponiveis = $projetosAtivos->whereNotIn('id', $usuario->projetos->pluck('id'));
                    if ($projetosDisponiveis->isNotEmpty()) {
                        $projeto = $projetosDisponiveis->random();
                        $this->createVinculo($usuario, $projeto, StatusVinculoProjeto::PENDENTE, now());
                    }
                }
            }
        }


        // Criar vínculos recusados no passado
        if ($usuarios->count() >= 3) {
            $usuariosParaRecusado = $usuarios->random(3);
            foreach ($usuariosParaRecusado as $usuario) {
                if ($projetos->isNotEmpty()) {
                    $projeto = $projetos->random();
                    $vinculo = $this->createVinculo($usuario, $projeto, StatusVinculoProjeto::RECUSADO, now()->subMonths(rand(1, 6)));
                    if ($vinculo) {
                        $vinculo->update(['data_fim' => $vinculo->data_inicio->addDays(rand(1, 5))]);
                        $this->moveToHistory($vinculo);
                    }
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
        }

        HistoricoUsuarioProjeto::create($vinculo->getAttributes());
        $vinculo->delete();
    }

    private function createTestSalasEBaias(): void
    {
        $this->command->info('Criando salas e baias de teste...');

        $salas = [
            ['nome' => 'Sala GP', 'baias' => 10],
            ['nome' => 'Sala Nobel', 'baias' => 10],
            ['nome' => 'Sala Mundo', 'baias' => 10],
        ];

        foreach ($salas as $salaData) {
            $sala = Sala::factory()->create([
                'nome' => $salaData['nome'],
                'descricao' => 'Sala de teste para desenvolvimento'
            ]);

            for ($i = 1; $i <= $salaData['baias']; $i++) {
                Baia::factory()->create([
                    'sala_id' => $sala->id,
                    'nome' => $sala->nome . ' - Baia ' . $i,
                ]);
            }
        }

        $this->command->info('Salas e baias de teste criadas com sucesso!');
    }

    /**
     * Cria um vínculo específico entre usuário e projeto
     */
    private function createVinculo(
        User $usuario,
        Projeto $projeto,
        StatusVinculoProjeto $status,
        $dataInicio,
        $dataFim = null,
        ?Funcao $funcao = null,
        ?TipoVinculo $tipoVinculo = null,
        bool $trocar = false
    ): ?UsuarioProjeto {
        // Evita vínculos duplicados (mesmo usuário, mesmo projeto, sem data de fim)
        $vinculoExistente = UsuarioProjeto::where('usuario_id', $usuario->id)
            ->where('projeto_id', $projeto->id)
            ->whereNull('data_fim')
            ->first();

        if ($vinculoExistente) {
            return null;
        }

        $funcoes = Funcao::cases();
        $tiposVinculo = TipoVinculo::cases();

        // Se não foi especificado explicitamente, ajusta a probabilidade de trocar baseado no status
        if (!$trocar && $status === StatusVinculoProjeto::PENDENTE) {
            $trocar = rand(1, 100) <= 35; // 35% chance de ser troca para pendentes
        } elseif (!$trocar && $status === StatusVinculoProjeto::APROVADO) {
            $trocar = rand(1, 100) <= 10; // 10% chance de querer trocar para aprovados
        }

        // Define função e tipo de vínculo padrão se não especificado
        $funcaoFinal = $funcao ?? $funcoes[array_rand($funcoes)];
        if ($funcaoFinal === Funcao::COORDENADOR) {
            $tipoVinculoFinal = TipoVinculo::COORDENADOR;
        } else {
            $tipoVinculoFinal = $tipoVinculo ?? $tiposVinculo[array_rand($tiposVinculo)];
        }


        return UsuarioProjeto::create([
            'usuario_id' => $usuario->id,
            'projeto_id' => $projeto->id,
            'status' => $status,
            'funcao' => $funcaoFinal,
            'tipo_vinculo' => $tipoVinculoFinal,
            'carga_horaria' => rand(10, 40),
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim,
            'trocar' => $trocar,
        ]);
    }

    private function createUser(array $data, StatusCadastro $status = StatusCadastro::ACEITO): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'email_verified_at' => now(),
            'password' => Hash::make('Ab@12312'),
            'status_cadastro' => $status,
            'cpf' => $data['cpf'],
            'data_nascimento' => $data['data_nascimento'],
            'telefone' => $data['telefone'],
            'rg' => $data['rg'],
            'uf_rg' => $data['uf_rg'] ?? 'PB',
            'orgao_emissor_rg' => $data['orgao_emissor_rg'] ?? 'SSP-PB',
            'conta_bancaria' => $data['conta_bancaria'],
        ]);
    }

    private function createProject(array $data): Projeto
    {
        return Projeto::create([
            'nome' => $data['nome'],
            'descricao' => $data['descricao'],
            'valor_total' => $data['valor_total'] ?? 50500430,
            'meses_execucao' => $data['meses_execucao'] ?? 12.3,
            'data_inicio' => $data['data_inicio'],
            'data_termino' => $data['data_termino'],
            'cliente' => $data['cliente'],
            'tipo' => $data['tipo'],
        ]);
    }

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
}
