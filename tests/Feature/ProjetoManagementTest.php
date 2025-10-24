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

describe('Criação de Projetos', function () {
    it('permite usuários autorizados criarem projetos', function () {
        $this->actingAs($this->docente);

        $dadosProjeto = [
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

        $response = $this->post(route('projetos.store'), $dadosProjeto);

        $response->assertRedirect();
        $this->assertDatabaseHas('projetos', [
            'nome' => 'Projeto de Teste',
            'cliente' => 'Cliente Teste',
            'tipo' => TipoProjeto::PDI->value,
        ]);
    });

    it('coordenador é automaticamente vinculado ao projeto criado', function () {
        $this->actingAs($this->docente);

        $dadosProjeto = [
            'nome' => 'Projeto Teste',
            'descricao' => 'Descrição do projeto teste',
            'data_inicio' => '2024-01-01',
            'data_termino' => '2024-12-31',
            'cliente' => 'Cliente Teste',
            'tipo' => TipoProjeto::PDI->value,
        ];

        $response = $this->post(route('projetos.store'), $dadosProjeto);

        $response->assertRedirect();

        // Verificar se o coordenador foi automaticamente vinculado ao projeto
        $projeto = Projeto::where('nome', 'Projeto Teste')->first();
        $this->assertDatabaseHas('usuario_projeto', [
            'usuario_id' => $this->docente->id,
            'projeto_id' => $projeto->id,
            'tipo_vinculo' => TipoVinculo::COORDENADOR->value,
            'status' => StatusVinculoProjeto::APROVADO->value,
        ]);
    });

    it('valida campos obrigatórios na criação de projeto', function () {
        $this->actingAs($this->docente);

        $response = $this->post(route('projetos.store'), []);

        $response->assertSessionHasErrors(['nome', 'data_inicio', 'cliente', 'tipo']);
    });

    it('valida formato e lógica de datas na criação de projeto', function () {
        $this->actingAs($this->docente);

        $dadosProjeto = [
            'nome' => 'Projeto Teste',
            'data_inicio' => '2024-12-31',
            'data_termino' => '2024-01-01', // Data anterior ao início
            'cliente' => 'Cliente',
            'tipo' => TipoProjeto::PDI->value,
        ];

        $response = $this->post(route('projetos.store'), $dadosProjeto);

        $response->assertSessionHasErrors(['data_termino']);
    });

    it('valida formato de URLs na criação de projeto', function () {
        $this->actingAs($this->docente);

        $dadosProjeto = [
            'nome' => 'Projeto Teste',
            'data_inicio' => '2024-01-01',
            'cliente' => 'Cliente',
            'tipo' => TipoProjeto::PDI->value,
            'slack_url' => 'invalid-url',
            'git_url' => 'not-a-url',
        ];

        $response = $this->post(route('projetos.store'), $dadosProjeto);

        $response->assertSessionHasErrors(['slack_url', 'git_url']);
    });

    it('valida enum de tipo de projeto na criação', function () {
        $this->actingAs($this->docente);

        $dadosProjeto = [
            'nome' => 'Projeto Teste',
            'data_inicio' => '2024-01-01',
            'cliente' => 'Cliente',
            'tipo' => 'TIPO_INVALIDO',
        ];

        $response = $this->post(route('projetos.store'), $dadosProjeto);

        $response->assertSessionHasErrors(['tipo']);
    });
});

describe('Listagem e Visualização de Projetos', function () {
    it('exibe lista de projetos para usuários autorizados', function () {
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

    it('coordenador pode visualizar projetos que coordena', function () {
        $projeto = Projeto::factory()->create();

        UsuarioProjeto::factory()->create([
            'usuario_id' => $this->docente->id,
            'projeto_id' => $projeto->id,
            'tipo_vinculo' => TipoVinculo::COORDENADOR,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        $response = $this->actingAs($this->docente)->get(route('projetos.index', ['tab' => 'coordenador']));

        $response->assertStatus(200);
        $response->assertInertia(
            fn($page) =>
            $page->component('Projetos/Index')
                ->has('projetos')
        );
    });

    it('colaborador pode visualizar projetos que participa', function () {
        $projeto = Projeto::factory()->create();

        UsuarioProjeto::factory()->create([
            'usuario_id' => $this->colaborador->id,
            'projeto_id' => $projeto->id,
            'tipo_vinculo' => TipoVinculo::COLABORADOR,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        $response = $this->actingAs($this->colaborador)->get(route('projetos.index', ['tab' => 'colaborador']));

        $response->assertStatus(200);
        $response->assertInertia(
            fn($page) =>
            $page->component('Projetos/Index')
        );
    });

    it('mostra detalhes do projeto para usuários autorizados', function () {
        $projeto = Projeto::factory()->create([
            'nome' => 'Projeto Específico',
        ]);

        UsuarioProjeto::factory()->create([
            'usuario_id' => $this->docente->id,
            'projeto_id' => $projeto->id,
            'tipo_vinculo' => TipoVinculo::COORDENADOR,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        $response = $this->actingAs($this->docente)->get(route('projetos.show', $projeto));

        $response->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('Projetos/Show')
                    ->where('projeto.nome', 'Projeto Específico')
            );
    });

    it('usuário sem vínculo não pode visualizar detalhes do projeto', function () {
        $projeto = Projeto::factory()->create();

        $response = $this->actingAs($this->docente)->get(route('projetos.show', $projeto));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    });

    it('coordenador pode visualizar participantes do projeto', function () {
        $projeto = Projeto::factory()->create();

        // Coordenador do projeto
        UsuarioProjeto::factory()->create([
            'usuario_id' => $this->docente->id,
            'projeto_id' => $projeto->id,
            'tipo_vinculo' => TipoVinculo::COORDENADOR,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        // Colaborador aprovado
        UsuarioProjeto::factory()->create([
            'usuario_id' => $this->colaborador->id,
            'projeto_id' => $projeto->id,
            'tipo_vinculo' => TipoVinculo::COLABORADOR,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        $response = $this->actingAs($this->docente)->get(route('projetos.show', $projeto));

        $response->assertStatus(200);
        $response->assertInertia(
            fn($page) =>
            $page->component('Projetos/Show')
                ->has('participantesProjeto.data')
        );
    });

    it('filtra projetos por termo de busca', function () {
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

    it('projeto pode ser pesquisado por cliente', function () {
        Projeto::factory()->create(['cliente' => 'Dell Technologies']);
        Projeto::factory()->create(['cliente' => 'Microsoft']);

        $response = $this->actingAs($this->docente)->get(route('projetos.index', ['search' => 'Dell']));

        $response->assertStatus(200);
        $response->assertInertia(
            fn($page) =>
            $page->component('Projetos/Index')
                ->has('projetos')
        );
    });

    it('filtra projetos por tipo', function () {
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

    it('usuário pode acessar página de criação de projeto', function () {
        $response = $this->actingAs($this->docente)->get(route('projetos.create'));

        $response->assertStatus(200);
        $response->assertInertia(
            fn($page) =>
            $page->component('Projetos/Create')
        );
    });
});

describe('Atualização de Projetos', function () {
    it('permite atualização de projetos por usuários autorizados', function () {
        $projeto = Projeto::factory()->create([
            'nome' => 'Nome Original',
            'cliente' => 'Cliente Original',
        ]);

        $this->actingAs($this->docente);

        $dadosAtualizacao = [
            'nome' => 'Nome Atualizado',
            'descricao' => 'Nova descrição',
            'data_inicio' => $projeto->data_inicio->format('Y-m-d'),
            'cliente' => 'Cliente Atualizado',
            'tipo' => $projeto->tipo->value,
        ];

        $response = $this->put(route('projetos.update', $projeto), $dadosAtualizacao);

        $response->assertRedirect();
        $this->assertDatabaseHas('projetos', [
            'id' => $projeto->id,
            'nome' => 'Nome Atualizado',
            'cliente' => 'Cliente Atualizado',
        ]);
    });

    it('valida dados de atualização adequadamente', function () {
        $projeto = Projeto::factory()->create();

        $this->actingAs($this->docente);

        $response = $this->put(route('projetos.update', $projeto), [
            'nome' => '', // Nome vazio inválido
            'data_inicio' => 'invalid-date',
            'tipo' => 'INVALID_TYPE',
        ]);

        $response->assertSessionHasErrors(['nome', 'data_inicio', 'tipo']);
    });

    it('impede atualização de projetos deletados', function () {
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

describe('Exclusão de Projetos', function () {
    it('permite exclusão suave de projetos', function () {
        $projeto = Projeto::factory()->create();

        $this->actingAs($this->docente);

        $response = $this->delete(route('projetos.destroy', $projeto));

        $response->assertRedirect();
        $this->assertSoftDeleted('projetos', ['id' => $projeto->id]);
    });

    it('lida com exclusão de projetos com usuários ativos', function () {
        $projeto = Projeto::factory()->create();

        // Criar relacionamento ativo usuário-projeto
        UsuarioProjeto::factory()->create([
            'projeto_id' => $projeto->id,
            'usuario_id' => $this->discente->id,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        $this->actingAs($this->docente);

        $response = $this->delete(route('projetos.destroy', $projeto));

        $response->assertRedirect();
        $this->assertSoftDeleted('projetos', ['id' => $projeto->id]);

        // Verificar se relacionamentos usuário-projeto também são excluídos suavemente
        $this->assertSoftDeleted('usuario_projeto', [
            'projeto_id' => $projeto->id,
            'usuario_id' => $this->discente->id,
        ]);
    });

    it('impede exclusão de projetos já deletados', function () {
        $projeto = Projeto::factory()->create();
        $projeto->delete();

        $this->actingAs($this->docente);

        $response = $this->delete(route('projetos.destroy', $projeto));

        $response->assertNotFound();
    });
});

describe('Autorização de Projetos', function () {
    it('nega acesso a usuários não autorizados', function () {
        $projeto = Projeto::factory()->create();

        // Teste sem autenticação
        $response = $this->get(route('projetos.index'));
        $response->assertRedirect(route('login'));

        $response = $this->post(route('projetos.store'), []);
        $response->assertRedirect(route('login'));

        $response = $this->get(route('projetos.show', $projeto));
        $response->assertRedirect(route('login'));
    });

    it('permite acesso a usuários autenticados', function () {
        $projeto = Projeto::factory()->create();

        $this->actingAs($this->discente);

        $response = $this->get(route('projetos.index'));
        $response->assertOk();

        $response = $this->get(route('projetos.show', $projeto));
        $response->assertOk();
    });

    it('lida com usuários com status de aprovação pendente', function () {
        $usuarioPendente = User::factory()->create([
            'status_cadastro' => StatusCadastro::PENDENTE,
        ]);

        $this->actingAs($usuarioPendente);

        $response = $this->get(route('projetos.index'));

        // Deve permitir visualização mas pode ter permissões diferentes
        $response->assertOk();
    });
});

describe('Busca e Filtros de Projetos', function () {
    it('busca projetos por múltiplos critérios', function () {
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

        // Buscar por nome
        $response = $this->get(route('projetos.index', ['search' => 'Sistema']));
        $response->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->has('projetos.data', 1)
            );

        // Buscar por cliente
        $response = $this->get(route('projetos.index', ['search' => 'Empresa A']));
        $response->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->has('projetos.data', 1)
            );
    });

    it('lida com resultados de busca vazios graciosamente', function () {
        Projeto::factory()->count(3)->create();

        $this->actingAs($this->docente);

        $response = $this->get(route('projetos.index', ['search' => 'ProjetoInexistente']));

        $response->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->has('projetos.data', 0)
            );
    });

    it('filtra por intervalo de datas', function () {
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

describe('Casos Extremos de Validação de Projetos', function () {
    it('lida com nomes de projetos muito longos', function () {
        $this->actingAs($this->docente);

        $nomeLongo = str_repeat('A', 300); // Assumindo limite de 255 caracteres

        $response = $this->post(route('projetos.store'), [
            'nome' => $nomeLongo,
            'data_inicio' => '2024-01-01',
            'cliente' => 'Cliente',
            'tipo' => TipoProjeto::MESTRADO->value,
        ]);

        $response->assertSessionHasErrors(['nome']);
    });

    it('valida caracteres especiais em URLs', function () {
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

    it('lida com criação concorrente de projetos', function () {
        $this->actingAs($this->docente);

        $dadosProjeto = [
            'nome' => 'Projeto Concorrente',
            'data_inicio' => '2024-01-01',
            'cliente' => 'Cliente',
            'tipo' => TipoProjeto::MESTRADO->value,
        ];

        // Simular requisições concorrentes
        $response1 = $this->post(route('projetos.store'), $dadosProjeto);
        $response2 = $this->post(route('projetos.store'), $dadosProjeto);

        $response1->assertRedirect();
        $response2->assertRedirect();

        // Ambos devem ter sucesso já que nomes de projetos podem ser duplicados
        $this->assertDatabaseCount('projetos', 2);
    });
});

describe('Performance de Projetos', function () {
    it('lida com grande número de projetos eficientemente', function () {
        Projeto::factory()->count(100)->create();

        $this->actingAs($this->docente);

        $tempoInicio = microtime(true);
        $response = $this->get(route('projetos.index'));
        $tempoFim = microtime(true);

        $response->assertOk();

        // Deve completar em tempo razoável (2 segundos)
        expect($tempoFim - $tempoInicio)->toBeLessThan(2.0);
    });

    it('pagina projetos adequadamente', function () {
        Projeto::factory()->count(50)->create();

        $this->actingAs($this->docente);

        $response = $this->get(route('projetos.index'));

        $response->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('Projetos/Index')
                    ->has('projetos.data')
                    ->has('projetos.links')
                    ->where('projetos.per_page', 15) // Paginação padrão
            );
    });
});
