<?php

use App\Models\User;
use App\Models\Projeto;
use App\Models\UsuarioProjeto;
use App\Enums\StatusCadastro;
use App\Enums\TipoVinculo;
use App\Enums\StatusVinculoProjeto;
use App\Enums\Funcao;
use App\Enums\TipoProjeto;

test('coordenador pode criar projeto', function () {
    $coordenador = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);

    $dadosProjeto = [
        'nome' => 'Projeto Teste',
        'descricao' => 'Descrição do projeto teste',
        'data_inicio' => '2024-01-01',
        'data_termino' => '2024-12-31',
        'cliente' => 'Cliente Teste',
        'slack_url' => 'https://slack.com/test',
        'discord_url' => 'https://discord.com/test',
        'board_url' => 'https://trello.com/test',
        'git_url' => 'https://github.com/test',
        'tipo' => TipoProjeto::PDI->value,
    ];

    $response = $this->actingAs($coordenador)
        ->post('/projeto/new', $dadosProjeto);

    $response->assertRedirect();

    $this->assertDatabaseHas('projetos', [
        'nome' => 'Projeto Teste',
        'cliente' => 'Cliente Teste',
    ]);

    // Verificar se o coordenador foi automaticamente vinculado ao projeto
    $projeto = Projeto::where('nome', 'Projeto Teste')->first();
    $this->assertDatabaseHas('usuario_projeto', [
        'usuario_id' => $coordenador->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR->value,
        'status' => StatusVinculoProjeto::APROVADO->value,
    ]);
});

test('coordenador pode visualizar projetos que coordena', function () {
    $coordenador = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create();

    UsuarioProjeto::factory()->create([
        'usuario_id' => $coordenador->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    $response = $this->actingAs($coordenador)->get('/projeto?tab=coordenador');

    $response->assertStatus(200);
    $response->assertInertia(
        fn($page) =>
        $page->component('Projetos/Index')
            ->has('projetos')
    );
});

test('colaborador pode visualizar projetos que participa', function () {
    $colaborador = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create();

    UsuarioProjeto::factory()->create([
        'usuario_id' => $colaborador->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COLABORADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    $response = $this->actingAs($colaborador)->get('/projeto?tab=colaborador');

    $response->assertStatus(200);
    $response->assertInertia(
        fn($page) =>
        $page->component('Projetos/Index')
            ->has('projetos')
    );
});

test('usuário pode visualizar detalhes de projeto se tiver vínculo aprovado', function () {
    $usuario = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create();

    UsuarioProjeto::factory()->create([
        'usuario_id' => $usuario->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COLABORADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    $response = $this->actingAs($usuario)->get("/projeto/{$projeto->id}");

    $response->assertStatus(200);
    $response->assertInertia(
        fn($page) =>
        $page->component('Projetos/Show')
            ->has('projeto')
            ->has('usuarioVinculo')
    );
});

test('usuário sem vínculo não pode visualizar detalhes do projeto', function () {
    $usuario = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create();

    $response = $this->actingAs($usuario)->get("/projeto/{$projeto->id}");

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

test('coordenador pode visualizar participantes do projeto', function () {
    $coordenador = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $colaborador = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create();

    // Coordenador do projeto
    UsuarioProjeto::factory()->create([
        'usuario_id' => $coordenador->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    // Colaborador aprovado
    UsuarioProjeto::factory()->create([
        'usuario_id' => $colaborador->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COLABORADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    $response = $this->actingAs($coordenador)->get("/projeto/{$projeto->id}");

    $response->assertStatus(200);
    $response->assertInertia(
        fn($page) =>
        $page->component('Projetos/Show')
            ->has('participantesProjeto.data')
    );
});

test('projeto pode ser pesquisado por nome', function () {
    $usuario = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto1 = Projeto::factory()->create(['nome' => 'Sistema de Gestão']);
    $projeto2 = Projeto::factory()->create(['nome' => 'App Mobile']);

    $response = $this->actingAs($usuario)->get('/projeto?search=Sistema');

    $response->assertStatus(200);
    $response->assertInertia(
        fn($page) =>
        $page->component('Projetos/Index')
            ->has('projetos')
    );
});

test('projeto pode ser pesquisado por cliente', function () {
    $usuario = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto1 = Projeto::factory()->create(['cliente' => 'Dell Technologies']);
    $projeto2 = Projeto::factory()->create(['cliente' => 'Microsoft']);

    $response = $this->actingAs($usuario)->get('/projeto?search=Dell');

    $response->assertStatus(200);
    $response->assertInertia(
        fn($page) =>
        $page->component('Projetos/Index')
            ->has('projetos')
    );
});

test('usuário pode acessar página de criação de projeto', function () {
    $usuario = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);

    $response = $this->actingAs($usuario)->get('/projeto/new');

    $response->assertStatus(200);
    $response->assertInertia(
        fn($page) =>
        $page->component('Projetos/Create')
    );
});

test('projeto requer dados obrigatórios para criação', function () {
    $usuario = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);

    $response = $this->actingAs($usuario)->post('/projeto/new', []);

    $response->assertSessionHasErrors(['nome', 'data_inicio', 'cliente', 'tipo']);
});

test('data_termino deve ser posterior à data_inicio', function () {
    $usuario = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);

    $dadosInvalidos = [
        'nome' => 'Projeto Teste',
        'data_inicio' => '2024-12-31',
        'data_termino' => '2024-01-01', // Data anterior ao início
        'cliente' => 'Cliente Teste',
        'tipo' => TipoProjeto::PDI->value,
    ];

    $response = $this->actingAs($usuario)->post('/projeto/new', $dadosInvalidos);

    $response->assertSessionHasErrors(['data_termino']);
});
