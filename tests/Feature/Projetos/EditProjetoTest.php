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

// Testes específicos para o campo valor_total

test('can update project with valid valor_total', function () {
    $user = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create();
    UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR->value,
        'status' => StatusVinculoProjeto::APROVADO->value,
    ]);

    $updateData = [
        'nome' => 'Projeto com Valor Total',
        'descricao' => 'Projeto para testar valor total',
        'data_inicio' => '2025-01-01',
        'cliente' => 'Cliente Teste',
        'tipo' => App\Enums\TipoProjeto::PDI->value,
        'valor_total' => 150000, // R$ 1500,00 em centavos
    ];

    $this->actingAs($user)
        ->patch(route('projetos.update', $projeto), $updateData)
        ->assertRedirect(route('projetos.show', $projeto->id))
        ->assertSessionHas('success', 'Projeto atualizado com sucesso!');

    $this->assertDatabaseHas('projetos', [
        'id' => $projeto->id,
        'nome' => 'Projeto com Valor Total',
        'valor_total' => 150000,
    ]);
});

test('can update project with null valor_total', function () {
    $user = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create(['valor_total' => 100000]);
    UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR->value,
        'status' => StatusVinculoProjeto::APROVADO->value,
    ]);

    $updateData = [
        'nome' => 'Projeto sem Valor Total',
        'descricao' => 'Projeto para testar valor total nulo',
        'data_inicio' => '2025-01-01',
        'cliente' => 'Cliente Teste',
        'tipo' => App\Enums\TipoProjeto::PDI->value,
        // Não enviamos valor_total para que use o valor default (0)
    ];

    $response = $this->actingAs($user)
        ->patch(route('projetos.update', $projeto), $updateData);

    $response->assertRedirect(route('projetos.show', $projeto->id));

    // Verificar em uma nova consulta
    $projetoAtualizado = Projeto::find($projeto->id);
    // Como não enviamos valor_total, deve manter o valor anterior (não usar default)
    expect($projetoAtualizado->valor_total)->toBe(100000);
    expect($projetoAtualizado->nome)->toBe('Projeto sem Valor Total');
});

test('cannot update project with negative valor_total', function () {
    $user = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create();
    UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR->value,
        'status' => StatusVinculoProjeto::APROVADO->value,
    ]);

    $invalidData = [
        'nome' => 'Projeto com Valor Negativo',
        'descricao' => 'Projeto para testar validação',
        'data_inicio' => '2025-01-01',
        'cliente' => 'Cliente Teste',
        'tipo' => App\Enums\TipoProjeto::PDI->value,
        'valor_total' => -1000, // Valor negativo
    ];

    $this->actingAs($user)
        ->patch(route('projetos.update', $projeto), $invalidData)
        ->assertSessionHasErrors(['valor_total']);
});

test('cannot update project with non-integer valor_total', function () {
    $user = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create();
    UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR->value,
        'status' => StatusVinculoProjeto::APROVADO->value,
    ]);

    $invalidData = [
        'nome' => 'Projeto com Valor Inválido',
        'descricao' => 'Projeto para testar validação',
        'data_inicio' => '2025-01-01',
        'cliente' => 'Cliente Teste',
        'tipo' => App\Enums\TipoProjeto::PDI->value,
        'valor_total' => 'invalid-value', // Valor não numérico
    ];

    $this->actingAs($user)
        ->patch(route('projetos.update', $projeto), $invalidData)
        ->assertSessionHasErrors(['valor_total']);
});

// Testes específicos para o campo campos_extras

test('can update project with valid campos_extras', function () {
    $user = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create();
    UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR->value,
        'status' => StatusVinculoProjeto::APROVADO->value,
    ]);

    $camposExtras = [
        'gerente' => 'João Silva',
        'departamento' => 'TI',
        'prioridade' => 'Alta',
        'observacoes' => 'Projeto estratégico'
    ];

    $updateData = [
        'nome' => 'Projeto com Campos Extras',
        'descricao' => 'Projeto para testar campos extras',
        'data_inicio' => '2025-01-01',
        'cliente' => 'Cliente Teste',
        'tipo' => App\Enums\TipoProjeto::PDI->value,
        'campos_extras' => $camposExtras,
    ];

    $response = $this->actingAs($user)
        ->patch(route('projetos.update', $projeto), $updateData);

    $response->assertRedirect(route('projetos.show', $projeto->id));

    $projeto->refresh();

    // Verificar que todos os campos estão presentes, independente da ordem
    expect($projeto->campos_extras)->toHaveCount(4);
    expect($projeto->campos_extras)->toHaveKey('gerente', 'João Silva');
    expect($projeto->campos_extras)->toHaveKey('departamento', 'TI');
    expect($projeto->campos_extras)->toHaveKey('prioridade', 'Alta');
    expect($projeto->campos_extras)->toHaveKey('observacoes', 'Projeto estratégico');
});

test('can update project with empty campos_extras', function () {
    $user = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create([
        'campos_extras' => ['campo1' => 'valor1', 'campo2' => 'valor2']
    ]);
    UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR->value,
        'status' => StatusVinculoProjeto::APROVADO->value,
    ]);

    $updateData = [
        'nome' => 'Projeto sem Campos Extras',
        'descricao' => 'Projeto para testar campos extras vazios',
        'data_inicio' => '2025-01-01',
        'cliente' => 'Cliente Teste',
        'tipo' => App\Enums\TipoProjeto::PDI->value,
        // Não enviamos campos_extras para que mantenha o valor anterior
    ];

    $this->actingAs($user)
        ->patch(route('projetos.update', $projeto), $updateData)
        ->assertRedirect(route('projetos.show', $projeto->id));

    // Verificar em uma nova consulta
    $projetoAtualizado = Projeto::find($projeto->id);
    // Como não enviamos campos_extras, deve manter o valor anterior
    expect($projetoAtualizado->campos_extras)->toEqual(['campo1' => 'valor1', 'campo2' => 'valor2']);
});

test('can update project with null campos_extras', function () {
    $user = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create([
        'campos_extras' => ['campo1' => 'valor1']
    ]);
    UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR->value,
        'status' => StatusVinculoProjeto::APROVADO->value,
    ]);

    $updateData = [
        'nome' => 'Projeto com Campos Extras Nulos',
        'descricao' => 'Projeto para testar campos extras nulos',
        'data_inicio' => '2025-01-01',
        'cliente' => 'Cliente Teste',
        'tipo' => App\Enums\TipoProjeto::PDI->value,
        // Não enviamos campos_extras para que mantenha o valor anterior
    ];

    $response = $this->actingAs($user)
        ->patch(route('projetos.update', $projeto), $updateData);

    $response->assertRedirect(route('projetos.show', $projeto->id));

    // Verificar em uma nova consulta
    $projetoAtualizado = Projeto::find($projeto->id);
    // Como não enviamos campos_extras, deve manter o valor anterior
    expect($projetoAtualizado->campos_extras)->toEqual(['campo1' => 'valor1']);
    expect($projetoAtualizado->nome)->toBe('Projeto com Campos Extras Nulos');
});

test('can explicitly clear campos_extras by sending empty array', function () {
    $user = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create([
        'campos_extras' => ['campo1' => 'valor1', 'campo2' => 'valor2']
    ]);
    UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR->value,
        'status' => StatusVinculoProjeto::APROVADO->value,
    ]);

    $updateData = [
        'nome' => 'Projeto com Campos Extras Limpos',
        'descricao' => 'Projeto para testar limpeza de campos extras',
        'data_inicio' => '2025-01-01',
        'cliente' => 'Cliente Teste',
        'tipo' => App\Enums\TipoProjeto::PDI->value,
        'campos_extras' => [], // Explicitamente enviando array vazio para limpar
    ];

    $response = $this->actingAs($user)
        ->patch(route('projetos.update', $projeto), $updateData);

    $response->assertRedirect(route('projetos.show', $projeto->id));

    // Verificar em uma nova consulta
    $projetoAtualizado = Projeto::find($projeto->id);
    // Quando enviamos array vazio explicitamente, deve limpar os campos
    expect($projetoAtualizado->campos_extras)->toEqual([]);
    expect($projetoAtualizado->nome)->toBe('Projeto com Campos Extras Limpos');
});

test('can explicitly set valor_total to zero', function () {
    $user = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create(['valor_total' => 100000]);
    UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR->value,
        'status' => StatusVinculoProjeto::APROVADO->value,
    ]);

    $updateData = [
        'nome' => 'Projeto com Valor Zero',
        'descricao' => 'Projeto para testar valor total zero',
        'data_inicio' => '2025-01-01',
        'cliente' => 'Cliente Teste',
        'tipo' => App\Enums\TipoProjeto::PDI->value,
        'valor_total' => 0, // Explicitamente enviando zero
    ];

    $response = $this->actingAs($user)
        ->patch(route('projetos.update', $projeto), $updateData);

    $response->assertRedirect(route('projetos.show', $projeto->id));

    // Verificar em uma nova consulta
    $projetoAtualizado = Projeto::find($projeto->id);
    // Quando enviamos 0 explicitamente, deve definir como 0
    expect($projetoAtualizado->valor_total)->toBe(0);
    expect($projetoAtualizado->nome)->toBe('Projeto com Valor Zero');
});

test('cannot update project with invalid campos_extras structure', function () {
    $user = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create();
    UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR->value,
        'status' => StatusVinculoProjeto::APROVADO->value,
    ]);

    $invalidData = [
        'nome' => 'Projeto com Campos Extras Inválidos',
        'descricao' => 'Projeto para testar validação',
        'data_inicio' => '2025-01-01',
        'cliente' => 'Cliente Teste',
        'tipo' => App\Enums\TipoProjeto::PDI->value,
        'campos_extras' => 'string-instead-of-array', // String ao invés de array
    ];

    $this->actingAs($user)
        ->patch(route('projetos.update', $projeto), $invalidData)
        ->assertSessionHasErrors(['campos_extras']);
});

test('cannot update project with campos_extras values exceeding max length', function () {
    $user = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create();
    UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR->value,
        'status' => StatusVinculoProjeto::APROVADO->value,
    ]);

    $longString = str_repeat('a', 256); // 256 caracteres, ultrapassando o limite de 255

    $invalidData = [
        'nome' => 'Projeto com Campos Extras Longos',
        'descricao' => 'Projeto para testar validação',
        'data_inicio' => '2025-01-01',
        'cliente' => 'Cliente Teste',
        'tipo' => App\Enums\TipoProjeto::PDI->value,
        'campos_extras' => [
            'campo_longo' => $longString,
        ],
    ];

    $this->actingAs($user)
        ->patch(route('projetos.update', $projeto), $invalidData)
        ->assertSessionHasErrors(['campos_extras.campo_longo']);
});

test('can update project with both valor_total and campos_extras', function () {
    $user = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create();
    UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR->value,
        'status' => StatusVinculoProjeto::APROVADO->value,
    ]);

    $camposExtras = [
        'gerente' => 'Maria Santos',
        'budget_aprovado' => 'Sim',
        'criticidade' => 'Média'
    ];

    $updateData = [
        'nome' => 'Projeto Completo',
        'descricao' => 'Projeto para testar campos combinados',
        'data_inicio' => '2025-01-01',
        'cliente' => 'Cliente Teste',
        'tipo' => App\Enums\TipoProjeto::PDI->value,
        'valor_total' => 250000, // R$ 2500,00 em centavos
        'campos_extras' => $camposExtras,
    ];

    $response = $this->actingAs($user)
        ->patch(route('projetos.update', $projeto), $updateData);

    $response->assertRedirect(route('projetos.show', $projeto->id));

    $projeto->refresh();
    expect($projeto->valor_total)->toBe(250000);

    // Verificar que todos os campos estão presentes, independente da ordem
    expect($projeto->campos_extras)->toHaveCount(3);
    expect($projeto->campos_extras)->toHaveKey('gerente', 'Maria Santos');
    expect($projeto->campos_extras)->toHaveKey('budget_aprovado', 'Sim');
    expect($projeto->campos_extras)->toHaveKey('criticidade', 'Média');
});

test('campos_extras persist as json in database', function () {
    $user = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create();
    UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR->value,
        'status' => StatusVinculoProjeto::APROVADO->value,
    ]);

    $camposExtras = [
        'responsavel_tecnico' => 'Pedro Oliveira',
        'tecnologias' => 'Laravel, React',
        'deadline_critico' => '2025-06-30'
    ];

    $updateData = [
        'nome' => 'Projeto para Teste JSON',
        'descricao' => 'Projeto para testar persistência JSON',
        'data_inicio' => '2025-01-01',
        'cliente' => 'Cliente Teste',
        'tipo' => App\Enums\TipoProjeto::PDI->value,
        'campos_extras' => $camposExtras,
    ];

    $response = $this->actingAs($user)
        ->patch(route('projetos.update', $projeto), $updateData);

    $response->assertRedirect(route('projetos.show', $projeto->id));

    // Verificar que os dados foram salvos corretamente no banco
    $this->assertDatabaseHas('projetos', [
        'id' => $projeto->id,
        'campos_extras' => json_encode($camposExtras),
    ]);

    // Verificar que o model retorna os dados corretamente (cast para array)
    $projeto->refresh();
    expect($projeto->campos_extras)->toHaveCount(3);
    expect($projeto->campos_extras)->toHaveKey('responsavel_tecnico', 'Pedro Oliveira');
    expect($projeto->campos_extras)->toHaveKey('tecnologias', 'Laravel, React');
    expect($projeto->campos_extras)->toHaveKey('deadline_critico', '2025-06-30');
});
