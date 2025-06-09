<?php

use App\Models\User;
use App\Models\Projeto;
use App\Models\UsuarioProjeto;
use App\Enums\StatusCadastro;
use App\Enums\TipoVinculo;
use App\Enums\StatusVinculoProjeto;
use App\Enums\Funcao;

test('usuário com cadastro aceito pode criar solicitação de vínculo a projeto', function () {
    $usuario = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create();

    $dadosVinculo = [
        'projeto_id' => $projeto->id,
        'data_inicio' => now()->addDays(7)->format('Y-m-d'),
        'carga_horaria' => 20,
        'tipo_vinculo' => TipoVinculo::COLABORADOR->value,
        'funcao' => Funcao::DESENVOLVEDOR->value,
    ];

    $response = $this->actingAs($usuario)->post('/vinculo', $dadosVinculo);

    $response->assertRedirect();

    $this->assertDatabaseHas('usuario_projeto', [
        'usuario_id' => $usuario->id,
        'projeto_id' => $projeto->id,
        'status' => StatusVinculoProjeto::PENDENTE->value,
        'carga_horaria' => 20,
    ]);
});

test('usuário com cadastro pendente é redirecionado ao tentar criar solicitação de vínculo', function () {
    $usuario = User::factory()->create(['status_cadastro' => StatusCadastro::PENDENTE]);
    $projeto = Projeto::factory()->create();

    $dadosVinculo = [
        'projeto_id' => $projeto->id,
        'data_inicio' => now()->addDays(7)->format('Y-m-d'),
        'carga_horaria' => 20,
        'tipo_vinculo' => TipoVinculo::COLABORADOR->value,
        'funcao' => Funcao::DESENVOLVEDOR->value,
    ];

    $response = $this->actingAs($usuario)->post('/vinculo', $dadosVinculo);

    // Usuário com status PENDENTE é redirecionado pelo middleware
    $response->assertRedirect(route('waiting-approval'));
});

test('usuário não pode criar múltiplas solicitações de vínculo ao mesmo projeto', function () {
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
        'carga_horaria' => 20,
        'tipo_vinculo' => TipoVinculo::COLABORADOR->value,
        'funcao' => Funcao::DESENVOLVEDOR->value,
    ];

    $response = $this->actingAs($usuario)->post('/vinculo', $dadosVinculo);

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

test('coordenador pode aprovar solicitação de vínculo pendente', function () {
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

test('coordenador pode recusar vínculo pendente (PENDENTE → RECUSADO)', function () {
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

test('coordenador pode editar carga horária e função ao aprovar vínculo', function () {
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
        'carga_horaria' => 20,
        'funcao' => Funcao::DESENVOLVEDOR,
    ]);

    $response = $this->actingAs($coordenador)
        ->patch("/vinculo/{$vinculoPendente->id}", [
            'status' => StatusVinculoProjeto::APROVADO->value,
            'carga_horaria' => 30,
            'funcao' => Funcao::PESQUISADOR->value,
        ]);

    $response->assertRedirect();

    $vinculoPendente->refresh();
    expect($vinculoPendente->status)->toBe(StatusVinculoProjeto::APROVADO);
    expect($vinculoPendente->carga_horaria)->toBe(30);
    expect($vinculoPendente->funcao)->toBe(Funcao::PESQUISADOR);
});

test('usuário com vínculo aprovado pode solicitar troca para novo projeto', function () {
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
        'carga_horaria' => 25,
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

test('usuário com troca pendente não pode solicitar nova troca', function () {
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
        'carga_horaria' => 25,
        'tipo_vinculo' => TipoVinculo::COLABORADOR->value,
        'funcao' => Funcao::PESQUISADOR->value,
        'trocar' => true,
        'usuario_projeto_trocado_id' => $vinculoAtual->id,
    ];

    $response = $this->actingAs($usuario)->post('/vinculo', $dadosTroca);

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

test('usuário sem vínculo ativo pode criar solicitação direta de adesão', function () {
    $usuario = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create();

    $dadosVinculo = [
        'projeto_id' => $projeto->id,
        'data_inicio' => now()->addDays(7)->format('Y-m-d'),
        'carga_horaria' => 20,
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

test('solicitação de vínculo com carga horária acima do limite é rejeitada', function () {
    $usuario = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create();

    $dadosInvalidos = [
        'projeto_id' => $projeto->id,
        'data_inicio' => now()->addDays(7)->format('Y-m-d'),
        'carga_horaria' => 500, // Acima do limite
        'tipo_vinculo' => TipoVinculo::COLABORADOR->value,
        'funcao' => Funcao::DESENVOLVEDOR->value,
    ];

    $response = $this->actingAs($usuario)->post('/vinculo', $dadosInvalidos);

    $response->assertSessionHasErrors(['carga_horaria']);
});

// ==> Testes de Autorização

test('colaborador não coordenador não pode aprovar vínculo de terceiros', function () {
    $usuario1 = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $usuario2 = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create();

    // Usuário 2 solicita vínculo
    $vinculo = UsuarioProjeto::factory()->create([
        'usuario_id' => $usuario2->id,
        'projeto_id' => $projeto->id,
        'status' => StatusVinculoProjeto::PENDENTE,
    ]);

    // Usuário 1 tenta aprovar o vínculo do usuário 2
    $response = $this->actingAs($usuario1)->put("/vinculos/{$vinculo->id}", [
        'status' => StatusVinculoProjeto::APROVADO->value
    ]);

    // Deve retornar erro 403 (Forbidden) 
    $response->assertForbidden();
});

test('usuário não autenticado é redirecionado ao tentar acessar endpoints de vínculo', function () {
    $projeto = Projeto::factory()->create();

    // Tentativa de criar vínculo sem autenticação
    $response = $this->post('/vinculo', [
        'projeto_id' => $projeto->id,
        'data_inicio' => now()->addDays(1)->format('Y-m-d'),
        'carga_horaria' => 20,
        'tipo_vinculo' => TipoVinculo::COLABORADOR->value,
        'funcao' => Funcao::DESENVOLVEDOR->value,
    ]);

    $response->assertRedirect('/login');
});

test('coordenador só pode aprovar vínculos do próprio projeto', function () {
    $usuario = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $outroUsuario = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create();

    $vinculo = UsuarioProjeto::factory()->create([
        'usuario_id' => $outroUsuario->id,
        'projeto_id' => $projeto->id,
        'status' => StatusVinculoProjeto::PENDENTE,
    ]);

    $response = $this->actingAs($usuario)->put("/vinculos/{$vinculo->id}", [
        'status' => StatusVinculoProjeto::APROVADO->value
    ]);

    $response->assertForbidden();
});

// ==> Testes de Casos Extremos e Edge Cases

test('solicitação com projeto inexistente retorna erro de validação', function () {
    $usuario = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);

    $dadosInvalidos = [
        'projeto_id' => '99999999-9999-9999-9999-999999999999', // UUID que não existe
        'data_inicio' => now()->addDays(1)->format('Y-m-d'),
        'carga_horaria' => 20,
        'tipo_vinculo' => TipoVinculo::COLABORADOR->value,
        'funcao' => Funcao::DESENVOLVEDOR->value,
    ];

    $response = $this->actingAs($usuario)->post('/vinculo', $dadosInvalidos);

    $response->assertSessionHasErrors(['projeto_id']);
});

test('solicitação de vínculo sem campos obrigatórios é rejeitada', function () {
    $usuario = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);

    $response = $this->actingAs($usuario)->post('/vinculo', []);

    $response->assertSessionHasErrors([
        'projeto_id',
        'data_inicio',
        'carga_horaria',
        'tipo_vinculo',
        'funcao'
    ]);
});

test('solicitação de vínculo com dados inválidos retorna erros de validação', function () {
    $usuario = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create();

    $dadosInvalidos = [
        'projeto_id' => $projeto->id,
        'data_inicio' => 'data-invalida',
        'carga_horaria' => 'não-é-número',
        'tipo_vinculo' => 'TIPO_INEXISTENTE',
        'funcao' => 'FUNCAO_INEXISTENTE',
    ];

    $response = $this->actingAs($usuario)->post('/vinculo', $dadosInvalidos);

    $response->assertSessionHasErrors([
        'data_inicio',
        'carga_horaria',
        'tipo_vinculo',
        'funcao'
    ]);
});

test('solicitação com projeto inexistente é rejeitada quando não há projetos cadastrados', function () {
    // Limpar tabelas relacionadas
    UsuarioProjeto::query()->delete();
    Projeto::query()->delete();

    $usuario = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);

    $dadosVinculo = [
        'projeto_id' => '99999999-9999-9999-9999-999999999999',
        'data_inicio' => now()->addDays(1)->format('Y-m-d'),
        'carga_horaria' => 20,
        'tipo_vinculo' => TipoVinculo::COLABORADOR->value,
        'funcao' => Funcao::DESENVOLVEDOR->value,
    ];

    $response = $this->actingAs($usuario)->post('/vinculo', $dadosVinculo);

    $response->assertSessionHasErrors(['projeto_id']);
});

// ==> Testes de Validação no Update

test('coordenador não pode atualizar vínculo com carga horária inválida', function () {
    // Criar coordenador que pode fazer updates
    $coordenador = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create();

    // Fazer o usuário ser coordenador do projeto
    UsuarioProjeto::factory()->create([
        'usuario_id' => $coordenador->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'funcao' => Funcao::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
        'carga_horaria' => 40,
        'data_inicio' => now()->subMonths(1),
    ]);

    // Criar usuário normal com vínculo pendente para ser atualizado
    $usuario = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $vinculo = UsuarioProjeto::factory()->create([
        'usuario_id' => $usuario->id,
        'projeto_id' => $projeto->id,
        'status' => StatusVinculoProjeto::PENDENTE,
    ]);

    // Teste com carga horária muito alta (acima do máximo de 40)
    $response = $this->actingAs($coordenador)->put("/vinculos/{$vinculo->id}", [
        'carga_horaria' => 50
    ]);

    $response->assertSessionHasErrors(['carga_horaria']);

    // Teste com carga horária zero (abaixo do mínimo de 1)
    $response = $this->actingAs($coordenador)->put("/vinculos/{$vinculo->id}", [
        'carga_horaria' => 0
    ]);

    $response->assertSessionHasErrors(['carga_horaria']);
});

test('coordenador não pode atualizar vínculo com enums inválidos', function () {
    // Criar coordenador que pode fazer updates
    $coordenador = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create();

    // Fazer o usuário ser coordenador do projeto
    UsuarioProjeto::factory()->create([
        'usuario_id' => $coordenador->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'funcao' => Funcao::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
        'carga_horaria' => 40,
        'data_inicio' => now()->subMonths(1),
    ]);

    // Criar usuário normal com vínculo pendente para ser atualizado
    $usuario = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $vinculo = UsuarioProjeto::factory()->create([
        'usuario_id' => $usuario->id,
        'projeto_id' => $projeto->id,
        'status' => StatusVinculoProjeto::PENDENTE,
    ]);

    $response = $this->actingAs($coordenador)->put("/vinculos/{$vinculo->id}", [
        'status' => 'STATUS_INEXISTENTE',
        'funcao' => 'FUNCAO_INEXISTENTE',
        'tipo_vinculo' => 'TIPO_INEXISTENTE'
    ]);

    $response->assertSessionHasErrors(['status', 'funcao', 'tipo_vinculo']);
});

test('coordenador não pode definir data fim anterior à data início no update', function () {
    // Criar coordenador que pode fazer updates
    $coordenador = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create();

    // Fazer o usuário ser coordenador do projeto
    UsuarioProjeto::factory()->create([
        'usuario_id' => $coordenador->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'funcao' => Funcao::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
        'carga_horaria' => 40,
        'data_inicio' => now()->subMonths(1),
    ]);

    $dataInicio = now()->addDays(10);
    $usuario = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $vinculo = UsuarioProjeto::factory()->create([
        'usuario_id' => $usuario->id,
        'projeto_id' => $projeto->id,
        'data_inicio' => $dataInicio,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    // Incluir data_inicio no request para que a validação funcione corretamente
    $response = $this->actingAs($coordenador)->put("/vinculos/{$vinculo->id}", [
        'data_inicio' => $dataInicio->format('Y-m-d'),
        'data_fim' => $dataInicio->subDays(5)->format('Y-m-d') // 5 dias antes da data de início
    ]);

    $response->assertSessionHasErrors(['data_fim']);
});

// ==> Testes de Transições de Status

test('coordenador pode aprovar vínculo pendente (PENDENTE → APROVADO)', function () {
    $coordenador = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $usuario = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create();

    // Criar vínculo do coordenador para ter permissão
    UsuarioProjeto::factory()->create([
        'usuario_id' => $coordenador->id,
        'projeto_id' => $projeto->id,
        'status' => StatusVinculoProjeto::APROVADO,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'funcao' => Funcao::COORDENADOR,
    ]);

    // Criar vínculo pendente do usuário
    $vinculo = UsuarioProjeto::factory()->create([
        'usuario_id' => $usuario->id,
        'projeto_id' => $projeto->id,
        'status' => StatusVinculoProjeto::PENDENTE,
    ]);

    // Coordenador aprova vínculo (PENDENTE -> APROVADO)
    $response = $this->actingAs($coordenador)->put("/vinculos/{$vinculo->id}", [
        'status' => StatusVinculoProjeto::APROVADO->value
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('usuario_projeto', [
        'id' => $vinculo->id,
        'status' => StatusVinculoProjeto::APROVADO->value
    ]);
});

test('coordenador pode rejeitar vínculo pendente (PENDENTE → RECUSADO)', function () {
    $coordenador = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $usuario = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create();

    // Criar vínculo do coordenador para ter permissão
    UsuarioProjeto::factory()->create([
        'usuario_id' => $coordenador->id,
        'projeto_id' => $projeto->id,
        'status' => StatusVinculoProjeto::APROVADO,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'funcao' => Funcao::COORDENADOR,
    ]);

    // Criar vínculo pendente do usuário
    $vinculo = UsuarioProjeto::factory()->create([
        'usuario_id' => $usuario->id,
        'projeto_id' => $projeto->id,
        'status' => StatusVinculoProjeto::PENDENTE,
    ]);

    $response = $this->actingAs($coordenador)->put("/vinculos/{$vinculo->id}", [
        'status' => StatusVinculoProjeto::RECUSADO->value
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('usuario_projeto', [
        'id' => $vinculo->id,
        'status' => StatusVinculoProjeto::RECUSADO->value
    ]);
});
