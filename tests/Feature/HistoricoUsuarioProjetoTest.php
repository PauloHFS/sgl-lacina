<?php

use App\Enums\Funcao;
use App\Enums\StatusCadastro;
use App\Enums\StatusVinculoProjeto;
use App\Enums\TipoVinculo;
use App\Models\HistoricoUsuarioProjeto;
use App\Models\Projeto;
use App\Models\User;
use App\Models\UsuarioProjeto;

test('histórico é criado quando vínculo é aprovado', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $coordenador = User::factory()->coordenador()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto = Projeto::factory()->create();

    $vinculo = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'status' => StatusVinculoProjeto::PENDENTE,
        'tipo_vinculo' => TipoVinculo::COLABORADOR,
        'funcao' => Funcao::DESENVOLVEDOR,
        'carga_horaria' => 20,
        'data_inicio' => now(),
    ]);

    // Aprovar vínculo
    $response = $this->actingAs($coordenador)
        ->patch("/projetos/{$projeto->id}/vinculos/{$vinculo->id}", [
            'status' => StatusVinculoProjeto::APROVADO->value,
            'tipo_vinculo' => TipoVinculo::COLABORADOR->value,
            'funcao' => Funcao::DESENVOLVEDOR->value,
            'carga_horaria' => 20,
            'data_inicio' => now()->format('Y-m-d'),
        ]);

    $response->assertRedirect();

    // Verificar se histórico foi criado
    $this->assertDatabaseHas('historico_usuario_projeto', [
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'status' => StatusVinculoProjeto::APROVADO->value,
        'tipo_vinculo' => TipoVinculo::COLABORADOR->value,
        'funcao' => Funcao::DESENVOLVEDOR->value,
        'carga_horaria' => 20,
    ]);
});

test('histórico preserva dados mesmo após exclusão do vínculo', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto = Projeto::factory()->create();

    // Criar histórico diretamente
    $historico = HistoricoUsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'status' => StatusVinculoProjeto::APROVADO,
        'tipo_vinculo' => TipoVinculo::COLABORADOR,
        'funcao' => Funcao::DESENVOLVEDOR,
        'data_inicio' => now()->subMonths(6),
        'data_fim' => now()->subMonths(2),
    ]);

    // Criar vínculo atual
    $vinculo = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    // Excluir vínculo
    $vinculo->delete();

    // Verificar que histórico permanece
    $this->assertDatabaseHas('historico_usuario_projeto', [
        'id' => $historico->id,
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
    ]);

    expect(HistoricoUsuarioProjeto::find($historico->id))->not->toBeNull();
});

test('histórico mantém relacionamento com projeto excluído', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto = Projeto::factory()->create(['nome' => 'Projeto Antigo']);

    $historico = HistoricoUsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'status' => StatusVinculoProjeto::APROVADO,
        'data_inicio' => now()->subYear(),
        'data_fim' => now()->subMonths(6),
    ]);

    // Soft delete do projeto
    $projeto->delete();

    // Verificar que relacionamento ainda funciona
    $historico->refresh();
    expect($historico->projeto)->not->toBeNull();
    expect($historico->projeto->nome)->toBe('Projeto Antigo');
});

test('histórico pode ser filtrado por usuário', function () {
    $user1 = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $user2 = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto = Projeto::factory()->create();

    // Histórico para user1
    HistoricoUsuarioProjeto::factory()->create([
        'usuario_id' => $user1->id,
        'projeto_id' => $projeto->id,
    ]);

    // Histórico para user2
    HistoricoUsuarioProjeto::factory()->create([
        'usuario_id' => $user2->id,
        'projeto_id' => $projeto->id,
    ]);

    $historicoUser1 = HistoricoUsuarioProjeto::where('usuario_id', $user1->id)->get();
    $historicoUser2 = HistoricoUsuarioProjeto::where('usuario_id', $user2->id)->get();

    expect($historicoUser1)->toHaveCount(1);
    expect($historicoUser2)->toHaveCount(1);
    expect($historicoUser1->first()->usuario_id)->toBe($user1->id);
    expect($historicoUser2->first()->usuario_id)->toBe($user2->id);
});

test('histórico pode ser ordenado por data de início', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto1 = Projeto::factory()->create(['nome' => 'Projeto Antigo']);
    $projeto2 = Projeto::factory()->create(['nome' => 'Projeto Novo']);

    // Criar histórico com datas diferentes
    HistoricoUsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto1->id,
        'data_inicio' => now()->subYear(),
    ]);

    HistoricoUsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto2->id,
        'data_inicio' => now()->subMonths(3),
    ]);

    $historico = HistoricoUsuarioProjeto::with('projeto')
        ->where('usuario_id', $user->id)
        ->orderBy('data_inicio', 'desc')
        ->get();

    expect($historico)->toHaveCount(2);
    expect($historico->first()->projeto->nome)->toBe('Projeto Novo');
    expect($historico->last()->projeto->nome)->toBe('Projeto Antigo');
});

test('histórico inclui informações completas do vínculo', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto = Projeto::factory()->create([
        'nome' => 'Sistema de Gestão',
        'cliente' => 'Cliente ABC',
    ]);

    $dataInicio = now()->subMonths(6);
    $dataFim = now()->subMonths(2);

    $historico = HistoricoUsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'status' => StatusVinculoProjeto::APROVADO,
        'tipo_vinculo' => TipoVinculo::COLABORADOR,
        'funcao' => Funcao::DESENVOLVEDOR,
        'carga_horaria' => 20,
        'data_inicio' => $dataInicio,
        'data_fim' => $dataFim,
        'trocar' => false,
    ]);

    expect($historico->usuario_id)->toBe($user->id);
    expect($historico->projeto_id)->toBe($projeto->id);
    expect($historico->status)->toBe(StatusVinculoProjeto::APROVADO);
    expect($historico->tipo_vinculo)->toBe(TipoVinculo::COLABORADOR);
    expect($historico->funcao)->toBe(Funcao::DESENVOLVEDOR);
    expect($historico->carga_horaria)->toBe(20);
    expect($historico->data_inicio->format('Y-m-d'))->toBe($dataInicio->format('Y-m-d'));
    expect($historico->data_fim->format('Y-m-d'))->toBe($dataFim->format('Y-m-d'));
    expect($historico->trocar)->toBeFalse();
});

test('histórico pode ter data_fim nula para vínculos ativos', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto = Projeto::factory()->create();

    $historico = HistoricoUsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'status' => StatusVinculoProjeto::APROVADO,
        'data_inicio' => now()->subMonths(3),
        'data_fim' => null,
    ]);

    expect($historico->data_fim)->toBeNull();
    expect($historico->data_inicio)->not->toBeNull();
});

test('histórico preserva flag trocar quando aplicável', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto = Projeto::factory()->create();

    $historico = HistoricoUsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'trocar' => true,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    expect($historico->trocar)->toBeTrue();
});

test('histórico suporta múltiplos vínculos do mesmo usuário em projetos diferentes', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto1 = Projeto::factory()->create(['nome' => 'Projeto Web']);
    $projeto2 = Projeto::factory()->create(['nome' => 'Projeto Mobile']);
    $projeto3 = Projeto::factory()->create(['nome' => 'Projeto API']);

    // Criar histórico para múltiplos projetos
    $historicos = collect([
        HistoricoUsuarioProjeto::factory()->create([
            'usuario_id' => $user->id,
            'projeto_id' => $projeto1->id,
            'funcao' => Funcao::DESENVOLVEDOR,
        ]),
        HistoricoUsuarioProjeto::factory()->create([
            'usuario_id' => $user->id,
            'projeto_id' => $projeto2->id,
            'funcao' => Funcao::TECNICO,
        ]),
        HistoricoUsuarioProjeto::factory()->create([
            'usuario_id' => $user->id,
            'projeto_id' => $projeto3->id,
            'funcao' => Funcao::PESQUISADOR,
        ]),
    ]);

    $totalHistorico = HistoricoUsuarioProjeto::where('usuario_id', $user->id)->count();
    expect($totalHistorico)->toBe(3);

    $funcoes = HistoricoUsuarioProjeto::where('usuario_id', $user->id)
        ->pluck('funcao')
        ->toArray();

    expect($funcoes)->toContain(Funcao::DESENVOLVEDOR);
    expect($funcoes)->toContain(Funcao::TECNICO);
    expect($funcoes)->toContain(Funcao::PESQUISADOR);
});

test('histórico mantém timestamps de criação e atualização', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto = Projeto::factory()->create();

    $beforeCreation = now();

    $historico = HistoricoUsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
    ]);

    $afterCreation = now();

    expect($historico->created_at)->toBeGreaterThanOrEqual($beforeCreation);
    expect($historico->created_at)->toBeLessThanOrEqual($afterCreation);
    expect($historico->updated_at)->toBeGreaterThanOrEqual($beforeCreation);
    expect($historico->updated_at)->toBeLessThanOrEqual($afterCreation);
});
