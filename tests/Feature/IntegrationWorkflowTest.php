<?php

use App\Enums\Funcao;
use App\Enums\StatusCadastro;
use App\Enums\StatusSolicitacaoTroca;
use App\Enums\StatusVinculoProjeto;
use App\Enums\TipoProjeto;
use App\Enums\TipoVinculo;
use App\Models\Banco;
use App\Models\HistoricoUsuarioProjeto;
use App\Models\Projeto;
use App\Models\SolicitacaoTrocaProjeto;
use App\Models\User;
use App\Models\UsuarioProjeto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
    Notification::fake();
    Storage::fake('public');

    // Create test banks
    $this->banco = Banco::factory()->create([
        'codigo' => '001',
        'nome' => 'Banco do Brasil',
    ]);

    // Create test users
    $this->docente = User::factory()->create([
        'email' => 'docente@lacina.ufcg.edu.br',
        'status_cadastro' => StatusCadastro::ACEITO,
        'cpf' => '12345678901',
        'banco_id' => $this->banco->id,
        'conta_bancaria' => '12345-6',
        'agencia' => '1234',
    ]);

    $this->discente = User::factory()->create([
        'email' => 'discente@ccc.ufcg.edu.br',
        'status_cadastro' => StatusCadastro::ACEITO,
        'cpf' => '98765432109',
    ]);

    $this->colaboradorExterno = User::factory()->create([
        'email' => 'externo@empresa.com',
        'status_cadastro' => StatusCadastro::ACEITO,
        'cpf' => '11122233344',
    ]);

    // Create test projects
    $this->projetoPesquisa = Projeto::factory()->create([
        'nome' => 'Projeto de Pesquisa IA',
        'tipo' => TipoProjeto::PDI,
        'data_inicio' => now()->subMonths(6),
        'data_termino' => now()->addMonths(6),
    ]);

    $this->projetoExtensao = Projeto::factory()->create([
        'nome' => 'Sistema de Gestão Municipal',
        'tipo' => TipoProjeto::TCC,
        'data_inicio' => now()->subMonths(3),
        'data_termino' => now()->addMonths(9),
    ]);

    // Set up coordinator relationships
    UsuarioProjeto::factory()->create([
        'usuario_id' => $this->docente->id,
        'projeto_id' => $this->projetoPesquisa->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'funcao' => Funcao::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
        'carga_horaria' => 20,
        'data_inicio' => now()->subMonths(6),
    ]);

    UsuarioProjeto::factory()->create([
        'usuario_id' => $this->docente->id,
        'projeto_id' => $this->projetoExtensao->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'funcao' => Funcao::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
        'carga_horaria' => 20,
        'data_inicio' => now()->subMonths(3),
    ]);
});

describe('Complete User Registration Flow', function () {
    it('handles complete user registration and approval workflow', function () {
        // Step 1: User registers
        $userData = [
            'name' => 'João Silva',
            'email' => 'joao.silva@ccc.ufcg.edu.br',
            'password' => 'senha123',
            'password_confirmation' => 'senha123',
            'cpf' => '55544433322',
            'docente_responsavel_email' => $this->docente->email,
        ];

        $response = $this->post(route('register'), $userData);
        $response->assertRedirect();

        $novoUser = User::where('email', 'joao.silva@ccc.ufcg.edu.br')->first();
        $this->assertNotNull($novoUser);
        $this->assertEquals(StatusCadastro::PENDENTE, $novoUser->status_cadastro);

        // Step 2: Docente receives notification and approves
        Notification::assertSentTo(
            $this->docente,
            \App\Notifications\NovoUsuarioCadastrado::class
        );

        $this->actingAs($this->docente);

        $response = $this->patch(route('colaboradores.aprovar', $novoUser), [
            'status_cadastro' => StatusCadastro::ACEITO->value,
        ]);

        $response->assertRedirect();

        $novoUser->refresh();
        $this->assertEquals(StatusCadastro::ACEITO, $novoUser->status_cadastro);

        // Step 3: User completes profile
        $this->actingAs($novoUser);

        $profileData = [
            'telefone' => '83999887766',
            'banco_id' => $this->banco->id,
            'conta_bancaria' => '98765-4',
            'agencia' => '9876',
            'rg' => '1234567',
            'uf_rg' => 'PB',
            'orgao_emissor_rg' => 'SSP',
            'linkedin_url' => 'https://linkedin.com/in/joaosilva',
            'github_url' => 'https://github.com/joaosilva',
        ];

        $response = $this->patch(route('profile.update'), $profileData);
        $response->assertRedirect();

        $novoUser->refresh();
        $this->assertEquals('83999887766', $novoUser->telefone);
        $this->assertEquals($this->banco->id, $novoUser->banco_id);

        // Step 4: User applies to project
        $applicationData = [
            'tipo_vinculo' => TipoVinculo::COLABORADOR->value,
            'funcao' => Funcao::DESENVOLVEDOR->value,
            'carga_horaria' => 20,
            'justificativa' => 'Tenho experiência em desenvolvimento web e gostaria de contribuir.',
        ];

        $response = $this->post(route('projetos.vinculos.store', $this->projetoPesquisa), $applicationData);
        $response->assertRedirect();

        $vinculo = UsuarioProjeto::where([
            'usuario_id' => $novoUser->id,
            'projeto_id' => $this->projetoPesquisa->id,
        ])->first();

        $this->assertNotNull($vinculo);
        $this->assertEquals(StatusVinculoProjeto::PENDENTE, $vinculo->status);

        // Step 5: Coordinator approves application
        $this->actingAs($this->docente);

        $response = $this->patch(route('projetos.vinculos.aprovar', [
            'projeto' => $this->projetoPesquisa,
            'vinculo' => $vinculo,
        ]));

        $response->assertRedirect();

        $vinculo->refresh();
        $this->assertEquals(StatusVinculoProjeto::APROVADO, $vinculo->status);

        // Verify historical record was created
        $this->assertDatabaseHas('historico_usuario_projeto', [
            'usuario_id' => $novoUser->id,
            'projeto_id' => $this->projetoPesquisa->id,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);
    });
});

describe('Complete Project Switching Workflow', function () {
    it('handles complete project switching with dual approval', function () {
        // Setup: Add discente to research project
        $vinculoOrigem = UsuarioProjeto::factory()->create([
            'usuario_id' => $this->discente->id,
            'projeto_id' => $this->projetoPesquisa->id,
            'status' => StatusVinculoProjeto::APROVADO,
            'tipo_vinculo' => TipoVinculo::COLABORADOR,
            'funcao' => Funcao::DESENVOLVEDOR,
            'carga_horaria' => 20,
            'data_inicio' => now()->subMonths(3),
        ]);

        // Create second coordinator for destination project
        $coordenadorDestino = User::factory()->create([
            'email' => 'coord.destino@lacina.ufcg.edu.br',
            'status_cadastro' => StatusCadastro::ACEITO,
        ]);

        UsuarioProjeto::factory()->create([
            'usuario_id' => $coordenadorDestino->id,
            'projeto_id' => $this->projetoExtensao->id,
            'tipo_vinculo' => TipoVinculo::COORDENADOR,
            'funcao' => Funcao::COORDENADOR,
            'status' => StatusVinculoProjeto::APROVADO,
            'carga_horaria' => 20,
        ]);

        // Step 1: Discente requests project switch
        $this->actingAs($this->discente);

        $switchData = [
            'projeto_destino_id' => $this->projetoExtensao->id,
            'motivo' => 'Gostaria de trabalhar com desenvolvimento de sistemas municipais.',
            'nova_funcao' => Funcao::DESENVOLVEDOR->value,
            'nova_carga_horaria' => 25,
        ];

        $response = $this->post(route('solicitacoes-troca.store'), $switchData);
        $response->assertRedirect();

        $solicitacaoTroca = SolicitacaoTrocaProjeto::where([
            'usuario_id' => $this->discente->id,
            'projeto_origem_id' => $this->projetoPesquisa->id,
            'projeto_destino_id' => $this->projetoExtensao->id,
        ])->first();

        $this->assertNotNull($solicitacaoTroca);
        $this->assertEquals(StatusSolicitacaoTroca::PENDENTE, $solicitacaoTroca->status);

        // Step 2: Origin coordinator approves
        $this->actingAs($this->docente);

        $response = $this->patch(route('solicitacoes-troca.aprovar-origem', $solicitacaoTroca));
        $response->assertRedirect();

        $solicitacaoTroca->refresh();
        $this->assertTrue($solicitacaoTroca->aprovacao_coordenador_origem);
        $this->assertEquals(StatusSolicitacaoTroca::AGUARDANDO_DESTINO, $solicitacaoTroca->status);

        // Step 3: Destination coordinator approves
        $this->actingAs($coordenadorDestino);

        $response = $this->patch(route('solicitacoes-troca.aprovar-destino', $solicitacaoTroca));
        $response->assertRedirect();

        $solicitacaoTroca->refresh();
        $this->assertTrue($solicitacaoTroca->aprovacao_coordenador_destino);
        $this->assertEquals(StatusSolicitacaoTroca::APROVADA, $solicitacaoTroca->status);

        // Step 4: Verify project switch was executed
        $vinculoOrigem->refresh();
        $this->assertNotNull($vinculoOrigem->deleted_at);
        $this->assertNotNull($vinculoOrigem->data_fim);

        $novoVinculo = UsuarioProjeto::where([
            'usuario_id' => $this->discente->id,
            'projeto_id' => $this->projetoExtensao->id,
            'status' => StatusVinculoProjeto::APROVADO,
        ])->first();

        $this->assertNotNull($novoVinculo);
        $this->assertEquals(25, $novoVinculo->carga_horaria);

        // Step 5: Verify historical records
        $this->assertDatabaseHas('historico_usuario_projeto', [
            'usuario_id' => $this->discente->id,
            'projeto_id' => $this->projetoPesquisa->id,
            'status' => StatusVinculoProjeto::RECUSADO,
        ]);

        $this->assertDatabaseHas('historico_usuario_projeto', [
            'usuario_id' => $this->discente->id,
            'projeto_id' => $this->projetoExtensao->id,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);
    });

    it('handles project switch rejection at any stage', function () {
        // Setup similar to above
        $vinculoOrigem = UsuarioProjeto::factory()->create([
            'usuario_id' => $this->discente->id,
            'projeto_id' => $this->projetoPesquisa->id,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        $this->actingAs($this->discente);

        $switchData = [
            'projeto_destino_id' => $this->projetoExtensao->id,
            'motivo' => 'Quero mudar de área.',
        ];

        $this->post(route('solicitacoes-troca.store'), $switchData);

        $solicitacaoTroca = SolicitacaoTrocaProjeto::where([
            'usuario_id' => $this->discente->id,
        ])->first();

        // Origin coordinator rejects
        $this->actingAs($this->docente);

        $response = $this->patch(route('solicitacoes-troca.rejeitar-origem', $solicitacaoTroca), [
            'motivo_rejeicao' => 'Usuário é essencial para o projeto atual.',
        ]);

        $response->assertRedirect();

        $solicitacaoTroca->refresh();
        $this->assertFalse($solicitacaoTroca->aprovacao_coordenador_origem);
        $this->assertEquals(StatusSolicitacaoTroca::REJEITADA, $solicitacaoTroca->status);

        // Verify original relationship remains active
        $vinculoOrigem->refresh();
        $this->assertNull($vinculoOrigem->deleted_at);
        $this->assertEquals(StatusVinculoProjeto::APROVADO, $vinculoOrigem->status);
    });
});

describe('Comprehensive Report Generation Workflow', function () {
    it('generates complete user participation history report', function () {
        // Create complex participation history
        $vinculo1 = UsuarioProjeto::factory()->create([
            'usuario_id' => $this->discente->id,
            'projeto_id' => $this->projetoPesquisa->id,
            'status' => StatusVinculoProjeto::RECUSADO,
            'data_inicio' => now()->subMonths(12),
            'data_fim' => now()->subMonths(6),
            'carga_horaria' => 15,
        ]);

        $vinculo2 = UsuarioProjeto::factory()->create([
            'usuario_id' => $this->discente->id,
            'projeto_id' => $this->projetoExtensao->id,
            'status' => StatusVinculoProjeto::APROVADO,
            'data_inicio' => now()->subMonths(3),
            'carga_horaria' => 20,
        ]);

        // Create historical records
        HistoricoUsuarioProjeto::create([
            'usuario_id' => $this->discente->id,
            'projeto_id' => $this->projetoPesquisa->id,
            'tipo_vinculo' => TipoVinculo::COLABORADOR,
            'funcao' => Funcao::DESENVOLVEDOR,
            'status' => StatusVinculoProjeto::APROVADO,
            'carga_horaria' => 15,
            'data_inicio' => now()->subMonths(12),
            'data_fim' => now()->subMonths(6),
        ]);

        HistoricoUsuarioProjeto::create([
            'usuario_id' => $this->discente->id,
            'projeto_id' => $this->projetoExtensao->id,
            'tipo_vinculo' => TipoVinculo::COLABORADOR,
            'funcao' => Funcao::DESENVOLVEDOR,
            'status' => StatusVinculoProjeto::APROVADO,
            'carga_horaria' => 20,
            'data_inicio' => now()->subMonths(3),
        ]);

        $this->actingAs($this->discente);

        // Test report generation
        $response = $this->post(route('relatorio.gerar'), [
            'email' => $this->discente->email,
        ]);

        $response->assertOk();

        // Verify email was sent with PDF
        Mail::assertSent(\App\Mail\RelatorioParticipacao::class, function ($mail) {
            return $mail->hasTo($this->discente->email) &&
                count($mail->attachments) > 0;
        });
    });

    it('generates coordinator summary reports', function () {
        // Add multiple team members to projects
        UsuarioProjeto::factory()->count(3)->create([
            'projeto_id' => $this->projetoPesquisa->id,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        UsuarioProjeto::factory()->count(2)->create([
            'projeto_id' => $this->projetoExtensao->id,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        $this->actingAs($this->docente);

        $response = $this->get(route('relatorio.coordenador'));

        $response->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->has('projetos', 2)
                    ->has('estatisticas')
                    ->where('estatisticas.total_colaboradores', 7) // 3 + 2 + 2 coordinators
            );
    });
});

describe('Multi-User Concurrent Operations', function () {
    it('handles concurrent project applications', function () {
        $discente2 = User::factory()->create([
            'status_cadastro' => StatusCadastro::ACEITO,
        ]);

        $applicationData = [
            'tipo_vinculo' => TipoVinculo::COLABORADOR->value,
            'funcao' => Funcao::DESENVOLVEDOR->value,
            'carga_horaria' => 10,
            'justificativa' => 'Interesse em participar.',
        ];

        // Simulate concurrent applications
        $this->actingAs($this->discente);
        $response1 = $this->post(route('projetos.vinculos.store', $this->projetoPesquisa), $applicationData);

        $this->actingAs($discente2);
        $response2 = $this->post(route('projetos.vinculos.store', $this->projetoPesquisa), $applicationData);

        $response1->assertRedirect();
        $response2->assertRedirect();

        // Both applications should be created
        $this->assertDatabaseCount('usuario_projeto', 4); // 2 coordinators + 2 applications
    });

    it('handles concurrent coordinator approvals', function () {
        $coord2 = User::factory()->create([
            'status_cadastro' => StatusCadastro::ACEITO,
        ]);

        // Add second coordinator
        UsuarioProjeto::factory()->create([
            'usuario_id' => $coord2->id,
            'projeto_id' => $this->projetoPesquisa->id,
            'tipo_vinculo' => TipoVinculo::COORDENADOR,
            'funcao' => Funcao::COORDENADOR,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        $solicitacao = UsuarioProjeto::factory()->create([
            'usuario_id' => $this->discente->id,
            'projeto_id' => $this->projetoPesquisa->id,
            'status' => StatusVinculoProjeto::PENDENTE,
        ]);

        // Simulate concurrent approvals
        $this->actingAs($this->docente);
        $response1 = $this->patch(route('projetos.vinculos.aprovar', [
            'projeto' => $this->projetoPesquisa,
            'vinculo' => $solicitacao,
        ]));

        $this->actingAs($coord2);
        $response2 = $this->patch(route('projetos.vinculos.aprovar', [
            'projeto' => $this->projetoPesquisa,
            'vinculo' => $solicitacao,
        ]));

        // First should succeed, second should fail gracefully
        $response1->assertRedirect();
        $response2->assertStatus(422); // Already processed
    });
});

describe('Complex Data Relationships', function () {
    it('maintains data integrity across cascaded operations', function () {
        // Create complex relationships
        $vinculo = UsuarioProjeto::factory()->create([
            'usuario_id' => $this->discente->id,
            'projeto_id' => $this->projetoPesquisa->id,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        $solicitacaoTroca = SolicitacaoTrocaProjeto::factory()->create([
            'usuario_id' => $this->discente->id,
            'projeto_origem_id' => $this->projetoPesquisa->id,
            'projeto_destino_id' => $this->projetoExtensao->id,
        ]);

        // Soft delete user
        $this->discente->delete();

        // Verify all related records are properly handled
        $this->assertSoftDeleted('users', ['id' => $this->discente->id]);
        $this->assertSoftDeleted('usuario_projeto', ['id' => $vinculo->id]);
        $this->assertSoftDeleted('solicitacoes_troca_projeto', ['id' => $solicitacaoTroca->id]);

        // Verify historical records remain
        $this->assertDatabaseHas('historico_usuario_projeto', [
            'usuario_id' => $this->discente->id,
            'projeto_id' => $this->projetoPesquisa->id,
        ]);
    });

    it('handles project deletion with active relationships', function () {
        // Add active users to project
        UsuarioProjeto::factory()->count(3)->create([
            'projeto_id' => $this->projetoPesquisa->id,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        // Create pending switch requests
        SolicitacaoTrocaProjeto::factory()->create([
            'projeto_origem_id' => $this->projetoPesquisa->id,
            'projeto_destino_id' => $this->projetoExtensao->id,
        ]);

        $this->actingAs($this->docente);

        // Delete project
        $response = $this->delete(route('projetos.destroy', $this->projetoPesquisa));
        $response->assertRedirect();

        // Verify cascading soft deletes
        $this->assertSoftDeleted('projetos', ['id' => $this->projetoPesquisa->id]);

        $vinculos = UsuarioProjeto::withTrashed()
            ->where('projeto_id', $this->projetoPesquisa->id)
            ->get();

        foreach ($vinculos as $vinculo) {
            $this->assertNotNull($vinculo->deleted_at);
        }
    });
});

describe('Performance Under Load', function () {
    it('handles large dataset operations efficiently', function () {
        // Create large dataset
        $users = User::factory()->count(50)->create([
            'status_cadastro' => StatusCadastro::ACEITO,
        ]);

        $projects = Projeto::factory()->count(20)->create();

        // Create many relationships
        foreach ($users as $user) {
            UsuarioProjeto::factory()->create([
                'usuario_id' => $user->id,
                'projeto_id' => $projects->random()->id,
                'status' => StatusVinculoProjeto::APROVADO,
            ]);
        }

        $this->actingAs($this->docente);

        $startTime = microtime(true);
        $response = $this->get(route('dashboard'));
        $endTime = microtime(true);

        $response->assertOk();

        // Should complete within reasonable time
        expect($endTime - $startTime)->toBeLessThan(3.0);
    });

    it('optimizes database queries in complex scenarios', function () {
        // Create test data
        UsuarioProjeto::factory()->count(10)->create([
            'projeto_id' => $this->projetoPesquisa->id,
        ]);

        $this->actingAs($this->docente);

        // Monitor query count
        DB::enableQueryLog();

        $response = $this->get(route('projetos.show', $this->projetoPesquisa));

        $queries = DB::getQueryLog();

        $response->assertOk();

        // Should not have excessive queries (N+1 problem)
        expect(count($queries))->toBeLessThan(15);
    });
});
