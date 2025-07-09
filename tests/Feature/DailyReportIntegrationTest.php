<?php

use App\Enums\DiaDaSemana;
use App\Enums\StatusCadastro;
use App\Enums\TipoHorario;
use App\Models\Horario;
use App\Models\Projeto;
use App\Models\User;
use App\Models\UsuarioProjeto;
use App\Services\HorariosCacheService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('daily report create page recebe horas por dia da semana do cache', function () {
    // Criar usuário
    $user = User::factory()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
    ]);

    // Criar projeto
    $projeto = Projeto::factory()->create();

    // Criar vínculo do usuário com projeto
    $usuarioProjeto = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'status' => 'APROVADO',
    ]);

    // Criar horários de trabalho para o usuário
    $horarios = [
        ['dia_da_semana' => DiaDaSemana::SEGUNDA, 'horario' => 8, 'tipo' => TipoHorario::TRABALHO_PRESENCIAL],
        ['dia_da_semana' => DiaDaSemana::SEGUNDA, 'horario' => 9, 'tipo' => TipoHorario::TRABALHO_PRESENCIAL],
        ['dia_da_semana' => DiaDaSemana::SEGUNDA, 'horario' => 10, 'tipo' => TipoHorario::TRABALHO_PRESENCIAL],
        ['dia_da_semana' => DiaDaSemana::SEGUNDA, 'horario' => 11, 'tipo' => TipoHorario::TRABALHO_PRESENCIAL],
        ['dia_da_semana' => DiaDaSemana::TERCA, 'horario' => 14, 'tipo' => TipoHorario::TRABALHO_PRESENCIAL],
        ['dia_da_semana' => DiaDaSemana::TERCA, 'horario' => 15, 'tipo' => TipoHorario::TRABALHO_PRESENCIAL],
        ['dia_da_semana' => DiaDaSemana::SEXTA, 'horario' => 8, 'tipo' => TipoHorario::TRABALHO_PRESENCIAL],
        ['dia_da_semana' => DiaDaSemana::SEXTA, 'horario' => 9, 'tipo' => TipoHorario::TRABALHO_PRESENCIAL],
        ['dia_da_semana' => DiaDaSemana::SEXTA, 'horario' => 10, 'tipo' => TipoHorario::TRABALHO_PRESENCIAL],
        ['dia_da_semana' => DiaDaSemana::SEXTA, 'horario' => 11, 'tipo' => TipoHorario::TRABALHO_PRESENCIAL],
        ['dia_da_semana' => DiaDaSemana::SEXTA, 'horario' => 14, 'tipo' => TipoHorario::TRABALHO_PRESENCIAL],
        ['dia_da_semana' => DiaDaSemana::SEXTA, 'horario' => 15, 'tipo' => TipoHorario::TRABALHO_PRESENCIAL],
    ];

    foreach ($horarios as $horarioData) {
        Horario::create([
            'usuario_id' => $user->id,
            'usuario_projeto_id' => $usuarioProjeto->id,
            'dia_da_semana' => $horarioData['dia_da_semana'],
            'horario' => $horarioData['horario'],
            'tipo' => $horarioData['tipo'],
        ]);
    }

    // Fazer login como usuário
    $this->actingAs($user);

    // Acessar página de criação de daily report
    $response = $this->get(route('daily-reports.create'));

    $response->assertStatus(200)
        ->assertInertia(
            fn(Assert $page) => $page
                ->component('DailyReports/Create')
                ->has('horasPorProjetoPorDia')
                ->where('horasPorProjetoPorDia.' . $projeto->id . '.SEGUNDA', 4)  // 4 horas na segunda
                ->where('horasPorProjetoPorDia.' . $projeto->id . '.TERCA', 2)    // 2 horas na terça
                ->where('horasPorProjetoPorDia.' . $projeto->id . '.QUARTA', 0)   // 0 horas na quarta
                ->where('horasPorProjetoPorDia.' . $projeto->id . '.QUINTA', 0)   // 0 horas na quinta
                ->where('horasPorProjetoPorDia.' . $projeto->id . '.SEXTA', 6)    // 6 horas na sexta
                ->where('horasPorProjetoPorDia.' . $projeto->id . '.SABADO', 0)   // 0 horas no sábado
                ->where('horasPorProjetoPorDia.' . $projeto->id . '.DOMINGO', 0)  // 0 horas no domingo
        );
});

/*
test('daily report edit page recebe horas por dia da semana do cache', function () {
    // Criar usuário
    $user = User::factory()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
    ]);

    // Criar projeto
    $projeto = Projeto::factory()->create();

    // Criar vínculo do usuário com projeto
    $usuarioProjeto = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'status' => 'APROVADO',
    ]);

    // Criar horários de trabalho para o usuário (diferentes da create)
    $horarios = [
        ['dia_da_semana' => DiaDaSemana::SEGUNDA, 'horario' => 8, 'tipo' => TipoHorario::TRABALHO_PRESENCIAL],
        ['dia_da_semana' => DiaDaSemana::SEGUNDA, 'horario' => 9, 'tipo' => TipoHorario::TRABALHO_PRESENCIAL],
        ['dia_da_semana' => DiaDaSemana::QUINTA, 'horario' => 14, 'tipo' => TipoHorario::TRABALHO_PRESENCIAL],
        ['dia_da_semana' => DiaDaSemana::QUINTA, 'horario' => 15, 'tipo' => TipoHorario::TRABALHO_PRESENCIAL],
        ['dia_da_semana' => DiaDaSemana::QUINTA, 'horario' => 16, 'tipo' => TipoHorario::TRABALHO_PRESENCIAL],
    ];

    foreach ($horarios as $horarioData) {
        Horario::create([
            'usuario_id' => $user->id,
            'usuario_projeto_id' => $usuarioProjeto->id,
            'dia_da_semana' => $horarioData['dia_da_semana'],
            'horario' => $horarioData['horario'],
            'tipo' => $horarioData['tipo'],
        ]);
    }

    // Fazer login como usuário
    $this->actingAs($user);

    // Criar um daily report para editar
    $dailyReport = \App\Models\DailyReport::create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'data' => '2025-01-07', // Uma terça-feira
        'horas_trabalhadas' => 8,
        'o_que_fez_ontem' => 'Teste ontem',
        'o_que_vai_fazer_hoje' => 'Teste hoje',
        'observacoes' => 'Teste observações',
    ]);

    // Verificar se o daily report foi criado corretamente
    expect($dailyReport->usuario_id)->toBe($user->id);
    
    // Verificar se o usuário tem projetos ativos
    $projetosAtivos = \App\Models\UsuarioProjeto::with('projeto')
        ->where('usuario_id', $user->id)
        ->where('status', 'APROVADO')
        ->whereNull('data_fim')
        ->get();
    
    expect($projetosAtivos)->toHaveCount(1);

    // Acessar página de edição de daily report
    $response = $this->get(route('daily-reports.edit', $dailyReport));
    
    // Debug information
    if ($response->status() === 403) {
        $content = $response->getContent();
        $this->fail("403 error. User ID: {$user->id}, DailyReport user_id: {$dailyReport->usuario_id}, Project ID: {$projeto->id}. Content: " . $content);
    }

    $response->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('DailyReports/Edit')
            ->has('horasPorDiaDaSemana')
            ->where('horasPorDiaDaSemana.SEGUNDA', 2)   // 2 horas na segunda
            ->where('horasPorDiaDaSemana.TERCA', 0)     // 0 horas na terça
            ->where('horasPorDiaDaSemana.QUARTA', 0)    // 0 horas na quarta
            ->where('horasPorDiaDaSemana.QUINTA', 3)    // 3 horas na quinta
            ->where('horasPorDiaDaSemana.SEXTA', 0)     // 0 horas na sexta
            ->where('horasPorDiaDaSemana.SABADO', 0)    // 0 horas no sábado
            ->where('horasPorDiaDaSemana.DOMINGO', 0)   // 0 horas no domingo
        );
});
*/

test('cache é invalidado quando horário é alterado e daily report pages refletem mudança', function () {
    // Criar usuário
    $user = User::factory()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
    ]);

    // Criar projeto
    $projeto = Projeto::factory()->create();

    // Criar vínculo do usuário com projeto
    $usuarioProjeto = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'status' => 'APROVADO',
    ]);

    // Criar horário inicial (apenas segunda)
    $horario = Horario::create([
        'usuario_id' => $user->id,
        'usuario_projeto_id' => $usuarioProjeto->id,
        'dia_da_semana' => DiaDaSemana::SEGUNDA,
        'horario' => 8,
        'tipo' => TipoHorario::TRABALHO_PRESENCIAL,
    ]);

    // Fazer login como usuário
    $this->actingAs($user);

    // Acessar página de criação e verificar estado inicial
    $response1 = $this->get(route('daily-reports.create'));
    $response1->assertInertia(
        fn(Assert $page) => $page
            ->where('horasPorProjetoPorDia.' . $projeto->id . '.SEGUNDA', 1)
            ->where('horasPorProjetoPorDia.' . $projeto->id . '.TERCA', 0)
    );

    // Adicionar mais horários na segunda-feira
    Horario::create([
        'usuario_id' => $user->id,
        'usuario_projeto_id' => $usuarioProjeto->id,
        'dia_da_semana' => DiaDaSemana::SEGUNDA,
        'horario' => 9,
        'tipo' => TipoHorario::TRABALHO_PRESENCIAL,
    ]);

    // Adicionar horários na terça-feira
    Horario::create([
        'usuario_id' => $user->id,
        'usuario_projeto_id' => $usuarioProjeto->id,
        'dia_da_semana' => DiaDaSemana::TERCA,
        'horario' => 14,
        'tipo' => TipoHorario::TRABALHO_PRESENCIAL,
    ]);

    // Acessar página novamente e verificar que o cache foi invalidado e atualizado
    $response2 = $this->get(route('daily-reports.create'));
    $response2->assertInertia(
        fn(Assert $page) => $page
            ->where('horasPorProjetoPorDia.' . $projeto->id . '.SEGUNDA', 2)  // Agora 2 horas na segunda
            ->where('horasPorProjetoPorDia.' . $projeto->id . '.TERCA', 1)    // Agora 1 hora na terça
    );

    // Atualizar horário existente
    $horario->update(['tipo' => TipoHorario::AUSENTE]);

    // Verificar que a mudança foi refletida (segunda deve voltar para 1)
    $response3 = $this->get(route('daily-reports.create'));
    $response3->assertInertia(
        fn(Assert $page) => $page
            ->where('horasPorProjetoPorDia.' . $projeto->id . '.SEGUNDA', 1)  // Voltou para 1 hora na segunda
            ->where('horasPorProjetoPorDia.' . $projeto->id . '.TERCA', 1)    // Terça permanece 1 hora
    );

    // Remover horário da terça
    $horarioTerca = Horario::where('dia_da_semana', DiaDaSemana::TERCA)->first();
    $horarioTerca->delete();

    // Verificar que a remoção foi refletida
    $response4 = $this->get(route('daily-reports.create'));
    $response4->assertInertia(
        fn(Assert $page) => $page
            ->where('horasPorProjetoPorDia.' . $projeto->id . '.SEGUNDA', 1)  // Segunda permanece 1 hora
            ->where('horasPorProjetoPorDia.' . $projeto->id . '.TERCA', 0)    // Terça voltou para 0 horas
    );
});

test('service de cache funciona corretamente com múltiplos usuários', function () {
    // Criar dois usuários
    $user1 = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);
    $user2 = User::factory()->create(['status_cadastro' => StatusCadastro::ACEITO]);

    // Criar projeto
    $projeto = Projeto::factory()->create();

    // Criar vínculos
    $usuarioProjeto1 = UsuarioProjeto::factory()->create([
        'usuario_id' => $user1->id,
        'projeto_id' => $projeto->id,
        'status' => 'APROVADO',
    ]);

    $usuarioProjeto2 = UsuarioProjeto::factory()->create([
        'usuario_id' => $user2->id,
        'projeto_id' => $projeto->id,
        'status' => 'APROVADO',
    ]);

    // Criar horários diferentes para cada usuário
    // User1: 2 horas na segunda
    Horario::create([
        'usuario_id' => $user1->id,
        'usuario_projeto_id' => $usuarioProjeto1->id,
        'dia_da_semana' => DiaDaSemana::SEGUNDA,
        'horario' => 8,
        'tipo' => TipoHorario::TRABALHO_PRESENCIAL,
    ]);
    Horario::create([
        'usuario_id' => $user1->id,
        'usuario_projeto_id' => $usuarioProjeto1->id,
        'dia_da_semana' => DiaDaSemana::SEGUNDA,
        'horario' => 9,
        'tipo' => TipoHorario::TRABALHO_PRESENCIAL,
    ]);

    // User2: 3 horas na segunda
    Horario::create([
        'usuario_id' => $user2->id,
        'usuario_projeto_id' => $usuarioProjeto2->id,
        'dia_da_semana' => DiaDaSemana::SEGUNDA,
        'horario' => 14,
        'tipo' => TipoHorario::TRABALHO_PRESENCIAL,
    ]);
    Horario::create([
        'usuario_id' => $user2->id,
        'usuario_projeto_id' => $usuarioProjeto2->id,
        'dia_da_semana' => DiaDaSemana::SEGUNDA,
        'horario' => 15,
        'tipo' => TipoHorario::TRABALHO_PRESENCIAL,
    ]);
    Horario::create([
        'usuario_id' => $user2->id,
        'usuario_projeto_id' => $usuarioProjeto2->id,
        'dia_da_semana' => DiaDaSemana::SEGUNDA,
        'horario' => 16,
        'tipo' => TipoHorario::TRABALHO_PRESENCIAL,
    ]);

    // Testar como user1
    $this->actingAs($user1);
    $response1 = $this->get(route('daily-reports.create'));
    $response1->assertInertia(
        fn(Assert $page) => $page
            ->where('horasPorProjetoPorDia.' . $projeto->id . '.SEGUNDA', 2)
    );

    // Testar como user2
    $this->actingAs($user2);
    $response2 = $this->get(route('daily-reports.create'));
    $response2->assertInertia(
        fn(Assert $page) => $page
            ->where('horasPorProjetoPorDia.' . $projeto->id . '.SEGUNDA', 3)
    );
});
