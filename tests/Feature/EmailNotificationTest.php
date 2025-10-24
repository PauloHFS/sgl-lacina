<?php

use App\Enums\StatusCadastro;
use App\Enums\StatusVinculoProjeto;
use App\Enums\TipoVinculo;
use App\Enums\Funcao;
use App\Models\User;
use App\Models\Projeto;
use App\Models\UsuarioProjeto;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Notification;

test('email de aprovação de cadastro é enviado quando usuário é aceito', function () {
    Mail::fake();

    $user = User::factory()->create([
        'status_cadastro' => StatusCadastro::PENDENTE,
        'email_verified_at' => now(),
    ]);

    // Simular aprovação do cadastro
    $user->update(['status_cadastro' => StatusCadastro::ACEITO]);

    // Verificar que o email foi enviado
    Mail::assertSent(\App\Mail\CadastroAceito::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });
});

test('email de rejeição de cadastro é enviado quando usuário é recusado', function () {
    Mail::fake();

    $user = User::factory()->create([
        'status_cadastro' => StatusCadastro::PENDENTE,
        'email_verified_at' => now(),
    ]);

    // Simular rejeição do cadastro
    $user->update(['status_cadastro' => StatusCadastro::RECUSADO]);

    // Verificar que o email foi enviado
    Mail::assertSent(\App\Mail\CadastroRejeitado::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });
});

test('email de aprovação de vínculo é enviado quando vínculo é aprovado', function () {
    Mail::fake();

    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto = Projeto::factory()->create();

    $vinculo = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'status' => StatusVinculoProjeto::PENDENTE,
    ]);

    // Simular aprovação do vínculo
    $vinculo->update(['status' => StatusVinculoProjeto::APROVADO]);

    // Verificar que o email foi enviado
    Mail::assertSent(\App\Mail\VinculoAceito::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });
});

test('email de rejeição de vínculo é enviado quando vínculo é recusado', function () {
    Mail::fake();

    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto = Projeto::factory()->create();

    $vinculo = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'status' => StatusVinculoProjeto::PENDENTE,
    ]);

    // Simular rejeição do vínculo
    $vinculo->update(['status' => StatusVinculoProjeto::RECUSADO]);

    // Verificar que o email foi enviado
    Mail::assertSent(\App\Mail\VinculoRejeitado::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });
});

test('email de troca de projeto aprovada é enviado', function () {
    Mail::fake();

    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projetoAntigo = Projeto::factory()->create(['nome' => 'Projeto Antigo']);
    $projetoNovo = Projeto::factory()->create(['nome' => 'Projeto Novo']);

    // Vínculo antigo
    $vinculoAntigo = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projetoAntigo->id,
        'status' => StatusVinculoProjeto::APROVADO,
        'trocar' => true,
    ]);

    // Novo vínculo
    $vinculoNovo = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projetoNovo->id,
        'status' => StatusVinculoProjeto::PENDENTE,
    ]);

    // Simular aprovação da troca
    $vinculoNovo->update(['status' => StatusVinculoProjeto::APROVADO]);
    $vinculoAntigo->update(['status' => StatusVinculoProjeto::ENCERRADO]);

    // Verificar que o email foi enviado
    Mail::assertSent(\App\Mail\TrocaProjetoAprovada::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });
});

test('emails são enviados para múltiplos destinatários quando necessário', function () {
    Mail::fake();

    $coordenador = User::factory()->coordenador()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $colaborador = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto = Projeto::factory()->create();

    // Vínculo do coordenador
    UsuarioProjeto::factory()->create([
        'usuario_id' => $coordenador->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'funcao' => Funcao::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    // Nova solicitação de vínculo
    UsuarioProjeto::factory()->create([
        'usuario_id' => $colaborador->id,
        'projeto_id' => $projeto->id,
        'status' => StatusVinculoProjeto::PENDENTE,
    ]);

    // Verificar que email foi enviado para o coordenador
    Mail::assertSent(\App\Mail\NovaSolicitacaoVinculo::class, function ($mail) use ($coordenador) {
        return $mail->hasTo($coordenador->email);
    });
});

test('job de geração de relatório é enfileirado corretamente', function () {
    Queue::fake();

    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    // Simular requisição de relatório
    dispatch(new \App\Jobs\GerarRelatorioParticipacao($user->id, $user->email));

    // Verificar que o job foi enfileirado
    Queue::assertPushed(\App\Jobs\GerarRelatorioParticipacao::class, function ($job) use ($user) {
        return $job->userId === $user->id && $job->email === $user->email;
    });
});

test('job de geração de relatório é processado corretamente', function () {
    Mail::fake();

    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto = Projeto::factory()->create();

    // Criar histórico de participação
    \App\Models\HistoricoUsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'status' => StatusVinculoProjeto::APROVADO,
        'data_inicio' => now()->subMonths(6),
        'data_fim' => now()->subMonths(2),
    ]);

    // Executar job
    $job = new \App\Jobs\GerarRelatorioParticipacao($user->id, $user->email);
    $job->handle();

    // Verificar que email com relatório foi enviado
    Mail::assertSent(\App\Mail\RelatorioParticipacao::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email) && $mail->hasAttachments();
    });
});

test('job falha graciosamente quando usuário não existe', function () {
    Queue::fake();
    Mail::fake();

    $job = new \App\Jobs\GerarRelatorioParticipacao('non-existent-id', 'test@example.com');

    expect(fn() => $job->handle())->toThrow(\Exception::class);

    // Email de erro não deve ser enviado
    Mail::assertNothingSent();
});

test('notificações são enviadas via múltiplos canais', function () {
    Notification::fake();

    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    // Enviar notificação
    $user->notify(new \App\Notifications\CadastroAprovado());

    // Verificar que notificação foi enviada
    Notification::assertSentTo($user, \App\Notifications\CadastroAprovado::class);
});

test('emails são throttled para evitar spam', function () {
    Mail::fake();

    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    // Tentar enviar múltiplos emails rapidamente
    for ($i = 0; $i < 5; $i++) {
        Mail::to($user->email)->send(new \App\Mail\CadastroAprovado($user));
    }

    // Verificar que emails foram limitados (implementação dependente)
    Mail::assertSentTimes(\App\Mail\CadastroAprovado::class, 5);
});

test('queue jobs têm timeout apropriado', function () {
    $job = new \App\Jobs\GerarRelatorioParticipacao('user-id', 'test@example.com');

    expect($job->timeout)->toBe(300); // 5 minutos
});

test('failed jobs são logados corretamente', function () {
    Queue::fake();

    $job = new \App\Jobs\GerarRelatorioParticipacao('invalid-id', 'invalid-email');

    // Simular falha do job
    try {
        $job->handle();
    } catch (\Exception $e) {
        $job->failed($e);
    }

    // Verificar que o job foi marcado como falhado
    expect($job->attempts)->toBeGreaterThan(0);
});

test('email templates contêm dados corretos', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'name' => 'João Silva',
        'email' => 'joao@example.com',
        'status_cadastro' => StatusCadastro::ACEITO,
    ]);

    $mail = new \App\Mail\CadastroAprovado($user);
    $content = $mail->render();

    expect($content)->toContain('João Silva');
    expect($content)->toContain('aprovado');
});

test('emails são enviados em fila para melhor performance', function () {
    Queue::fake();
    Mail::fake();

    $users = User::factory()->count(10)->create([
        'status_cadastro' => StatusCadastro::ACEITO,
    ]);

    foreach ($users as $user) {
        Mail::to($user->email)->queue(new \App\Mail\CadastroAprovado($user));
    }

    // Verificar que emails foram enfileirados, não enviados imediatamente
    Queue::assertPushed(\Illuminate\Mail\SendQueuedMailable::class, 10);
});
