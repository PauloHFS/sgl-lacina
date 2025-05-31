<?php

use App\Models\User;
use App\Models\Projeto;
use App\Models\UsuarioProjeto;
use App\Enums\StatusCadastro;
use App\Enums\TipoVinculo;
use App\Enums\StatusVinculoProjeto;
use App\Enums\Funcao;

test('usuário com cadastro aceito pode solicitar vínculo a projeto', function () {
    $usuario = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create();

    $dadosVinculo = [
        'projeto_id' => $projeto->id,
        'data_inicio' => now()->addDays(7)->format('Y-m-d'),
        'carga_horaria_semanal' => 20,
        'tipo_vinculo' => TipoVinculo::COLABORADOR->value,
        'funcao' => Funcao::DESENVOLVEDOR->value,
    ];

    $response = $this->actingAs($usuario)->post('/vinculo', $dadosVinculo);

    $response->assertRedirect();

    $this->assertDatabaseHas('usuario_projeto', [
        'usuario_id' => $usuario->id,
        'projeto_id' => $projeto->id,
        'status' => StatusVinculoProjeto::PENDENTE->value,
        'carga_horaria_semanal' => 20,
    ]);
});

test('usuário com cadastro pendente não pode solicitar vínculo', function () {
    $usuario = User::factory()->create(['status_cadastro' => StatusCadastro::PENDENTE]);
    $projeto = Projeto::factory()->create();

    $dadosVinculo = [
        'projeto_id' => $projeto->id,
        'data_inicio' => now()->addDays(7)->format('Y-m-d'),
        'carga_horaria_semanal' => 20,
        'tipo_vinculo' => TipoVinculo::COLABORADOR->value,
        'funcao' => Funcao::DESENVOLVEDOR->value,
    ];

    $response = $this->actingAs($usuario)->post('/vinculo', $dadosVinculo);

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

test('usuário não pode solicitar vínculo duplicado ao mesmo projeto', function () {
    $usuario = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create();

    // Criar vínculo existente
    UsuarioProjeto::factory()->create([
        'usuario_id' => $usuario->id,
        'projeto_id' => $projeto->id,
        'status' => StatusVinculoProjeto::PENDENTE,
    ]);

    $dadosVinculo = [
        'projeto_id' => $projeto->id,
        'data_inicio' => now()->addDays(7)->format('Y-m-d'),
        'carga_horaria_semanal' => 20,
        'tipo_vinculo' => TipoVinculo::COLABORADOR->value,
        'funcao' => Funcao::DESENVOLVEDOR->value,
    ];

    $response = $this->actingAs($usuario)->post('/vinculo', $dadosVinculo);

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

test('coordenador pode aprovar solicitação de vínculo', function () {
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

    // Solicitação pendente
    $vinculoPendente = UsuarioProjeto::factory()->create([
        'usuario_id' => $colaborador->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COLABORADOR,
        'status' => StatusVinculoProjeto::PENDENTE,
    ]);

    $response = $this->actingAs($coordenador)
        ->patch("/vinculo/{$vinculoPendente->id}", [
            'status' => StatusVinculoProjeto::APROVADO->value,
        ]);

    $response->assertRedirect();

    $vinculoPendente->refresh();
    expect($vinculoPendente->status)->toBe(StatusVinculoProjeto::APROVADO);
});

test('coordenador pode recusar solicitação de vínculo', function () {
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

    // Solicitação pendente
    $vinculoPendente = UsuarioProjeto::factory()->create([
        'usuario_id' => $colaborador->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COLABORADOR,
        'status' => StatusVinculoProjeto::PENDENTE,
    ]);

    $response = $this->actingAs($coordenador)
        ->patch("/vinculo/{$vinculoPendente->id}", [
            'status' => StatusVinculoProjeto::RECUSADO->value,
        ]);

    $response->assertRedirect();

    $vinculoPendente->refresh();
    expect($vinculoPendente->status)->toBe(StatusVinculoProjeto::RECUSADO);
});

test('coordenador pode editar dados do vínculo antes de aprovar', function () {
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

    // Solicitação pendente
    $vinculoPendente = UsuarioProjeto::factory()->create([
        'usuario_id' => $colaborador->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COLABORADOR,
        'status' => StatusVinculoProjeto::PENDENTE,
        'carga_horaria_semanal' => 20,
        'funcao' => Funcao::DESENVOLVEDOR,
    ]);

    $response = $this->actingAs($coordenador)
        ->patch("/vinculo/{$vinculoPendente->id}", [
            'status' => StatusVinculoProjeto::APROVADO->value,
            'carga_horaria_semanal' => 30,
            'funcao' => Funcao::PESQUISADOR->value,
        ]);

    $response->assertRedirect();

    $vinculoPendente->refresh();
    expect($vinculoPendente->status)->toBe(StatusVinculoProjeto::APROVADO);
    expect($vinculoPendente->carga_horaria_semanal)->toBe(30);
    expect($vinculoPendente->funcao)->toBe(Funcao::PESQUISADOR);
});

test('usuário pode solicitar troca de projeto', function () {
    $usuario = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projetoAtual = Projeto::factory()->create();
    $projetoNovo = Projeto::factory()->create();

    // Vínculo atual aprovado
    $vinculoAtual = UsuarioProjeto::factory()->create([
        'usuario_id' => $usuario->id,
        'projeto_id' => $projetoAtual->id,
        'tipo_vinculo' => TipoVinculo::COLABORADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    $dadosTroca = [
        'projeto_id' => $projetoNovo->id,
        'data_inicio' => now()->addDays(14)->format('Y-m-d'),
        'carga_horaria_semanal' => 25,
        'tipo_vinculo' => TipoVinculo::COLABORADOR->value,
        'funcao' => Funcao::PESQUISADOR->value,
        'trocar' => true,
        'usuario_projeto_trocado_id' => $vinculoAtual->id,
    ];

    $response = $this->actingAs($usuario)->post('/vinculo', $dadosTroca);

    $response->assertRedirect();

    // Verificar se o vínculo antigo foi marcado para troca
    $vinculoAtual->refresh();
    expect($vinculoAtual->trocar)->toBeTrue();

    // Verificar se foi criada nova solicitação
    $this->assertDatabaseHas('usuario_projeto', [
        'usuario_id' => $usuario->id,
        'projeto_id' => $projetoNovo->id,
        'status' => StatusVinculoProjeto::PENDENTE->value,
    ]);
});

test('usuário não pode ter múltiplas trocas em andamento', function () {
    $usuario = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projetoAtual = Projeto::factory()->create();
    $projetoNovo = Projeto::factory()->create();

    // Vínculo atual com troca já em andamento
    $vinculoAtual = UsuarioProjeto::factory()->create([
        'usuario_id' => $usuario->id,
        'projeto_id' => $projetoAtual->id,
        'tipo_vinculo' => TipoVinculo::COLABORADOR,
        'status' => StatusVinculoProjeto::APROVADO,
        'trocar' => true,
    ]);

    $dadosTroca = [
        'projeto_id' => $projetoNovo->id,
        'data_inicio' => now()->addDays(14)->format('Y-m-d'),
        'carga_horaria_semanal' => 25,
        'tipo_vinculo' => TipoVinculo::COLABORADOR->value,
        'funcao' => Funcao::PESQUISADOR->value,
        'trocar' => true,
        'usuario_projeto_trocado_id' => $vinculoAtual->id,
    ];

    $response = $this->actingAs($usuario)->post('/vinculo', $dadosTroca);

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

test('colaborador sem vínculo pode solicitar adesão direta', function () {
    $usuario = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create();

    $dadosVinculo = [
        'projeto_id' => $projeto->id,
        'data_inicio' => now()->addDays(7)->format('Y-m-d'),
        'carga_horaria_semanal' => 20,
        'tipo_vinculo' => TipoVinculo::COLABORADOR->value,
        'funcao' => Funcao::ALUNO->value,
        'trocar' => false,
    ];

    $response = $this->actingAs($usuario)->post('/vinculo', $dadosVinculo);

    $response->assertRedirect();

    $this->assertDatabaseHas('usuario_projeto', [
        'usuario_id' => $usuario->id,
        'projeto_id' => $projeto->id,
        'status' => StatusVinculoProjeto::PENDENTE->value,
        'trocar' => false,
    ]);
});

test('carga horária semanal deve estar entre 1 e 40 horas', function () {
    $usuario = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create();

    $dadosInvalidos = [
        'projeto_id' => $projeto->id,
        'data_inicio' => now()->addDays(7)->format('Y-m-d'),
        'carga_horaria_semanal' => 50, // Acima do limite
        'tipo_vinculo' => TipoVinculo::COLABORADOR->value,
        'funcao' => Funcao::DESENVOLVEDOR->value,
    ];

    $response = $this->actingAs($usuario)->post('/vinculo', $dadosInvalidos);

    $response->assertSessionHasErrors(['carga_horaria_semanal']);
});
