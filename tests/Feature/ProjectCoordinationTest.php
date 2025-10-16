<?php

use App\Enums\Funcao;
use App\Enums\StatusCadastro;
use App\Enums\StatusSolicitacaoTroca;
use App\Enums\StatusVinculoProjeto;
use App\Enums\TipoProjeto;
use App\Enums\TipoVinculo;
use App\Models\Projeto;
use App\Models\SolicitacaoTrocaProjeto;
use App\Models\User;
use App\Models\UsuarioProjeto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
    Notification::fake();

    // Create test users
    $this->coordenador = User::factory()->create([
        'email' => 'coordenador@test.com',
        'status_cadastro' => StatusCadastro::ACEITO,
    ]);

    $this->discente1 = User::factory()->create([
        'email' => 'discente1@test.com',
        'status_cadastro' => StatusCadastro::ACEITO,
    ]);

    $this->discente2 = User::factory()->create([
        'email' => 'discente2@test.com',
        'status_cadastro' => StatusCadastro::ACEITO,
    ]);

    $this->externo = User::factory()->create([
        'email' => 'externo@test.com',
        'status_cadastro' => StatusCadastro::ACEITO,
    ]);

    // Create test project
    $this->projeto = Projeto::factory()->create([
        'nome' => 'Projeto de Coordenação',
        'tipo' => TipoProjeto::PDI,
    ]);

    // Make coordenador a coordinator of the project
    UsuarioProjeto::factory()->create([
        'usuario_id' => $this->coordenador->id,
        'projeto_id' => $this->projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'funcao' => Funcao::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
        'carga_horaria' => 40,
        'data_inicio' => now(),
    ]);
});

describe('Project Coordination Dashboard', function () {
    it('shows coordinator dashboard with project overview', function () {
        // Add some team members
        UsuarioProjeto::factory()->create([
            'usuario_id' => $this->discente1->id,
            'projeto_id' => $this->projeto->id,
            'tipo_vinculo' => TipoVinculo::COLABORADOR,
            'funcao' => Funcao::DESENVOLVEDOR,
            'status' => StatusVinculoProjeto::APROVADO,
            'carga_horaria' => 20,
            'data_inicio' => now(),
        ]);

        $this->actingAs($this->coordenador);

        $response = $this->get(route('projetos.show', $this->projeto));

        $response->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Projetos/Show')
                    ->has('projeto')
                    ->has('usuarios') // Team members
            );
    });

    it('displays project statistics for coordinators', function () {
        // Create various team members with different statuses
        UsuarioProjeto::factory()->create([
            'usuario_id' => $this->discente1->id,
            'projeto_id' => $this->projeto->id,
            'status' => StatusVinculoProjeto::APROVADO,
            'carga_horaria' => 20,
        ]);

        UsuarioProjeto::factory()->create([
            'usuario_id' => $this->discente2->id,
            'projeto_id' => $this->projeto->id,
            'status' => StatusVinculoProjeto::RECUSADO,
            'carga_horaria' => 15,
        ]);

        $this->actingAs($this->coordenador);

        $response = $this->get(route('projetos.show', $this->projeto));

        $response->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->where('usuarios.total', 3) // Including coordinator
                    ->where('usuarios.APROVADOs', 2) // Coordinator + discente1
            );
    });

    it('shows pending project applications to coordinators', function () {
        // Create pending application
        UsuarioProjeto::factory()->create([
            'usuario_id' => $this->discente1->id,
            'projeto_id' => $this->projeto->id,
            'status' => StatusVinculoProjeto::PENDENTE,
            'tipo_vinculo' => TipoVinculo::COLABORADOR,
            'funcao' => Funcao::DESENVOLVEDOR,
            'carga_horaria' => 10,
        ]);

        $this->actingAs($this->coordenador);

        $response = $this->get(route('projetos.vinculos.index', $this->projeto));

        $response->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->has('solicitacoes')
                    ->where('solicitacoes.0.status', StatusVinculoProjeto::PENDENTE->value)
            );
    });
});

describe('Team Member Management', function () {
    it('allows coordinators to approve project applications', function () {
        $solicitacao = UsuarioProjeto::factory()->create([
            'usuario_id' => $this->discente1->id,
            'projeto_id' => $this->projeto->id,
            'status' => StatusVinculoProjeto::PENDENTE,
            'tipo_vinculo' => TipoVinculo::COLABORADOR,
            'funcao' => Funcao::DESENVOLVEDOR,
            'carga_horaria' => 20,
        ]);

        $this->actingAs($this->coordenador);

        $response = $this->patch(route('projetos.vinculos.aprovar', [
            'projeto' => $this->projeto,
            'vinculo' => $solicitacao,
        ]));

        $response->assertRedirect();

        $this->assertDatabaseHas('usuario_projeto', [
            'id' => $solicitacao->id,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);
    });

    it('allows coordinators to reject project applications', function () {
        $solicitacao = UsuarioProjeto::factory()->create([
            'usuario_id' => $this->discente1->id,
            'projeto_id' => $this->projeto->id,
            'status' => StatusVinculoProjeto::PENDENTE,
        ]);

        $this->actingAs($this->coordenador);

        $response = $this->patch(route('projetos.vinculos.rejeitar', [
            'projeto' => $this->projeto,
            'vinculo' => $solicitacao,
        ]), [
            'motivo_rejeicao' => 'Perfil não adequado para o projeto.',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('usuario_projeto', [
            'id' => $solicitacao->id,
            'status' => StatusVinculoProjeto::RECUSADO,
        ]);
    });

    it('allows coordinators to edit application details before approval', function () {
        $solicitacao = UsuarioProjeto::factory()->create([
            'usuario_id' => $this->discente1->id,
            'projeto_id' => $this->projeto->id,
            'status' => StatusVinculoProjeto::PENDENTE,
            'carga_horaria' => 10,
            'funcao' => Funcao::DESENVOLVEDOR,
        ]);

        $this->actingAs($this->coordenador);

        $response = $this->patch(route('projetos.vinculos.update', [
            'projeto' => $this->projeto,
            'vinculo' => $solicitacao,
        ]), [
            'carga_horaria' => 20,
            'funcao' => Funcao::TECNICO->value,
            'status' => StatusVinculoProjeto::APROVADO->value,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('usuario_projeto', [
            'id' => $solicitacao->id,
            'carga_horaria' => 20,
            'funcao' => Funcao::TECNICO,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);
    });

    it('allows coordinators to remove team members', function () {
        $vinculo = UsuarioProjeto::factory()->create([
            'usuario_id' => $this->discente1->id,
            'projeto_id' => $this->projeto->id,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        $this->actingAs($this->coordenador);

        $response = $this->delete(route('projetos.vinculos.destroy', [
            'projeto' => $this->projeto,
            'vinculo' => $vinculo,
        ]));

        $response->assertRedirect();

        $this->assertSoftDeleted('usuario_projeto', [
            'id' => $vinculo->id,
        ]);
    });

    it('prevents coordinators from removing themselves from projects', function () {
        $coordenadorVinculo = UsuarioProjeto::where([
            'usuario_id' => $this->coordenador->id,
            'projeto_id' => $this->projeto->id,
        ])->first();

        $this->actingAs($this->coordenador);

        $response = $this->delete(route('projetos.vinculos.destroy', [
            'projeto' => $this->projeto,
            'vinculo' => $coordenadorVinculo,
        ]));

        $response->assertStatus(403);

        $this->assertDatabaseHas('usuario_projeto', [
            'id' => $coordenadorVinculo->id,
            'deleted_at' => null,
        ]);
    });
});

describe('Project Switching Coordination', function () {
    it('allows coordinators to approve switch requests', function () {
        $novoProjetoDestino = Projeto::factory()->create();

        // Create coordinator for destination project
        UsuarioProjeto::factory()->create([
            'usuario_id' => $this->coordenador->id,
            'projeto_id' => $novoProjetoDestino->id,
            'tipo_vinculo' => TipoVinculo::COORDENADOR,
            'funcao' => Funcao::COORDENADOR,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        // Add discente to current project
        $vinculoAtual = UsuarioProjeto::factory()->create([
            'usuario_id' => $this->discente1->id,
            'projeto_id' => $this->projeto->id,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        // Create switch request
        $solicitacaoTroca = SolicitacaoTrocaProjeto::factory()->create([
            'usuario_id' => $this->discente1->id,
            'projeto_origem_id' => $this->projeto->id,
            'projeto_destino_id' => $novoProjetoDestino->id,
            'status' => StatusSolicitacaoTroca::PENDENTE,
        ]);

        $this->actingAs($this->coordenador);

        $response = $this->patch(route('solicitacoes-troca.aprovar-origem', $solicitacaoTroca));

        $response->assertRedirect();

        $this->assertDatabaseHas('solicitacoes_troca_projeto', [
            'id' => $solicitacaoTroca->id,
            'aprovacao_coordenador_origem' => true,
        ]);
    });

    it('notifies coordinators of pending switch requests', function () {
        $novoProjetoDestino = Projeto::factory()->create();

        // Add discente to current project
        UsuarioProjeto::factory()->create([
            'usuario_id' => $this->discente1->id,
            'projeto_id' => $this->projeto->id,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        // Create switch request
        SolicitacaoTrocaProjeto::factory()->create([
            'usuario_id' => $this->discente1->id,
            'projeto_origem_id' => $this->projeto->id,
            'projeto_destino_id' => $novoProjetoDestino->id,
            'status' => StatusSolicitacaoTroca::PENDENTE,
        ]);

        $this->actingAs($this->coordenador);

        $response = $this->get(route('dashboard'));

        $response->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->has('solicitacoesTrocaPendentes')
            );
    });

    it('allows coordinators to reject switch requests with reason', function () {
        $solicitacaoTroca = SolicitacaoTrocaProjeto::factory()->create([
            'usuario_id' => $this->discente1->id,
            'projeto_origem_id' => $this->projeto->id,
            'status' => StatusSolicitacaoTroca::PENDENTE,
        ]);

        $this->actingAs($this->coordenador);

        $response = $this->patch(route('solicitacoes-troca.rejeitar-origem', $solicitacaoTroca), [
            'motivo_rejeicao' => 'Usuário é essencial para o projeto atual.',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('solicitacoes_troca_projeto', [
            'id' => $solicitacaoTroca->id,
            'aprovacao_coordenador_origem' => false,
            'status' => StatusSolicitacaoTroca::REJEITADA,
        ]);
    });
});

describe('Coordinator Authorization', function () {
    it('restricts coordinator actions to their own projects', function () {
        $outroProjetoCoordnador = User::factory()->create();
        $outroProjeto = Projeto::factory()->create();

        UsuarioProjeto::factory()->create([
            'usuario_id' => $outroProjetoCoordnador->id,
            'projeto_id' => $outroProjeto->id,
            'tipo_vinculo' => TipoVinculo::COORDENADOR,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        $solicitacao = UsuarioProjeto::factory()->create([
            'usuario_id' => $this->discente1->id,
            'projeto_id' => $outroProjeto->id,
            'status' => StatusVinculoProjeto::PENDENTE,
        ]);

        $this->actingAs($this->coordenador);

        $response = $this->patch(route('projetos.vinculos.aprovar', [
            'projeto' => $outroProjeto,
            'vinculo' => $solicitacao,
        ]));

        $response->assertStatus(403);
    });

    it('prevents non-coordinators from managing team members', function () {
        $solicitacao = UsuarioProjeto::factory()->create([
            'usuario_id' => $this->discente2->id,
            'projeto_id' => $this->projeto->id,
            'status' => StatusVinculoProjeto::PENDENTE,
        ]);

        $this->actingAs($this->discente1);

        $response = $this->patch(route('projetos.vinculos.aprovar', [
            'projeto' => $this->projeto,
            'vinculo' => $solicitacao,
        ]));

        $response->assertStatus(403);
    });

    it('allows multiple coordinators per project', function () {
        $segundoCoordenador = User::factory()->create([
            'status_cadastro' => StatusCadastro::ACEITO,
        ]);

        UsuarioProjeto::factory()->create([
            'usuario_id' => $segundoCoordenador->id,
            'projeto_id' => $this->projeto->id,
            'tipo_vinculo' => TipoVinculo::COORDENADOR,
            'funcao' => Funcao::COORDENADOR,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        $solicitacao = UsuarioProjeto::factory()->create([
            'usuario_id' => $this->discente1->id,
            'projeto_id' => $this->projeto->id,
            'status' => StatusVinculoProjeto::PENDENTE,
        ]);

        // Both coordinators should be able to approve
        $this->actingAs($segundoCoordenador);

        $response = $this->patch(route('projetos.vinculos.aprovar', [
            'projeto' => $this->projeto,
            'vinculo' => $solicitacao,
        ]));

        $response->assertRedirect();

        $this->assertDatabaseHas('usuario_projeto', [
            'id' => $solicitacao->id,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);
    });
});

describe('Project Member Reporting', function () {
    it('generates team member reports for coordinators', function () {
        // Add team members with different roles
        UsuarioProjeto::factory()->create([
            'usuario_id' => $this->discente1->id,
            'projeto_id' => $this->projeto->id,
            'status' => StatusVinculoProjeto::APROVADO,
            'funcao' => Funcao::DESENVOLVEDOR,
            'carga_horaria' => 20,
            'data_inicio' => now()->subMonths(3),
        ]);

        UsuarioProjeto::factory()->create([
            'usuario_id' => $this->discente2->id,
            'projeto_id' => $this->projeto->id,
            'status' => StatusVinculoProjeto::APROVADO,
            'funcao' => Funcao::TECNICO,
            'carga_horaria' => 15,
            'data_inicio' => now()->subMonths(2),
        ]);

        $this->actingAs($this->coordenador);

        $response = $this->get(route('projetos.relatorio', $this->projeto));

        $response->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->has('projeto')
                    ->has('membros', 3) // Including coordinator
                    ->has('estatisticas')
            );
    });

    it('exports team member data to CSV', function () {
        UsuarioProjeto::factory()->create([
            'usuario_id' => $this->discente1->id,
            'projeto_id' => $this->projeto->id,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        $this->actingAs($this->coordenador);

        $response = $this->get(route('projetos.export', $this->projeto));

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    });

    it('tracks project workload distribution', function () {
        UsuarioProjeto::factory()->create([
            'usuario_id' => $this->discente1->id,
            'projeto_id' => $this->projeto->id,
            'status' => StatusVinculoProjeto::APROVADO,
            'carga_horaria' => 20,
        ]);

        UsuarioProjeto::factory()->create([
            'usuario_id' => $this->discente2->id,
            'projeto_id' => $this->projeto->id,
            'status' => StatusVinculoProjeto::APROVADO,
            'carga_horaria' => 15,
        ]);

        $this->actingAs($this->coordenador);

        $response = $this->get(route('projetos.show', $this->projeto));

        $response->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->where('estatisticas.carga_horaria_total', 75) // 40 + 20 + 15
                    ->where('estatisticas.media_carga_horaria', 25) // 75 / 3
            );
    });
});

describe('Notification Management', function () {
    it('sends notifications when applications are approved', function () {
        $solicitacao = UsuarioProjeto::factory()->create([
            'usuario_id' => $this->discente1->id,
            'projeto_id' => $this->projeto->id,
            'status' => StatusVinculoProjeto::PENDENTE,
        ]);

        $this->actingAs($this->coordenador);

        $this->patch(route('projetos.vinculos.aprovar', [
            'projeto' => $this->projeto,
            'vinculo' => $solicitacao,
        ]));

        Notification::assertSentTo(
            $this->discente1,
            \App\Notifications\SolicitacaoAprovada::class
        );
    });

    it('sends notifications when applications are rejected', function () {
        $solicitacao = UsuarioProjeto::factory()->create([
            'usuario_id' => $this->discente1->id,
            'projeto_id' => $this->projeto->id,
            'status' => StatusVinculoProjeto::PENDENTE,
        ]);

        $this->actingAs($this->coordenador);

        $this->patch(route('projetos.vinculos.rejeitar', [
            'projeto' => $this->projeto,
            'vinculo' => $solicitacao,
        ]), [
            'motivo_rejeicao' => 'Teste de rejeição',
        ]);

        Notification::assertSentTo(
            $this->discente1,
            \App\Notifications\SolicitacaoRejeitada::class
        );
    });

    it('notifies coordinators of new applications', function () {
        $this->actingAs($this->discente1);

        $this->post(route('projetos.vinculos.store', $this->projeto), [
            'tipo_vinculo' => TipoVinculo::COLABORADOR->value,
            'funcao' => Funcao::DESENVOLVEDOR->value,
            'carga_horaria' => 10,
            'justificativa' => 'Gostaria de contribuir com o projeto.',
        ]);

        Notification::assertSentTo(
            $this->coordenador,
            \App\Notifications\NovaSolicitacaoVinculo::class
        );
    });
});

describe('Edge Cases and Error Handling', function () {
    it('handles projects without coordinators gracefully', function () {
        $projetoSemCoordenador = Projeto::factory()->create();

        $this->actingAs($this->discente1);

        $response = $this->get(route('projetos.show', $projetoSemCoordenador));

        $response->assertOk();
        // Should not crash even without coordinators
    });

    it('prevents approval of already processed applications', function () {
        $solicitacao = UsuarioProjeto::factory()->create([
            'usuario_id' => $this->discente1->id,
            'projeto_id' => $this->projeto->id,
            'status' => StatusVinculoProjeto::APROVADO, // Already approved
        ]);

        $this->actingAs($this->coordenador);

        $response = $this->patch(route('projetos.vinculos.aprovar', [
            'projeto' => $this->projeto,
            'vinculo' => $solicitacao,
        ]));

        $response->assertStatus(422);
    });

    it('handles coordination of multiple projects simultaneously', function () {
        $projeto2 = Projeto::factory()->create();

        UsuarioProjeto::factory()->create([
            'usuario_id' => $this->coordenador->id,
            'projeto_id' => $projeto2->id,
            'tipo_vinculo' => TipoVinculo::COORDENADOR,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        $this->actingAs($this->coordenador);

        $response = $this->get(route('dashboard'));

        $response->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->has('projetosCoordenados', 2)
            );
    });
});
