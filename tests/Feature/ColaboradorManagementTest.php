<?php

use App\Enums\StatusCadastro;
use App\Enums\StatusVinculoProjeto;
use App\Enums\TipoVinculo;
use App\Models\Banco;
use App\Models\Projeto;
use App\Models\User;
use App\Models\UsuarioProjeto;

test('coordenador pode visualizar lista de colaboradores sem vínculo', function () {
    $coordenador = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create();

    // Criar vínculo de coordenador
    UsuarioProjeto::factory()->create([
        'usuario_id' => $coordenador->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    // Usuário sem vínculo
    $usuarioSemVinculo = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);

    // Usuário com vínculo ativo
    $usuarioComVinculo = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    UsuarioProjeto::factory()->create([
        'usuario_id' => $usuarioComVinculo->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COLABORADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    $response = $this->actingAs($coordenador)->get('/colaboradores');

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($page) => $page->component('Colaboradores/Index')
    );
});

test('coordenador pode aceitar cadastro de colaborador', function () {
    $coordenador = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create();

    UsuarioProjeto::factory()->create([
        'usuario_id' => $coordenador->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    $colaborador = User::factory()->create(['status_cadastro' => StatusCadastro::PENDENTE]);

    $response = $this->actingAs($coordenador)
        ->post("/colaboradores/{$colaborador->id}/aceitar");

    $response->assertRedirect();

    $colaborador->refresh();
    expect($colaborador->status_cadastro)->toBe(StatusCadastro::ACEITO);
});

test('coordenador pode recusar cadastro de colaborador', function () {
    $coordenador = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create();

    UsuarioProjeto::factory()->create([
        'usuario_id' => $coordenador->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    $colaborador = User::factory()->create(['status_cadastro' => StatusCadastro::PENDENTE]);

    $response = $this->actingAs($coordenador)
        ->post("/colaboradores/{$colaborador->id}/recusar");

    $response->assertRedirect();

    $colaborador->refresh();
    expect($colaborador->status_cadastro)->toBe(StatusCadastro::RECUSADO);
});

test('coordenador pode atualizar dados de colaborador', function () {
    $coordenador = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create();

    UsuarioProjeto::factory()->create([
        'usuario_id' => $coordenador->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    $colaborador = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $banco = Banco::factory()->create();

    $dadosAtualizacao = [
        'name' => 'Nome Atualizado',
        'email' => 'novo@email.com',
        'cpf' => '12345678901',
        'rg' => '1234567',
        'uf_rg' => 'SP',
        'orgao_emissor_rg' => 'SSP',
        'telefone' => '11999999999',
        'banco_id' => $banco->id,
        'conta_bancaria' => '123456',
        'agencia' => '1234',
        'endereco' => 'Rua Nova, 123',
        'numero' => '123',
        'bairro' => 'Centro',
        'cidade' => 'São Paulo',
        'uf' => 'SP',
        'cep' => '01234567',
    ];

    $response = $this->actingAs($coordenador)
        ->put("/colaboradores/{$colaborador->id}", $dadosAtualizacao);

    $response->assertRedirect();

    $colaborador->refresh();
    expect($colaborador->name)->toBe('Nome Atualizado');
    expect($colaborador->email)->toBe('novo@email.com');
});

test('usuário sem privilégio de coordenador não pode acessar gestão de colaboradores', function () {
    $usuario = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);

    $response = $this->actingAs($usuario)->get('/colaboradores');

    $response->assertStatus(403);
});

test('coordenador pode visualizar detalhes de um colaborador específico', function () {
    $coordenador = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $projeto = Projeto::factory()->create();

    UsuarioProjeto::factory()->create([
        'usuario_id' => $coordenador->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    $colaborador = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $banco = Banco::factory()->create();
    $colaborador->update(['banco_id' => $banco->id]);

    $response = $this->actingAs($coordenador)->get("/colaboradores/{$colaborador->id}");

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($page) => $page->component('Colaboradores/Show')
            ->has('colaborador')
    );
});
