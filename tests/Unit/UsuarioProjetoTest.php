<?php

use App\Models\UsuarioProjeto;
use App\Models\User;
use App\Models\Projeto;
use App\Enums\TipoVinculo;
use App\Enums\Funcao;
use App\Enums\StatusVinculoProjeto;

test('usuário projeto pode ser criado com dados obrigatórios', function () {
    $user = User::factory()->create();
    $projeto = Projeto::factory()->create();

    $vinculo = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COLABORADOR,
        'funcao' => Funcao::DESENVOLVEDOR,
        'status' => StatusVinculoProjeto::APROVADO,
        'carga_horaria' => 20,
        'data_inicio' => now(),
    ]);

    expect($vinculo->usuario_id)->toBe($user->id);
    expect($vinculo->projeto_id)->toBe($projeto->id);
    expect($vinculo->tipo_vinculo)->toBe(TipoVinculo::COLABORADOR);
    expect($vinculo->funcao)->toBe(Funcao::DESENVOLVEDOR);
    expect($vinculo->status)->toBe(StatusVinculoProjeto::APROVADO);
    expect($vinculo->carga_horaria)->toBe(20);
    expect($vinculo->trocar)->toBeFalse();
});

test('usuário projeto pertence a um usuário', function () {
    $user = User::factory()->create();
    $projeto = Projeto::factory()->create();

    $vinculo = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
    ]);

    expect($vinculo->usuario)->toBeInstanceOf(User::class);
    expect($vinculo->usuario->id)->toBe($user->id);
});

test('usuário projeto pertence a um projeto', function () {
    $user = User::factory()->create();
    $projeto = Projeto::factory()->create();

    $vinculo = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
    ]);

    expect($vinculo->projeto)->toBeInstanceOf(Projeto::class);
    expect($vinculo->projeto->id)->toBe($projeto->id);
});

test('usuário projeto pode ter flag trocar ativada', function () {
    $user = User::factory()->create();
    $projeto = Projeto::factory()->create();

    $vinculo = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'trocar' => true,
    ]);

    expect($vinculo->trocar)->toBeTrue();
});

test('usuário projeto pode ter data fim opcional', function () {
    $user = User::factory()->create();
    $projeto = Projeto::factory()->create();

    $vinculo = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'data_fim' => now()->addMonth(),
    ]);

    expect($vinculo->data_fim)->not->toBeNull();
    expect($vinculo->data_fim)->toBeInstanceOf(Carbon\Carbon::class);
});

test('usuário projeto enums devem ser castados corretamente', function () {
    $user = User::factory()->create();
    $projeto = Projeto::factory()->create();

    $vinculo = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'funcao' => Funcao::COORDENADOR,
        'status' => StatusVinculoProjeto::PENDENTE,
    ]);

    expect($vinculo->tipo_vinculo)->toBeInstanceOf(TipoVinculo::class);
    expect($vinculo->funcao)->toBeInstanceOf(Funcao::class);
    expect($vinculo->status)->toBeInstanceOf(StatusVinculoProjeto::class);
});

test('usuário projeto datas devem ser castadas corretamente', function () {
    $user = User::factory()->create();
    $projeto = Projeto::factory()->create();

    $vinculo = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'data_inicio' => '2024-01-15 10:00:00',
        'data_fim' => '2024-12-15 18:00:00',
    ]);

    expect($vinculo->data_inicio)->toBeInstanceOf(Carbon\Carbon::class);
    expect($vinculo->data_fim)->toBeInstanceOf(Carbon\Carbon::class);
});

test('usuário projeto deve usar UUID como chave primária', function () {
    $vinculo = UsuarioProjeto::factory()->create();

    expect($vinculo->getKeyType())->toBe('string');
    expect($vinculo->incrementing)->toBeFalse();
    expect(strlen($vinculo->getKey()))->toBe(36); // UUID length
});

test('todos os tipos de vínculo são válidos', function () {
    $user = User::factory()->create();
    $projeto = Projeto::factory()->create();

    foreach (TipoVinculo::cases() as $tipo) {
        $vinculo = UsuarioProjeto::factory()->create([
            'usuario_id' => $user->id,
            'projeto_id' => $projeto->id,
            'tipo_vinculo' => $tipo,
        ]);

        expect($vinculo->tipo_vinculo)->toBe($tipo);
    }
});

test('todas as funções são válidas', function () {
    $user = User::factory()->create();
    $projeto = Projeto::factory()->create();

    foreach (Funcao::cases() as $funcao) {
        $vinculo = UsuarioProjeto::factory()->create([
            'usuario_id' => $user->id,
            'projeto_id' => $projeto->id,
            'funcao' => $funcao,
        ]);

        expect($vinculo->funcao)->toBe($funcao);
    }
});

test('todos os status de vínculo são válidos', function () {
    $user = User::factory()->create();
    $projeto = Projeto::factory()->create();

    foreach (StatusVinculoProjeto::cases() as $status) {
        $vinculo = UsuarioProjeto::factory()->create([
            'usuario_id' => $user->id,
            'projeto_id' => $projeto->id,
            'status' => $status,
        ]);

        expect($vinculo->status)->toBe($status);
    }
});
