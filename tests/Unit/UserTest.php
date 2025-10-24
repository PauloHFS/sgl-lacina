<?php

use App\Models\User;
use App\Models\Banco;
use App\Models\Projeto;
use App\Models\UsuarioProjeto;
use App\Enums\StatusCadastro;
use App\Enums\Genero;
use App\Enums\TipoVinculo;
use App\Enums\StatusVinculoProjeto;

test('user pode ser criado com dados básicos', function () {
    $user = User::factory()->create([
        'name' => 'João Silva',
        'email' => 'joao@exemplo.com',
        'status_cadastro' => StatusCadastro::PENDENTE,
    ]);

    expect($user->name)->toBe('João Silva');
    expect($user->email)->toBe('joao@exemplo.com');
    expect($user->status_cadastro)->toBe(StatusCadastro::PENDENTE);
    expect($user->id)->toBeString();
});

test('user pode ter campos extras em JSONB', function () {
    $user = User::factory()->create();

    $user->setCampoExtra('Matricula', '2021123456');
    $user->setCampoExtra('Chave Dell', 'DELL123');
    $user->save();

    expect($user->getCampoExtra('Matricula'))->toBe('2021123456');
    expect($user->getCampoExtra('Chave Dell'))->toBe('DELL123');
    expect($user->hasCampoExtra('Matricula'))->toBeTrue();
    expect($user->hasCampoExtra('Campo Inexistente'))->toBeFalse();
});

test('user pode remover campos extras', function () {
    $user = User::factory()->create();
    $user->setCampoExtra('Teste', 'Valor');
    $user->save();

    expect($user->hasCampoExtra('Teste'))->toBeTrue();

    $user->removeCampoExtra('Teste');
    $user->save();

    expect($user->hasCampoExtra('Teste'))->toBeFalse();
});

test('user pode fazer merge de campos extras', function () {
    $user = User::factory()->create();
    $user->setCampoExtra('Campo1', 'Valor1');
    $user->save();

    $user->mergeCamposExtra([
        'Campo2' => 'Valor2',
        'Campo3' => 'Valor3',
    ]);
    $user->save();

    expect($user->getCampoExtra('Campo1'))->toBe('Valor1');
    expect($user->getCampoExtra('Campo2'))->toBe('Valor2');
    expect($user->getCampoExtra('Campo3'))->toBe('Valor3');
});

test('user pertence a um banco', function () {
    $banco = Banco::factory()->create();
    $user = User::factory()->create(['banco_id' => $banco->id]);

    expect($user->banco)->toBeInstanceOf(Banco::class);
    expect($user->banco->id)->toBe($banco->id);
});

test('user pode ter múltiplos vínculos com projetos', function () {
    $user = User::factory()->create();
    $projeto1 = Projeto::factory()->create();
    $projeto2 = Projeto::factory()->create();

    UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto1->id,
        'tipo_vinculo' => TipoVinculo::COLABORADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto2->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    expect($user->vinculos)->toHaveCount(2);
    expect($user->projetos)->toHaveCount(2);
});

test('user pode verificar se é coordenador de um projeto', function () {
    $user = User::factory()->create();
    $projeto = Projeto::factory()->create();

    UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    expect($user->isCoordenador($projeto))->toBeTrue();
});

test('user pode verificar se é colaborador de um projeto', function () {
    $user = User::factory()->create();
    $projeto = Projeto::factory()->create();

    UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COLABORADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    expect($user->isColaborador($projeto))->toBeTrue();
});

test('user pode verificar se tem vínculo pendente com projeto', function () {
    $user = User::factory()->create();
    $projeto = Projeto::factory()->create();

    UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COLABORADOR,
        'status' => StatusVinculoProjeto::PENDENTE,
    ]);

    expect($user->isVinculoProjetoPendente($projeto))->toBeTrue();
});

test('user com gênero deve ser castado corretamente', function () {
    $user = User::factory()->create(['genero' => Genero::MASCULINO]);

    expect($user->genero)->toBeInstanceOf(Genero::class);
    expect($user->genero)->toBe(Genero::MASCULINO);
});

test('user com status cadastro deve ser castado corretamente', function () {
    $user = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);

    expect($user->status_cadastro)->toBeInstanceOf(StatusCadastro::class);
    expect($user->status_cadastro)->toBe(StatusCadastro::ACEITO);
});

test('user deve retornar chave como string', function () {
    $user = User::factory()->create();

    expect($user->getKey())->toBeString();
});
