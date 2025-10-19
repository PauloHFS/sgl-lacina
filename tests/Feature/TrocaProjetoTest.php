<?php

use App\Enums\Funcao;
use App\Enums\StatusCadastro;
use App\Enums\StatusVinculoProjeto;
use App\Enums\TipoVinculo;
use App\Models\HistoricoUsuarioProjeto;
use App\Models\Projeto;
use App\Models\User;
use App\Models\UsuarioProjeto;
use Illuminate\Support\Facades\Mail;

test('usuário pode solicitar troca de projeto marcando trocar como true', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projetoAtual = Projeto::factory()->create(['nome' => 'Projeto Atual']);
    $projetoNovo = Projeto::factory()->create(['nome' => 'Projeto Novo']);

    // Vínculo atual ativo
    $vinculoAtual = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projetoAtual->id,
        'status' => StatusVinculoProjeto::APROVADO,
        'tipo_vinculo' => TipoVinculo::COLABORADOR,
        'funcao' => Funcao::DESENVOLVEDOR,
        'trocar' => false,
    ]);

    // Solicitar troca
    $response = $this->actingAs($user)->post('/vinculo', [
        'projeto_id' => $projetoNovo->id,
        'tipo_vinculo' => TipoVinculo::COLABORADOR->value,
        'funcao' => Funcao::DESENVOLVEDOR->value,
        'carga_horaria' => 20,
        'data_inicio' => now()->addWeek()->format('Y-m-d'),
        'trocar' => true,
        'usuario_projeto_trocado_id' => $vinculoAtual->id,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    // Verificar que vínculo atual foi marcado para troca
    $vinculoAtual->refresh();
    expect($vinculoAtual->trocar)->toBeTrue();

    // Verificar que novo vínculo foi criado com status pendente
    $this->assertDatabaseHas('usuario_projeto', [
        'usuario_id' => $user->id,
        'projeto_id' => $projetoNovo->id,
        'status' => StatusVinculoProjeto::PENDENTE->value,
    ]);
});

test('usuário não pode ter múltiplas solicitações de troca simultâneas', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto1 = Projeto::factory()->create();
    $projeto2 = Projeto::factory()->create();
    $projeto3 = Projeto::factory()->create();

    // Vínculo atual com troca já marcada
    $vinculo1 = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto1->id,
        'status' => StatusVinculoProjeto::APROVADO,
        'trocar' => true,
    ]);

    // Tentar nova solicitação de troca
    $response = $this->actingAs($user)->post('/vinculo', [
        'projeto_id' => $projeto3->id,
        'tipo_vinculo' => TipoVinculo::COLABORADOR->value,
        'funcao' => Funcao::DESENVOLVEDOR->value,
        'carga_horaria' => 20,
        'data_inicio' => now()->addWeek()->format('Y-m-d'),
        'trocar' => true,
        'usuario_projeto_trocado_id' => $vinculo1->id,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error', 'Você já possui uma troca em andamento.');

    // Verificar que nova solicitação não foi criada
    $this->assertDatabaseMissing('usuario_projeto', [
        'usuario_id' => $user->id,
        'projeto_id' => $projeto3->id,
    ]);
});

test('aprovação de troca encerra projeto antigo e ativa projeto novo', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $coordenador = User::factory()->coordenador()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projetoAntigo = Projeto::factory()->create(['nome' => 'Projeto Antigo']);
    $projetoNovo = Projeto::factory()->create(['nome' => 'Projeto Novo']);

    // Criar vínculo com coordenador no projeto novo
    UsuarioProjeto::factory()->create([
        'usuario_id' => $coordenador->id,
        'projeto_id' => $projetoNovo->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    // Vínculo antigo marcado para troca
    $vinculoAntigo = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projetoAntigo->id,
        'status' => StatusVinculoProjeto::APROVADO,
        'trocar' => true,
        'data_inicio' => now()->subMonths(6),
    ]);

    // Novo vínculo pendente
    $vinculoNovo = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projetoNovo->id,
        'status' => StatusVinculoProjeto::PENDENTE,
        'data_inicio' => now()->addWeek(),
    ]);

    // Coordenador aprova a troca
    $response = $this->actingAs($coordenador)
        ->patch("/projetos/{$projetoNovo->id}/vinculos/{$vinculoNovo->id}", [
            'status' => StatusVinculoProjeto::APROVADO->value,
        ]);

    $response->assertRedirect();

    // Verificar que vínculo antigo foi encerrado
    $vinculoAntigo->refresh();
    expect($vinculoAntigo->status)->toBe(StatusVinculoProjeto::ENCERRADO);
    expect($vinculoAntigo->trocar)->toBeFalse();
    expect($vinculoAntigo->data_fim)->not->toBeNull();

    // Verificar que novo vínculo foi aprovado
    $vinculoNovo->refresh();
    expect($vinculoNovo->status)->toBe(StatusVinculoProjeto::APROVADO);
});

test('rejeição de troca mantém projeto antigo ativo', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $coordenador = User::factory()->coordenador()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projetoAntigo = Projeto::factory()->create();
    $projetoNovo = Projeto::factory()->create();

    // Criar vínculo com coordenador
    UsuarioProjeto::factory()->create([
        'usuario_id' => $coordenador->id,
        'projeto_id' => $projetoNovo->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    // Vínculo antigo marcado para troca
    $vinculoAntigo = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projetoAntigo->id,
        'status' => StatusVinculoProjeto::APROVADO,
        'trocar' => true,
    ]);

    // Novo vínculo pendente
    $vinculoNovo = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projetoNovo->id,
        'status' => StatusVinculoProjeto::PENDENTE,
    ]);

    // Coordenador rejeita a troca
    $response = $this->actingAs($coordenador)
        ->patch("/projetos/{$projetoNovo->id}/vinculos/{$vinculoNovo->id}", [
            'status' => StatusVinculoProjeto::RECUSADO->value,
        ]);

    $response->assertRedirect();

    // Verificar que vínculo antigo permanece ativo e trocar foi resetado
    $vinculoAntigo->refresh();
    expect($vinculoAntigo->status)->toBe(StatusVinculoProjeto::APROVADO);
    expect($vinculoAntigo->trocar)->toBeFalse();
    expect($vinculoAntigo->data_fim)->toBeNull();

    // Verificar que novo vínculo foi rejeitado
    $vinculoNovo->refresh();
    expect($vinculoNovo->status)->toBe(StatusVinculoProjeto::RECUSADO);
});

test('histórico preserva informações de troca quando apropriado', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $coordenador = User::factory()->coordenador()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projetoAntigo = Projeto::factory()->create();
    $projetoNovo = Projeto::factory()->create();

    UsuarioProjeto::factory()->create([
        'usuario_id' => $coordenador->id,
        'projeto_id' => $projetoNovo->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    // Vínculo com troca
    $vinculoAntigo = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projetoAntigo->id,
        'status' => StatusVinculoProjeto::APROVADO,
        'trocar' => true,
        'funcao' => Funcao::DESENVOLVEDOR,
    ]);

    $vinculoNovo = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projetoNovo->id,
        'status' => StatusVinculoProjeto::PENDENTE,
        'funcao' => Funcao::TECNICO,
    ]);

    // Aprovar troca
    $this->actingAs($coordenador)
        ->patch("/projetos/{$projetoNovo->id}/vinculos/{$vinculoNovo->id}", [
            'status' => StatusVinculoProjeto::APROVADO->value,
        ]);

    // Verificar histórico foi criado com flag trocar preservada
    $this->assertDatabaseHas('historico_usuario_projeto', [
        'usuario_id' => $user->id,
        'projeto_id' => $projetoAntigo->id,
        'trocar' => true,
        'funcao' => Funcao::DESENVOLVEDOR->value,
        'status' => StatusVinculoProjeto::ENCERRADO->value,
    ]);

    $this->assertDatabaseHas('historico_usuario_projeto', [
        'usuario_id' => $user->id,
        'projeto_id' => $projetoNovo->id,
        'trocar' => false,
        'funcao' => Funcao::TECNICO->value,
        'status' => StatusVinculoProjeto::APROVADO->value,
    ]);
});

test('troca de projeto cria notificações por email', function () {
    Mail::fake();

    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $coordenador = User::factory()->coordenador()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projetoAntigo = Projeto::factory()->create(['nome' => 'Projeto Antigo']);
    $projetoNovo = Projeto::factory()->create(['nome' => 'Projeto Novo']);

    UsuarioProjeto::factory()->create([
        'usuario_id' => $coordenador->id,
        'projeto_id' => $projetoNovo->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    $vinculoAntigo = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projetoAntigo->id,
        'status' => StatusVinculoProjeto::APROVADO,
        'trocar' => true,
    ]);

    $vinculoNovo = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projetoNovo->id,
        'status' => StatusVinculoProjeto::PENDENTE,
    ]);

    // Aprovar troca
    $this->actingAs($coordenador)
        ->patch("/projetos/{$projetoNovo->id}/vinculos/{$vinculoNovo->id}", [
            'status' => StatusVinculoProjeto::APROVADO->value,
        ]);

    // Verificar que email de troca aprovada foi enviado
    Mail::assertSent(\App\Mail\VinculoAceito::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });
});

test('usuário não pode solicitar troca para projeto que já participa', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto = Projeto::factory()->create();

    // Vínculo atual ativo
    $vinculo = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    // Tentar solicitar troca para o mesmo projeto
    $response = $this->actingAs($user)->post('/vinculo', [
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COLABORADOR->value,
        'funcao' => Funcao::DESENVOLVEDOR->value,
        'carga_horaria' => 20,
        'data_inicio' => now()->addWeek()->format('Y-m-d'),
        'trocar' => true,
        'usuario_projeto_trocado_id' => $vinculo->id,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error', 'Você já possui solicitação ou vínculo ativo neste projeto.');
});

test('data_fim é definida automaticamente quando vínculo antigo é encerrado na troca', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $coordenador = User::factory()->coordenador()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projetoAntigo = Projeto::factory()->create();
    $projetoNovo = Projeto::factory()->create();

    UsuarioProjeto::factory()->create([
        'usuario_id' => $coordenador->id,
        'projeto_id' => $projetoNovo->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    $dataInicioNovo = now()->addWeek();

    $vinculoAntigo = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projetoAntigo->id,
        'status' => StatusVinculoProjeto::APROVADO,
        'trocar' => true,
        'data_inicio' => now()->subMonths(6),
        'data_fim' => null,
    ]);

    $vinculoNovo = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projetoNovo->id,
        'status' => StatusVinculoProjeto::PENDENTE,
        'data_inicio' => $dataInicioNovo,
    ]);

    // Aprovar troca
    $this->actingAs($coordenador)
        ->patch("/projetos/{$projetoNovo->id}/vinculos/{$vinculoNovo->id}", [
            'status' => StatusVinculoProjeto::APROVADO->value,
        ]);

    // Verificar que data_fim do vínculo antigo foi definida como data_inicio do novo
    $vinculoAntigo->refresh();
    expect($vinculoAntigo->data_fim)->not->toBeNull();
    expect($vinculoAntigo->data_fim->format('Y-m-d'))->toBe($dataInicioNovo->format('Y-m-d'));
});

test('múltiplas trocas sequenciais são processadas corretamente', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $coordenador = User::factory()->coordenador()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto1 = Projeto::factory()->create(['nome' => 'Projeto 1']);
    $projeto2 = Projeto::factory()->create(['nome' => 'Projeto 2']);
    $projeto3 = Projeto::factory()->create(['nome' => 'Projeto 3']);

    // Criar vínculos do coordenador
    UsuarioProjeto::factory()->create([
        'usuario_id' => $coordenador->id,
        'projeto_id' => $projeto2->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    UsuarioProjeto::factory()->create([
        'usuario_id' => $coordenador->id,
        'projeto_id' => $projeto3->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    // Primeira troca: Projeto1 -> Projeto2
    $vinculo1 = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto1->id,
        'status' => StatusVinculoProjeto::APROVADO,
        'data_inicio' => now()->subYear(),
    ]);

    // Solicitar primeira troca
    $this->actingAs($user)->post('/vinculo', [
        'projeto_id' => $projeto2->id,
        'tipo_vinculo' => TipoVinculo::COLABORADOR->value,
        'funcao' => Funcao::DESENVOLVEDOR->value,
        'carga_horaria' => 20,
        'data_inicio' => now()->addDay()->format('Y-m-d'),
        'trocar' => true,
        'usuario_projeto_trocado_id' => $vinculo1->id,
    ]);

    $vinculo2 = UsuarioProjeto::where('usuario_id', $user->id)
        ->where('projeto_id', $projeto2->id)
        ->first();

    // Aprovar primeira troca
    $this->actingAs($coordenador)
        ->patch("/projetos/{$projeto2->id}/vinculos/{$vinculo2->id}", [
            'status' => StatusVinculoProjeto::APROVADO->value,
        ]);

    // Aguardar alguns segundos e solicitar segunda troca: Projeto2 -> Projeto3
    $vinculo2->refresh();

    $this->actingAs($user)->post('/vinculo', [
        'projeto_id' => $projeto3->id,
        'tipo_vinculo' => TipoVinculo::COLABORADOR->value,
        'funcao' => Funcao::TECNICO->value,
        'carga_horaria' => 25,
        'data_inicio' => now()->addWeek()->format('Y-m-d'),
        'trocar' => true,
        'usuario_projeto_trocado_id' => $vinculo2->id,
    ]);

    $vinculo3 = UsuarioProjeto::where('usuario_id', $user->id)
        ->where('projeto_id', $projeto3->id)
        ->first();

    // Aprovar segunda troca
    $this->actingAs($coordenador)
        ->patch("/projetos/{$projeto3->id}/vinculos/{$vinculo3->id}", [
            'status' => StatusVinculoProjeto::APROVADO->value,
        ]);

    // Verificar estado final
    $vinculo1->refresh();
    $vinculo2->refresh();
    $vinculo3->refresh();

    expect($vinculo1->status)->toBe(StatusVinculoProjeto::ENCERRADO);
    expect($vinculo1->trocar)->toBeFalse();
    expect($vinculo1->data_fim)->not->toBeNull();

    expect($vinculo2->status)->toBe(StatusVinculoProjeto::ENCERRADO);
    expect($vinculo2->trocar)->toBeFalse();
    expect($vinculo2->data_fim)->not->toBeNull();

    expect($vinculo3->status)->toBe(StatusVinculoProjeto::APROVADO);
    expect($vinculo3->trocar)->toBeFalse();
    expect($vinculo3->data_fim)->toBeNull();

    // Verificar histórico completo
    $historicos = HistoricoUsuarioProjeto::where('usuario_id', $user->id)
        ->orderBy('data_inicio', 'asc')
        ->get();

    expect($historicos)->toHaveCount(3);
    expect($historicos[0]->projeto_id)->toBe($projeto1->id);
    expect($historicos[1]->projeto_id)->toBe($projeto2->id);
    expect($historicos[2]->projeto_id)->toBe($projeto3->id);
});

test('troca de projeto valida permissões de coordenação', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $coordenadorNaoAutorizado = User::factory()->coordenador()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projetoAntigo = Projeto::factory()->create();
    $projetoNovo = Projeto::factory()->create();

    // Coordenador não tem vínculo com projeto novo
    $vinculoAntigo = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projetoAntigo->id,
        'status' => StatusVinculoProjeto::APROVADO,
        'trocar' => true,
    ]);

    $vinculoNovo = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projetoNovo->id,
        'status' => StatusVinculoProjeto::PENDENTE,
    ]);

    // Tentar aprovar troca sem ser coordenador do projeto
    $response = $this->actingAs($coordenadorNaoAutorizado)
        ->patch("/projetos/{$projetoNovo->id}/vinculos/{$vinculoNovo->id}", [
            'status' => StatusVinculoProjeto::APROVADO->value,
        ]);

    $response->assertStatus(403);

    // Verificar que troca não foi processada
    $vinculoAntigo->refresh();
    $vinculoNovo->refresh();

    expect($vinculoAntigo->status)->toBe(StatusVinculoProjeto::APROVADO);
    expect($vinculoAntigo->trocar)->toBeTrue();
    expect($vinculoNovo->status)->toBe(StatusVinculoProjeto::PENDENTE);
});

test('troca com dados inválidos é rejeitada', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto = Projeto::factory()->create();

    $vinculo = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    // Tentar troca com projeto inexistente
    $response = $this->actingAs($user)->post('/vinculo', [
        'projeto_id' => 'inexistente',
        'tipo_vinculo' => TipoVinculo::COLABORADOR->value,
        'funcao' => Funcao::DESENVOLVEDOR->value,
        'carga_horaria' => 20,
        'data_inicio' => now()->addWeek()->format('Y-m-d'),
        'trocar' => true,
        'usuario_projeto_trocado_id' => $vinculo->id,
    ]);

    $response->assertSessionHasErrors(['projeto_id']);

    // Tentar troca com vínculo inexistente
    $response = $this->actingAs($user)->post('/vinculo', [
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COLABORADOR->value,
        'funcao' => Funcao::DESENVOLVEDOR->value,
        'carga_horaria' => 20,
        'data_inicio' => now()->addWeek()->format('Y-m-d'),
        'trocar' => true,
        'usuario_projeto_trocado_id' => 'inexistente',
    ]);

    $response->assertSessionHasErrors(['usuario_projeto_trocado_id']);
});

test('troca de projeto funciona com diferentes tipos de vínculo e função', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $coordenador = User::factory()->coordenador()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projetoAntigo = Projeto::factory()->create();
    $projetoNovo = Projeto::factory()->create();

    UsuarioProjeto::factory()->create([
        'usuario_id' => $coordenador->id,
        'projeto_id' => $projetoNovo->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    // Vínculo antigo como Aluno/Colaborador
    $vinculoAntigo = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projetoAntigo->id,
        'status' => StatusVinculoProjeto::APROVADO,
        'tipo_vinculo' => TipoVinculo::COLABORADOR,
        'funcao' => Funcao::ALUNO,
        'carga_horaria' => 15,
        'trocar' => true,
    ]);

    // Novo vínculo como Técnico/Colaborador
    $vinculoNovo = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projetoNovo->id,
        'status' => StatusVinculoProjeto::PENDENTE,
        'tipo_vinculo' => TipoVinculo::COLABORADOR,
        'funcao' => Funcao::TECNICO,
        'carga_horaria' => 30,
    ]);

    // Aprovar troca
    $this->actingAs($coordenador)
        ->patch("/projetos/{$projetoNovo->id}/vinculos/{$vinculoNovo->id}", [
            'status' => StatusVinculoProjeto::APROVADO->value,
        ]);

    // Verificar mudanças nos tipos e funções
    $vinculoAntigo->refresh();
    $vinculoNovo->refresh();

    expect($vinculoAntigo->status)->toBe(StatusVinculoProjeto::ENCERRADO);
    expect($vinculoAntigo->funcao)->toBe(Funcao::ALUNO);
    expect($vinculoAntigo->carga_horaria)->toBe(15);

    expect($vinculoNovo->status)->toBe(StatusVinculoProjeto::APROVADO);
    expect($vinculoNovo->funcao)->toBe(Funcao::TECNICO);
    expect($vinculoNovo->carga_horaria)->toBe(30);
});

test('sistema impede trocar para mesmo projeto através de vínculo diferente', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto = Projeto::factory()->create();

    // Vínculo atual
    $vinculo1 = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'status' => StatusVinculoProjeto::APROVADO,
        'funcao' => Funcao::ALUNO,
    ]);

    // Tentar criar "troca" para o mesmo projeto mas com função diferente
    $response = $this->actingAs($user)->post('/vinculo', [
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COLABORADOR->value,
        'funcao' => Funcao::DESENVOLVEDOR->value,
        'carga_horaria' => 20,
        'data_inicio' => now()->addWeek()->format('Y-m-d'),
        'trocar' => true,
        'usuario_projeto_trocado_id' => $vinculo1->id,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error', 'Você já possui solicitação ou vínculo ativo neste projeto.');

    // Verificar que nenhuma mudança foi feita
    $vinculo1->refresh();
    expect($vinculo1->trocar)->toBeFalse();
});

test('usuários com cadastro não aceito não podem solicitar troca', function () {
    $userPendente = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::PENDENTE,
        'email_verified_at' => now(),
    ]);

    $projeto1 = Projeto::factory()->create();
    $projeto2 = Projeto::factory()->create();

    $vinculo = UsuarioProjeto::factory()->create([
        'usuario_id' => $userPendente->id,
        'projeto_id' => $projeto1->id,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    $response = $this->actingAs($userPendente)->post('/vinculo', [
        'projeto_id' => $projeto2->id,
        'tipo_vinculo' => TipoVinculo::COLABORADOR->value,
        'funcao' => Funcao::DESENVOLVEDOR->value,
        'carga_horaria' => 20,
        'data_inicio' => now()->addWeek()->format('Y-m-d'),
        'trocar' => true,
        'usuario_projeto_trocado_id' => $vinculo->id,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error', 'Seu cadastro não está aceito. Entre em contato com o administrador.');
});
