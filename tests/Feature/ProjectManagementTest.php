<?php

use App\Models\User;
use App\Models\Projeto;
use App\Models\UsuarioProjeto;
use App\Enums\TipoProjeto;
use App\Enums\StatusCadastro;
use App\Enums\StatusVinculoProjeto;
use App\Enums\TipoVinculo;
use App\Enums\Funcao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');

    // Create test users
    $this->docente = User::factory()->create([
        'email' => 'docente@test.com',
        'status_cadastro' => StatusCadastro::ACEITO,
    ]);

    $this->discente = User::factory()->create([
        'email' => 'discente@test.com',
        'status_cadastro' => StatusCadastro::ACEITO,
    ]);

    $this->colaborador = User::factory()->create([
        'email' => 'colaborador@test.com',
        'status_cadastro' => StatusCadastro::ACEITO,
    ]);
});

describe('Project Creation', function () {
    it('allows authorized users to create projects', function () {
        $this->actingAs($this->docente);

        $projectData = [
            'nome' => 'Projeto de Teste',
            'descricao' => 'Descrição do projeto de teste',
            'data_inicio' => '2024-01-01',
            'data_termino' => '2024-12-31',
            'cliente' => 'Cliente Teste',
            'slack_url' => 'https://slack.com/test',
            'discord_url' => 'https://discord.com/test',
            'board_url' => 'https://trello.com/test',
            'git_url' => 'https://github.com/test/repo',
            'tipo' => TipoProjeto::PDI->value,
        ];

        $response = $this->post(route('projetos.store'), $projectData);

        $response->assertRedirect();
        $this->assertDatabaseHas('projetos', [
            'nome' => 'Projeto de Teste',
            'cliente' => 'Cliente Teste',
            'tipo' => TipoProjeto::PDI->value,
        ]);
    });

    it('validates required fields on project creation', function () {
        $this->actingAs($this->docente);

        $response = $this->post(route('projetos.store'), []);

        $response->assertSessionHasErrors(['nome', 'data_inicio', 'cliente', 'tipo']);
    });

    it('validates date format and logic on project creation', function () {
        $this->actingAs($this->docente);

        $projectData = [
            'nome' => 'Projeto Teste',
            'data_inicio' => '2024-12-31',
            'data_termino' => '2024-01-01', // End before start
            'cliente' => 'Cliente',
            'tipo' => TipoProjeto::PDI->value,
        ];

        $response = $this->post(route('projetos.store'), $projectData);

        $response->assertSessionHasErrors(['data_termino']);
    });

    it('validates URL formats on project creation', function () {
        $this->actingAs($this->docente);

        $projectData = [
            'nome' => 'Projeto Teste',
            'data_inicio' => '2024-01-01',
            'cliente' => 'Cliente',
            'tipo' => TipoProjeto::PDI->value,
            'slack_url' => 'invalid-url',
            'git_url' => 'not-a-url',
        ];

        $response = $this->post(route('projetos.store'), $projectData);

        $response->assertSessionHasErrors(['slack_url', 'git_url']);
    });

    it('validates project type enum on creation', function () {
        $this->actingAs($this->docente);

        $projectData = [
            'nome' => 'Projeto Teste',
            'data_inicio' => '2024-01-01',
            'cliente' => 'Cliente',
            'tipo' => 'TIPO_INVALIDO',
        ];

        $response = $this->post(route('projetos.store'), $projectData);

        $response->assertSessionHasErrors(['tipo']);
    });
});

describe('Project Listing and Viewing', function () {
    it('displays projects list for authorized users', function () {
        $projeto = Projeto::factory()->create();

        $this->actingAs($this->docente);

        $response = $this->get(route('projetos.index'));

        $response->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('Projetos/Index')
                    ->has('projetos.data')
            );
    });

    it('shows project details for authorized users', function () {
        $projeto = Projeto::factory()->create([
            'nome' => 'Projeto Específico',
        ]);

        $this->actingAs($this->docente);

        $response = $this->get(route('projetos.show', $projeto));

        $response->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('Projetos/Show')
                    ->where('projeto.nome', 'Projeto Específico')
            );
    });

    it('filters projects by search term', function () {
        Projeto::factory()->create(['nome' => 'Projeto Alpha']);
        Projeto::factory()->create(['nome' => 'Projeto Beta']);
        Projeto::factory()->create(['nome' => 'Sistema Gamma']);

        $this->actingAs($this->docente);

        $response = $this->get(route('projetos.index', ['search' => 'Projeto']));

        $response->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('Projetos/Index')
                    ->has('projetos.data', 2)
            );
    });

    it('filters projects by type', function () {
        Projeto::factory()->create(['tipo' => TipoProjeto::MESTRADO]);
        Projeto::factory()->create(['tipo' => TipoProjeto::MESTRADO]);
        Projeto::factory()->create(['tipo' => TipoProjeto::PDI]);

        $this->actingAs($this->docente);

        $response = $this->get(route('projetos.index', ['tipo' => TipoProjeto::MESTRADO->value]));

        $response->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('Projetos/Index')
                    ->has('projetos.data', 2)
            );
    });
});

describe('Project Updates', function () {
    it('allows project updates by authorized users', function () {
        $projeto = Projeto::factory()->create([
            'nome' => 'Nome Original',
            'cliente' => 'Cliente Original',
        ]);

        $this->actingAs($this->docente);

        $updateData = [
            'nome' => 'Nome Atualizado',
            'descricao' => 'Nova descrição',
            'data_inicio' => $projeto->data_inicio->format('Y-m-d'),
            'cliente' => 'Cliente Atualizado',
            'tipo' => $projeto->tipo->value,
        ];

        $response = $this->put(route('projetos.update', $projeto), $updateData);

        $response->assertRedirect();
        $this->assertDatabaseHas('projetos', [
            'id' => $projeto->id,
            'nome' => 'Nome Atualizado',
            'cliente' => 'Cliente Atualizado',
        ]);
    });

    it('validates update data properly', function () {
        $projeto = Projeto::factory()->create();

        $this->actingAs($this->docente);

        $response = $this->put(route('projetos.update', $projeto), [
            'nome' => '', // Invalid empty name
            'data_inicio' => 'invalid-date',
            'tipo' => 'INVALID_TYPE',
        ]);

        $response->assertSessionHasErrors(['nome', 'data_inicio', 'tipo']);
    });

    it('prevents updating deleted projects', function () {
        $projeto = Projeto::factory()->create();
        $projeto->delete();

        $this->actingAs($this->docente);

        $response = $this->put(route('projetos.update', $projeto), [
            'nome' => 'Tentativa de Update',
            'data_inicio' => '2024-01-01',
            'cliente' => 'Cliente',
            'tipo' => TipoProjeto::MESTRADO->value,
        ]);

        $response->assertNotFound();
    });
});

describe('Project Deletion', function () {
    it('allows soft deletion of projects', function () {
        $projeto = Projeto::factory()->create();

        $this->actingAs($this->docente);

        $response = $this->delete(route('projetos.destroy', $projeto));

        $response->assertRedirect();
        $this->assertSoftDeleted('projetos', ['id' => $projeto->id]);
    });

    it('handles deletion of projects with active users', function () {
        $projeto = Projeto::factory()->create();

        // Create active user-project relationship
        UsuarioProjeto::factory()->create([
            'projeto_id' => $projeto->id,
            'usuario_id' => $this->discente->id,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        $this->actingAs($this->docente);

        $response = $this->delete(route('projetos.destroy', $projeto));

        $response->assertRedirect();
        $this->assertSoftDeleted('projetos', ['id' => $projeto->id]);

        // Verify user-project relationships are also soft deleted
        $this->assertSoftDeleted('usuario_projeto', [
            'projeto_id' => $projeto->id,
            'usuario_id' => $this->discente->id,
        ]);
    });

    it('prevents deletion of already deleted projects', function () {
        $projeto = Projeto::factory()->create();
        $projeto->delete();

        $this->actingAs($this->docente);

        $response = $this->delete(route('projetos.destroy', $projeto));

        $response->assertNotFound();
    });
});

describe('Project Authorization', function () {
    it('denies access to unauthorized users', function () {
        $projeto = Projeto::factory()->create();

        // Test without authentication
        $response = $this->get(route('projetos.index'));
        $response->assertRedirect(route('login'));

        $response = $this->post(route('projetos.store'), []);
        $response->assertRedirect(route('login'));

        $response = $this->get(route('projetos.show', $projeto));
        $response->assertRedirect(route('login'));
    });

    it('allows access to authenticated users', function () {
        $projeto = Projeto::factory()->create();

        $this->actingAs($this->discente);

        $response = $this->get(route('projetos.index'));
        $response->assertOk();

        $response = $this->get(route('projetos.show', $projeto));
        $response->assertOk();
    });

    it('handles users with pending approval status', function () {
        $pendingUser = User::factory()->create([
            'status_cadastro' => StatusCadastro::PENDENTE,
        ]);

        $this->actingAs($pendingUser);

        $response = $this->get(route('projetos.index'));

        // Should allow viewing but may have different permissions
        $response->assertOk();
    });
});

describe('Project Search and Filtering', function () {
    it('searches projects by multiple criteria', function () {
        $projeto1 = Projeto::factory()->create([
            'nome' => 'Sistema de Gestão',
            'cliente' => 'Empresa A',
            'tipo' => TipoProjeto::MESTRADO,
        ]);

        $projeto2 = Projeto::factory()->create([
            'nome' => 'App Mobile',
            'cliente' => 'Empresa B',
            'tipo' => TipoProjeto::PDI,
        ]);

        $this->actingAs($this->docente);

        // Search by name
        $response = $this->get(route('projetos.index', ['search' => 'Sistema']));
        $response->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->has('projetos.data', 1)
            );

        // Search by client
        $response = $this->get(route('projetos.index', ['search' => 'Empresa A']));
        $response->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->has('projetos.data', 1)
            );
    });

    it('handles empty search results gracefully', function () {
        Projeto::factory()->count(3)->create();

        $this->actingAs($this->docente);

        $response = $this->get(route('projetos.index', ['search' => 'NonexistentProject']));

        $response->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->has('projetos.data', 0)
            );
    });

    it('filters by date range', function () {
        Projeto::factory()->create([
            'data_inicio' => '2024-01-01',
            'data_termino' => '2024-06-30',
        ]);

        Projeto::factory()->create([
            'data_inicio' => '2024-07-01',
            'data_termino' => '2024-12-31',
        ]);

        $this->actingAs($this->docente);

        $response = $this->get(route('projetos.index', [
            'data_inicio_min' => '2024-06-01',
        ]));

        $response->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->has('projetos.data', 2)
            );
    });
});

describe('Project Validation Edge Cases', function () {
    it('handles very long project names', function () {
        $this->actingAs($this->docente);

        $longName = str_repeat('A', 300); // Assuming 255 char limit

        $response = $this->post(route('projetos.store'), [
            'nome' => $longName,
            'data_inicio' => '2024-01-01',
            'cliente' => 'Cliente',
            'tipo' => TipoProjeto::MESTRADO->value,
        ]);

        $response->assertSessionHasErrors(['nome']);
    });

    it('validates special characters in URLs', function () {
        $this->actingAs($this->docente);

        $response = $this->post(route('projetos.store'), [
            'nome' => 'Projeto',
            'data_inicio' => '2024-01-01',
            'cliente' => 'Cliente',
            'tipo' => TipoProjeto::MESTRADO->value,
            'slack_url' => 'https://slack.com/test with spaces',
        ]);

        $response->assertSessionHasErrors(['slack_url']);
    });

    it('handles concurrent project creation', function () {
        $this->actingAs($this->docente);

        $projectData = [
            'nome' => 'Projeto Concorrente',
            'data_inicio' => '2024-01-01',
            'cliente' => 'Cliente',
            'tipo' => TipoProjeto::MESTRADO->value,
        ];

        // Simulate concurrent requests
        $response1 = $this->post(route('projetos.store'), $projectData);
        $response2 = $this->post(route('projetos.store'), $projectData);

        $response1->assertRedirect();
        $response2->assertRedirect();

        // Both should succeed as project names can be duplicate
        $this->assertDatabaseCount('projetos', 2);
    });
});

describe('Project Performance', function () {
    it('handles large number of projects efficiently', function () {
        Projeto::factory()->count(100)->create();

        $this->actingAs($this->docente);

        $startTime = microtime(true);
        $response = $this->get(route('projetos.index'));
        $endTime = microtime(true);

        $response->assertOk();

        // Should complete within reasonable time (2 seconds)
        expect($endTime - $startTime)->toBeLessThan(2.0);
    });

    it('paginates projects properly', function () {
        Projeto::factory()->count(50)->create();

        $this->actingAs($this->docente);

        $response = $this->get(route('projetos.index'));

        $response->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('Projetos/Index')
                    ->has('projetos.data')
                    ->has('projetos.links')
                    ->where('projetos.per_page', 15) // Default pagination
            );
    });
});
