<?php

use App\Enums\StatusCadastro;
use App\Enums\StatusVinculoProjeto;
use App\Enums\TipoVinculo;
use App\Models\Projeto;
use App\Models\User;
use App\Models\UsuarioProjeto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

// Testes para a rota GET /projeto/{projeto}/edit (projetos.edit)

test('unauthenticated user cannot access project edit page', function () {
    $projeto = Projeto::factory()->create();
    $response = $this->get(route('projetos.edit', $projeto));
    $response->assertRedirect(route('login'));
});

test('authenticated user who is not a coordinator cannot access project edit page', function () {
    $user = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create();
    // Vincular usuário como participante, não coordenador
    UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COLABORADOR->value, // Corrigido de PARTICIPANTE para COLABORADOR
        'status' => StatusVinculoProjeto::APROVADO->value,
    ]);

    $this->actingAs($user)
        ->get(route('projetos.edit', $projeto))
        ->assertRedirect(route('projetos.show', $projeto->id))
        ->assertSessionHas('error', 'Você não tem permissão para editar este projeto.');
});

test('authenticated user who is a non-approved coordinator cannot access project edit page', function () {
    $user = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create();
    UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR->value,
        'status' => StatusVinculoProjeto::PENDENTE->value, // Não aprovado
    ]);

    $this->actingAs($user)
        ->get(route('projetos.edit', $projeto))
        ->assertRedirect(route('projetos.show', $projeto->id))
        ->assertSessionHas('error', 'Você não tem permissão para editar este projeto.');
});

test('authenticated user who is an approved coordinator can access project edit page', function () {
    $user = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create();
    UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR->value,
        'status' => StatusVinculoProjeto::APROVADO->value,
    ]);

    $this->actingAs($user)
        ->get(route('projetos.edit', $projeto))
        ->assertOk()
        ->assertInertia(
            fn(Assert $page) => $page
                ->component('Projetos/Edit')
                ->has(
                    'projeto',
                    fn(Assert $prop) => $prop
                        ->where('id', (string)$projeto->id) // Ensure $projeto->id is cast to string for comparison
                        ->etc()
                )
                ->has('tiposProjeto')
        );
});


// Testes para a rota PATCH /projeto/{projeto} (projetos.update)

test('unauthenticated user cannot update a project', function () {
    $projeto = Projeto::factory()->create();
    $updateData = [
        'nome' => 'Projeto Atualizado',
        'cliente' => 'Novo Cliente',
    ];

    $this->patch(route('projetos.update', $projeto), $updateData)
        ->assertRedirect(route('login'));
});

test('authenticated user who is not a coordinator cannot update a project', function () {
    $user = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create(['nome' => 'Nome Antigo']);
    UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COLABORADOR->value, // Corrigido de PARTICIPANTE para COLABORADOR
        'status' => StatusVinculoProjeto::APROVADO->value,
    ]);

    $updateData = [
        'nome' => 'Projeto Atualizado por Não Coordenador',
        'descricao' => $projeto->descricao,
        'data_inicio' => $projeto->data_inicio->format('Y-m-d'),
        'data_termino' => $projeto->data_termino?->format('Y-m-d'),
        'cliente' => $projeto->cliente,
        'tipo' => $projeto->tipo->value, // Corrected
    ];

    $this->actingAs($user)
        ->patch(route('projetos.update', $projeto), $updateData)
        ->assertRedirect(route('projetos.show', $projeto->id)) // Changed from assertForbidden
        ->assertSessionHas('error', 'Você não tem permissão para editar este projeto.');

    $this->assertDatabaseHas('projetos', [
        'id' => $projeto->id,
        'nome' => 'Nome Antigo', // Nome não deve ter sido alterado
    ]);
});

test('authenticated user who is a non-approved coordinator cannot update a project', function () {
    $user = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create(['nome' => 'Nome Original']);
    UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR->value,
        'status' => StatusVinculoProjeto::PENDENTE->value, // Não aprovado
    ]);

    $updateData = [
        'nome' => 'Projeto Atualizado por Coordenador Pendente',
        'descricao' => $projeto->descricao,
        'data_inicio' => $projeto->data_inicio->format('Y-m-d'),
        'data_termino' => $projeto->data_termino?->format('Y-m-d'),
        'cliente' => $projeto->cliente,
        'tipo' => $projeto->tipo->value, // Corrected
    ];

    $this->actingAs($user)
        ->patch(route('projetos.update', $projeto), $updateData)
        ->assertRedirect(route('projetos.show', $projeto->id)) // Changed from assertForbidden
        ->assertSessionHas('error', 'Você não tem permissão para editar este projeto.');

    $this->assertDatabaseHas('projetos', [
        'id' => $projeto->id,
        'nome' => 'Nome Original',
    ]);
});

test('approved coordinator can successfully update a project with valid data', function () {
    $user = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create();
    UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR->value,
        'status' => StatusVinculoProjeto::APROVADO->value,
    ]);

    $updateData = [
        'nome' => 'Nome do Projeto Atualizado',
        'descricao' => 'Descrição atualizada do projeto.',
        'data_inicio' => '2025-07-01',
        'data_termino' => '2026-06-30',
        'cliente' => 'Cliente Atualizado Inc.',
        'slack_url' => 'https://updated.slack.com',
        'discord_url' => 'https://updated.discord.com',
        'board_url' => 'https://updated.board.com',
        'git_url' => 'https://updated.git.com',
        'tipo' => App\Enums\TipoProjeto::PDI->value, // Corrected
    ];

    $this->actingAs($user)
        ->patch(route('projetos.update', $projeto), $updateData)
        ->assertRedirect(route('projetos.show', $projeto->id))
        ->assertSessionHas('success', 'Projeto atualizado com sucesso!');

    $this->assertDatabaseHas('projetos', array_merge(['id' => $projeto->id], $updateData));
});

test('updating a project with invalid data returns validation errors', function () {
    $user = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create();
    UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR->value,
        'status' => StatusVinculoProjeto::APROVADO->value,
    ]);

    $invalidData = [
        'nome' => '', // Nome é obrigatório
        'data_inicio' => 'data-invalida', // Formato inválido
        'cliente' => '', // Cliente é obrigatório
        'tipo' => 'TIPO_INVALIDO', // Tipo inválido
        'slack_url' => 'url invalida',
    ];

    $this->actingAs($user)
        ->patch(route('projetos.update', $projeto), $invalidData)
        ->assertSessionHasErrors(['nome', 'data_inicio', 'cliente', 'tipo', 'slack_url']);
});

test('data_termino can be null when updating', function () {
    $user = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create([
        'data_termino' => '2025-12-31'
    ]);
    UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR->value,
        'status' => StatusVinculoProjeto::APROVADO->value,
    ]);

    $updateData = [
        'nome' => 'Projeto com Data Término Nula',
        'descricao' => $projeto->descricao,
        'data_inicio' => $projeto->data_inicio->format('Y-m-d'),
        'data_termino' => null,
        'cliente' => $projeto->cliente,
        'tipo' => $projeto->tipo->value, // Corrected
    ];

    $this->actingAs($user)
        ->patch(route('projetos.update', $projeto), $updateData)
        ->assertRedirect(route('projetos.show', $projeto->id))
        ->assertSessionHas('success', 'Projeto atualizado com sucesso!');

    $this->assertDatabaseHas('projetos', [
        'id' => $projeto->id,
        'nome' => 'Projeto com Data Término Nula',
        'data_termino' => null,
    ]);
});

test('data_termino must be after or equal to data_inicio when updating', function () {
    $user = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create();
    UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR->value,
        'status' => StatusVinculoProjeto::APROVADO->value,
    ]);

    $invalidData = [
        'nome' => 'Projeto Teste Datas',
        'descricao' => 'Descrição teste.',
        'data_inicio' => '2025-08-01',
        'data_termino' => '2025-07-01', // Data de término antes da data de início
        'cliente' => 'Cliente Teste',
        'tipo' => App\Enums\TipoProjeto::PDI->value, // Corrected
    ];

    $this->actingAs($user)
        ->patch(route('projetos.update', $projeto), $invalidData)
        ->assertSessionHasErrors(['data_termino']);
});

test('optional fields can be null when updating', function () {
    $user = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $dataInicioEspecifica = '2024-01-01'; // Data de início específica
    $projeto = Projeto::factory()->create([
        'data_inicio' => $dataInicioEspecifica,
        'slack_url' => 'https://original.slack.com',
        'discord_url' => 'https://original.discord.com',
        'board_url' => 'https://original.board.com',
        'git_url' => 'https://original.git.com',
    ]);
    UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR->value,
        'status' => StatusVinculoProjeto::APROVADO->value,
    ]);

    $updateData = [
        'nome' => 'Projeto com Campos Opcionais Nulos',
        'descricao' => $projeto->descricao,
        'data_inicio' => $dataInicioEspecifica, // Usar a data de início específica
        'data_termino' => null, // data_termino é null
        'cliente' => $projeto->cliente,
        'tipo' => $projeto->tipo->value,
        'slack_url' => null,
        'discord_url' => null,
        'board_url' => null,
        'git_url' => null,
    ];

    $this->actingAs($user)
        ->patch(route('projetos.update', $projeto), $updateData)
        ->assertRedirect(route('projetos.show', $projeto->id))
        ->assertSessionHas('success', 'Projeto atualizado com sucesso!');

    $this->assertDatabaseHas('projetos', [
        'id' => $projeto->id,
        'nome' => 'Projeto com Campos Opcionais Nulos',
        'slack_url' => null,
        'discord_url' => null,
        'board_url' => null,
        'git_url' => null,
    ]);
});

test('url fields must be valid urls when updating', function () {
    $user = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create();
    UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR->value,
        'status' => StatusVinculoProjeto::APROVADO->value,
    ]);

    $invalidData = [
        'nome' => 'Projeto Teste URLs',
        'descricao' => 'Descrição teste.',
        'data_inicio' => '2025-01-01',
        'cliente' => 'Cliente Teste',
        'tipo' => App\Enums\TipoProjeto::PDI->value, // Corrected
        'slack_url' => 'nao-e-url',
        'discord_url' => 'http://incomplete',
        'board_url' => 'ftp://valid.com',
        'git_url' => 'invalid-git-url',
    ];

    $response = $this->actingAs($user)
        ->patch(route('projetos.update', $projeto), $invalidData);

    $response->assertSessionHasErrors(['slack_url', 'git_url']);
    // A validação de 'discord_url' pode passar se 'http://incomplete' for considerado um início válido por 'url'.
    // Para ser mais rigoroso, poderia usar regex ou validação de domínio.
});
