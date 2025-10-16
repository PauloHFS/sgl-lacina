<?php

use App\Enums\StatusCadastro;
use App\Enums\StatusVinculoProjeto;
use App\Enums\TipoVinculo;
use App\Models\Projeto;
use App\Models\User;
use App\Models\UsuarioProjeto;

test('coordenador pode ver lista de colaboradores com cadastro pendente', function () {
    $coordenador = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto = Projeto::factory()->create();
    UsuarioProjeto::factory()->create([
        'usuario_id' => $coordenador->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    User::factory()->count(3)->create([
        'status_cadastro' => StatusCadastro::PENDENTE,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($coordenador)
        ->get('/colaborador?status=cadastro_pendente');

    $response->assertStatus(200);
    $response->assertJsonCount(3, 'usuarios');
});

test('coordenador pode aceitar cadastro de colaborador', function () {
    $coordenador = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto = Projeto::factory()->create();
    UsuarioProjeto::factory()->create([
        'usuario_id' => $coordenador->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    $colaborador = User::factory()->create([
        'status_cadastro' => StatusCadastro::PENDENTE,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($coordenador)
        ->patch("/colaborador/{$colaborador->id}/aceitar");

    $response->assertRedirect();

    $colaborador->refresh();
    expect($colaborador->status_cadastro)->toBe(StatusCadastro::ACEITO);
});

test('coordenador pode rejeitar cadastro de colaborador', function () {
    $coordenador = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto = Projeto::factory()->create();
    UsuarioProjeto::factory()->create([
        'usuario_id' => $coordenador->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    $colaborador = User::factory()->create([
        'status_cadastro' => StatusCadastro::PENDENTE,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($coordenador)
        ->patch("/colaborador/{$colaborador->id}/rejeitar");

    $response->assertRedirect();

    $colaborador->refresh();
    expect($colaborador->status_cadastro)->toBe(StatusCadastro::RECUSADO);
});

test('coordenador pode ver colaboradores com vínculos pendentes', function () {
    $coordenador = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto = Projeto::factory()->create();
    UsuarioProjeto::factory()->create([
        'usuario_id' => $coordenador->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    $colaborador = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    UsuarioProjeto::factory()->create([
        'usuario_id' => $colaborador->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COLABORADOR,
        'status' => StatusVinculoProjeto::PENDENTE,
    ]);

    $response = $this->actingAs($coordenador)
        ->get('/colaborador?status=vinculo_pendente');

    $response->assertStatus(200);
    $response->assertJsonCount(1, 'usuarios');
});

test('coordenador pode aceitar vínculo de colaborador', function () {
    $coordenador = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto = Projeto::factory()->create();
    UsuarioProjeto::factory()->create([
        'usuario_id' => $coordenador->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    $colaborador = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $vinculo = UsuarioProjeto::factory()->create([
        'usuario_id' => $colaborador->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COLABORADOR,
        'status' => StatusVinculoProjeto::PENDENTE,
    ]);

    $response = $this->actingAs($coordenador)
        ->patch("/colaborador/{$colaborador->id}/vinculo/{$vinculo->id}/aceitar");

    $response->assertRedirect();

    $vinculo->refresh();
    expect($vinculo->status)->toBe(StatusVinculoProjeto::APROVADO);
});

test('coordenador pode ver colaboradores APROVADOs', function () {
    $coordenador = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto = Projeto::factory()->create();
    UsuarioProjeto::factory()->create([
        'usuario_id' => $coordenador->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    $colaborador = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    UsuarioProjeto::factory()->create([
        'usuario_id' => $colaborador->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COLABORADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    $response = $this->actingAs($coordenador)
        ->get('/colaborador?status=APROVADOs');

    $response->assertStatus(200);
    $response->assertJsonCount(1, 'usuarios');
});

test('colaborador não pode acessar gestão de colaboradores', function () {
    $colaborador = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($colaborador)
        ->get('/colaborador?status=cadastro_pendente');

    $response->assertStatus(403);
});

test('coordenador pode editar dados de colaborador antes de aceitar', function () {
    $coordenador = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto = Projeto::factory()->create();
    UsuarioProjeto::factory()->create([
        'usuario_id' => $coordenador->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    $colaborador = User::factory()->create([
        'status_cadastro' => StatusCadastro::PENDENTE,
        'email_verified_at' => now(),
        'name' => 'Nome Original',
    ]);

    $dadosEditados = [
        'name' => 'Nome Editado',
        'email' => $colaborador->email,
        'area_atuacao' => 'Nova Área',
        'tecnologias' => 'Nova Tecnologia',
    ];

    $response = $this->actingAs($coordenador)
        ->patch("/colaborador/{$colaborador->id}/editar", $dadosEditados);

    $response->assertRedirect();

    $colaborador->refresh();
    expect($colaborador->name)->toBe('Nome Editado');
    expect($colaborador->area_atuacao)->toBe('Nova Área');
});

test('coordenador pode remover colaborador de projeto', function () {
    $coordenador = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto = Projeto::factory()->create();
    UsuarioProjeto::factory()->create([
        'usuario_id' => $coordenador->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    $colaborador = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $vinculo = UsuarioProjeto::factory()->create([
        'usuario_id' => $colaborador->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COLABORADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    $response = $this->actingAs($coordenador)
        ->delete("/colaborador/{$colaborador->id}/vinculo/{$vinculo->id}");

    $response->assertRedirect();

    $vinculo->refresh();
    expect($vinculo->status)->toBe(StatusVinculoProjeto::RECUSADO);
});
