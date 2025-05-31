<?php

use App\Models\Projeto;
use App\Models\User;
use App\Models\UsuarioProjeto;
use App\Enums\TipoProjeto;
use App\Enums\TipoVinculo;
use App\Enums\StatusVinculoProjeto;

test('projeto pode ser criado com dados obrigatórios', function () {
    $projeto = Projeto::factory()->create([
        'nome' => 'Sistema de Gestão',
        'cliente' => 'Empresa XYZ',
        'data_inicio' => '2024-01-01',
        'tipo' => TipoProjeto::PDI,
    ]);

    expect($projeto->nome)->toBe('Sistema de Gestão');
    expect($projeto->cliente)->toBe('Empresa XYZ');
    expect($projeto->data_inicio->format('Y-m-d'))->toBe('2024-01-01');
    expect($projeto->tipo)->toBe(TipoProjeto::PDI);
    expect($projeto->id)->toBeString();
});

test('projeto pode ter data de término opcional', function () {
    $projeto = Projeto::factory()->create([
        'data_termino' => '2024-12-31',
    ]);

    expect($projeto->data_termino->format('Y-m-d'))->toBe('2024-12-31');

    $projetoSemTermino = Projeto::factory()->create([
        'data_termino' => null,
    ]);

    expect($projetoSemTermino->data_termino)->toBeNull();
});

test('projeto pode ter URLs opcionais', function () {
    $projeto = Projeto::factory()->create([
        'slack_url' => 'https://slack.com/workspace',
        'discord_url' => 'https://discord.gg/abc123',
        'board_url' => 'https://trello.com/board',
        'git_url' => 'https://github.com/usuario/projeto',
    ]);

    expect($projeto->slack_url)->toBe('https://slack.com/workspace');
    expect($projeto->discord_url)->toBe('https://discord.gg/abc123');
    expect($projeto->board_url)->toBe('https://trello.com/board');
    expect($projeto->git_url)->toBe('https://github.com/usuario/projeto');
});

test('projeto tem relacionamento many-to-many com usuários', function () {
    $projeto = Projeto::factory()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    UsuarioProjeto::factory()->create([
        'projeto_id' => $projeto->id,
        'usuario_id' => $user1->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
    ]);

    UsuarioProjeto::factory()->create([
        'projeto_id' => $projeto->id,
        'usuario_id' => $user2->id,
        'tipo_vinculo' => TipoVinculo::COLABORADOR,
    ]);

    expect($projeto->usuarios)->toHaveCount(2);
    expect($projeto->usuarios->first())->toBeInstanceOf(User::class);
});

test('projeto pode obter vínculo específico de um usuário', function () {
    $projeto = Projeto::factory()->create();
    $user = User::factory()->create();

    $vinculo = UsuarioProjeto::factory()->create([
        'projeto_id' => $projeto->id,
        'usuario_id' => $user->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    $vinculoEncontrado = $projeto->getUsuarioVinculo($user->id);

    expect($vinculoEncontrado)->not->toBeNull();
    expect($vinculoEncontrado->tipo_vinculo)->toBe(TipoVinculo::COORDENADOR);
    expect($vinculoEncontrado->status)->toBe(StatusVinculoProjeto::APROVADO);
});

test('projeto retorna null quando usuário não tem vínculo', function () {
    $projeto = Projeto::factory()->create();
    $user = User::factory()->create();

    $vinculo = $projeto->getUsuarioVinculo($user->id);

    expect($vinculo)->toBeNull();
});

test('projeto tipos devem ser válidos', function () {
    foreach (TipoProjeto::cases() as $tipo) {
        $projeto = Projeto::factory()->create(['tipo' => $tipo]);
        expect($projeto->tipo)->toBe($tipo);
    }
});

test('projeto datas devem ser castadas corretamente', function () {
    $projeto = Projeto::factory()->create([
        'data_inicio' => '2024-01-15',
        'data_termino' => '2024-12-20',
    ]);

    expect($projeto->data_inicio)->toBeInstanceOf(Carbon\Carbon::class);
    expect($projeto->data_termino)->toBeInstanceOf(Carbon\Carbon::class);
    expect($projeto->data_inicio->format('Y-m-d'))->toBe('2024-01-15');
    expect($projeto->data_termino->format('Y-m-d'))->toBe('2024-12-20');
});

test('projeto unique ids deve incluir id', function () {
    $projeto = new Projeto();

    expect($projeto->uniqueIds())->toContain('id');
});
