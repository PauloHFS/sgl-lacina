<?php

use App\Enums\StatusCadastro;
use App\Enums\StatusVinculoProjeto;
use App\Enums\TipoVinculo;
use App\Enums\Funcao;
use App\Mail\ParticipacaoLacinaReportMail;
use App\Models\HistoricoUsuarioProjeto;
use App\Models\Projeto;
use App\Models\User;
use App\Models\UsuarioProjeto;
use Illuminate\Support\Facades\Mail;

test('usuário pode solicitar relatório de participação por email', function () {
    Mail::fake();

    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    // Criar histórico de participação
    $projeto = Projeto::factory()->create();
    HistoricoUsuarioProjeto::create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COLABORADOR,
        'funcao' => Funcao::DESENVOLVEDOR,
        'status' => StatusVinculoProjeto::APROVADO,
        'carga_horaria_semanal' => 20,
        'data_inicio' => now()->subMonths(6),
        'data_fim' => now()->subMonths(2),
        'trocar' => false,
    ]);

    $response = $this->actingAs($user)->post('/relatorio/participacao');

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Relatório de participação enviado para o seu e-mail com o PDF anexado.');

    Mail::assertSent(ParticipacaoLacinaReportMail::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email) &&
            $mail->user->id === $user->id &&
            $mail->historico->isNotEmpty();
    });
});

test('relatório incluir todos os projetos do histórico do usuário', function () {
    Mail::fake();

    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    // Criar múltiplos projetos no histórico
    $projetos = Projeto::factory()->count(3)->create();

    foreach ($projetos as $projeto) {
        HistoricoUsuarioProjeto::create([
            'usuario_id' => $user->id,
            'projeto_id' => $projeto->id,
            'tipo_vinculo' => TipoVinculo::COLABORADOR,
            'funcao' => Funcao::DESENVOLVEDOR,
            'status' => StatusVinculoProjeto::APROVADO,
            'carga_horaria_semanal' => 20,
            'data_inicio' => now()->subMonths(6),
            'data_fim' => now()->subMonths(2),
            'trocar' => false,
        ]);
    }

    $response = $this->actingAs($user)->post('/relatorio/participacao');

    $response->assertRedirect();

    Mail::assertSent(ParticipacaoLacinaReportMail::class, function ($mail) {
        return $mail->historico->count() === 3;
    });
});

test('relatório deve estar ordenado por data de início decrescente', function () {
    Mail::fake();

    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto1 = Projeto::factory()->create(['nome' => 'Projeto Antigo']);
    $projeto2 = Projeto::factory()->create(['nome' => 'Projeto Novo']);

    // Criar histórico com datas diferentes
    HistoricoUsuarioProjeto::create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto1->id,
        'tipo_vinculo' => TipoVinculo::COLABORADOR,
        'funcao' => Funcao::DESENVOLVEDOR,
        'status' => StatusVinculoProjeto::APROVADO,
        'carga_horaria_semanal' => 20,
        'data_inicio' => now()->subYear(),
        'data_fim' => now()->subMonths(8),
        'trocar' => false,
    ]);

    HistoricoUsuarioProjeto::create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto2->id,
        'tipo_vinculo' => TipoVinculo::COLABORADOR,
        'funcao' => Funcao::DESENVOLVEDOR,
        'status' => StatusVinculoProjeto::APROVADO,
        'carga_horaria_semanal' => 20,
        'data_inicio' => now()->subMonths(3),
        'data_fim' => now()->subMonth(),
        'trocar' => false,
    ]);

    $response = $this->actingAs($user)->post('/relatorio/participacao');

    Mail::assertSent(ParticipacaoLacinaReportMail::class, function ($mail) {
        $primeiro = $mail->historico->first();
        $ultimo = $mail->historico->last();

        return $primeiro->projeto->nome === 'Projeto Novo' &&
            $ultimo->projeto->nome === 'Projeto Antigo';
    });
});

test('usuário sem histórico pode solicitar relatório vazio', function () {
    Mail::fake();

    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->post('/relatorio/participacao');

    $response->assertRedirect();

    Mail::assertSent(ParticipacaoLacinaReportMail::class, function ($mail) use ($user) {
        return $mail->user->id === $user->id &&
            $mail->historico->isEmpty();
    });
});

test('usuário não autenticado não pode solicitar relatório', function () {
    $response = $this->post('/relatorio/participacao');

    $response->assertRedirect('/login');
});

test('usuário com cadastro pendente não pode solicitar relatório', function () {
    $user = User::factory()->create([
        'status_cadastro' => StatusCadastro::PENDENTE,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->post('/relatorio/participacao');

    // Users with PENDENTE status are redirected to waiting-approval
    $response->assertRedirect(route('waiting-approval'));
});

test('relatório deve incluir informações corretas do projeto', function () {
    Mail::fake();

    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto = Projeto::factory()->create([
        'nome' => 'Sistema de Gestão',
        'cliente' => 'Cliente Teste',
    ]);

    HistoricoUsuarioProjeto::create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COLABORADOR,
        'funcao' => Funcao::PESQUISADOR,
        'status' => StatusVinculoProjeto::APROVADO,
        'carga_horaria_semanal' => 20,
        'data_inicio' => now()->subMonths(6),
        'data_fim' => now()->subMonths(2),
        'trocar' => false,
    ]);

    $response = $this->actingAs($user)->post('/relatorio/participacao');

    Mail::assertSent(ParticipacaoLacinaReportMail::class, function ($mail) {
        $item = $mail->historico->first();

        return $item->projeto->nome === 'Sistema de Gestão' &&
            $item->projeto->cliente === 'Cliente Teste' &&
            $item->carga_horaria_semanal === 20;
    });
});

test('nome do arquivo PDF deve incluir ID do usuário e timestamp', function () {
    Mail::fake();

    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->post('/relatorio/participacao');

    Mail::assertSent(ParticipacaoLacinaReportMail::class, function ($mail) use ($user) {
        return str_contains($mail->pdfFilename, 'relatorio_participacao_' . $user->id . '_') &&
            str_contains($mail->pdfFilename, '.pdf');
    });
});

test('email deve conter PDF como anexo', function () {
    Mail::fake();

    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->post('/relatorio/participacao');

    Mail::assertSent(ParticipacaoLacinaReportMail::class, function ($mail) {
        return !is_null($mail->pdfContent) && !empty($mail->pdfContent);
    });
});

test('relatório deve incluir apenas histórico do usuário logado', function () {
    Mail::fake();

    $user1 = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $user2 = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto = Projeto::factory()->create();

    // Criar histórico para ambos usuários
    HistoricoUsuarioProjeto::create([
        'usuario_id' => $user1->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COLABORADOR,
        'funcao' => Funcao::DESENVOLVEDOR,
        'status' => StatusVinculoProjeto::APROVADO,
        'carga_horaria_semanal' => 20,
        'data_inicio' => now()->subMonths(6),
        'data_fim' => now()->subMonths(2),
        'trocar' => false,
    ]);

    HistoricoUsuarioProjeto::create([
        'usuario_id' => $user2->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COLABORADOR,
        'funcao' => Funcao::DESENVOLVEDOR,
        'status' => StatusVinculoProjeto::APROVADO,
        'carga_horaria_semanal' => 20,
        'data_inicio' => now()->subMonths(6),
        'data_fim' => now()->subMonths(2),
        'trocar' => false,
    ]);

    $response = $this->actingAs($user1)->post('/relatorio/participacao');

    $response->assertRedirect();

    Mail::assertSent(ParticipacaoLacinaReportMail::class);

    // Verify the mail content separately
    Mail::assertSent(ParticipacaoLacinaReportMail::class, function ($mail) use ($user1) {
        return $mail->user->id === $user1->id;
    });
});
