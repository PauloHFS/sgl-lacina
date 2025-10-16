<?php

use App\Enums\Funcao;
use App\Enums\StatusVinculoProjeto;
use App\Enums\TipoVinculo;
use App\Models\HistoricoUsuarioProjeto;
use App\Models\Projeto;
use App\Models\User;
use App\Models\UsuarioProjeto;

test('observer registra histórico quando vínculo é criado aprovado', function () {
    $user = User::factory()->create();
    $projeto = Projeto::factory()->create();

    $vinculo = UsuarioProjeto::create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COLABORADOR,
        'funcao' => Funcao::DESENVOLVEDOR,
        'status' => StatusVinculoProjeto::APROVADO,
        'carga_horaria' => 80, // ou semanal dependendo da coluna
        'data_inicio' => now(),
    ]);

    // Verificar se histórico foi criado
    expect(HistoricoUsuarioProjeto::where('usuario_id', $user->id)->count())->toBe(1);

    $historico = HistoricoUsuarioProjeto::where('usuario_id', $user->id)->first();
    expect($historico->status)->toBe(StatusVinculoProjeto::APROVADO);
});

test('observer registra histórico quando status muda para aprovado', function () {
    $user = User::factory()->create();
    $projeto = Projeto::factory()->create();

    $vinculo = UsuarioProjeto::create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COLABORADOR,
        'funcao' => Funcao::DESENVOLVEDOR,
        'status' => StatusVinculoProjeto::PENDENTE,
        'carga_horaria' => 80,
        'data_inicio' => now(),
    ]);

    // Limpar qualquer histórico criado
    HistoricoUsuarioProjeto::where('usuario_id', $user->id)->delete();

    // Aprovar o vínculo
    $vinculo->update(['status' => StatusVinculoProjeto::APROVADO]);

    // Verificar se histórico foi criado
    expect(HistoricoUsuarioProjeto::where('usuario_id', $user->id)->count())->toBe(1);
});
