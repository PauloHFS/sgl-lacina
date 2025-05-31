<?php

use App\Enums\StatusCadastro;
use App\Enums\TipoHorario;
use App\Models\User;
use App\Models\Horarios;
use Carbon\WeekDay;

test('usuário autenticado pode visualizar página de horários', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->get('/meus-horarios');

    $response->assertStatus(200);
    $response->assertInertia(
        fn($page) => $page
            ->component('Horarios/MeuHorario')
            ->has('auth.user')
    );
});

test('usuário não autenticado não pode acessar horários', function () {
    $response = $this->get('/meus-horarios');

    $response->assertRedirect('/login');
});

test('horário pode ser criado com dados válidos', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $horario = Horarios::factory()->create([
        'usuario_id' => $user->id,
        'dia_semana' => WeekDay::Monday,
        'tipo' => TipoHorario::AULA,
    ]);

    expect($horario->usuario_id)->toBe($user->id);
    expect($horario->dia_semana)->toBe(WeekDay::Monday);
    expect($horario->tipo)->toBe(TipoHorario::AULA);

    $this->assertDatabaseHas('horarios', [
        'id' => $horario->id,
        'usuario_id' => $user->id,
    ]);
});

test('horário deve ter relacionamento correto com usuário', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $horario = Horarios::factory()->create([
        'usuario_id' => $user->id,
    ]);

    expect($horario->usuario)->toBeInstanceOf(User::class);
    expect($horario->usuario->id)->toBe($user->id);
});

test('usuário pode ter múltiplos horários em dias diferentes', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $horarios = [
        Horarios::factory()->create([
            'usuario_id' => $user->id,
            'dia_semana' => WeekDay::Monday,
            'tipo' => TipoHorario::AULA,
        ]),
        Horarios::factory()->create([
            'usuario_id' => $user->id,
            'dia_semana' => WeekDay::Tuesday,
            'tipo' => TipoHorario::TRABALHO,
        ]),
        Horarios::factory()->create([
            'usuario_id' => $user->id,
            'dia_semana' => WeekDay::Wednesday,
            'tipo' => TipoHorario::AUSENTE,
        ]),
    ];

    expect($horarios)->toHaveCount(3);

    foreach ($horarios as $horario) {
        expect($horario->usuario_id)->toBe($user->id);
        $this->assertDatabaseHas('horarios', [
            'id' => $horario->id,
            'usuario_id' => $user->id,
        ]);
    }
});

test('horário deve validar tipos válidos', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $tiposValidos = [TipoHorario::AULA, TipoHorario::TRABALHO, TipoHorario::AUSENTE];

    foreach ($tiposValidos as $tipo) {
        $horario = Horarios::factory()->create([
            'usuario_id' => $user->id,
            'tipo' => $tipo,
        ]);

        expect($horario->tipo)->toBe($tipo);
    }
});

test('horário deve validar dias da semana válidos', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $diasValidos = [
        WeekDay::Monday,
        WeekDay::Tuesday,
        WeekDay::Wednesday,
        WeekDay::Thursday,
        WeekDay::Friday,
        WeekDay::Saturday,
        WeekDay::Sunday,
    ];

    foreach ($diasValidos as $dia) {
        $horario = Horarios::factory()->create([
            'usuario_id' => $user->id,
            'dia_semana' => $dia,
        ]);

        expect($horario->dia_semana)->toBe($dia);
    }
});

test('usuário pode ter horários sobrepostos em diferentes tipos', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $horario1 = Horarios::factory()->create([
        'usuario_id' => $user->id,
        'dia_semana' => WeekDay::Monday,
        'hora_inicio' => '08:00:00',
        'hora_fim' => '10:00:00',
        'tipo' => TipoHorario::AULA,
    ]);

    $horario2 = Horarios::factory()->create([
        'usuario_id' => $user->id,
        'dia_semana' => WeekDay::Monday,
        'hora_inicio' => '10:00:00',
        'hora_fim' => '12:00:00',
        'tipo' => TipoHorario::TRABALHO,
    ]);

    expect($horario1->usuario_id)->toBe($user->id);
    expect($horario2->usuario_id)->toBe($user->id);
    expect($horario1->dia_semana)->toBe($horario2->dia_semana);
    expect($horario1->tipo)->not->toBe($horario2->tipo);
});

test('horário mantém timestamps corretos', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $beforeCreation = now();

    $horario = Horarios::factory()->create([
        'usuario_id' => $user->id,
    ]);

    $afterCreation = now();

    expect($horario->created_at)->toBeGreaterThanOrEqual($beforeCreation);
    expect($horario->created_at)->toBeLessThanOrEqual($afterCreation);
    expect($horario->updated_at)->toBeGreaterThanOrEqual($beforeCreation);
    expect($horario->updated_at)->toBeLessThanOrEqual($afterCreation);
});

test('filtrar horários por usuário e dia', function () {
    $user1 = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $user2 = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    // Criar horários para user1
    Horarios::factory()->create([
        'usuario_id' => $user1->id,
        'dia_semana' => WeekDay::Monday,
        'tipo' => TipoHorario::AULA,
    ]);

    Horarios::factory()->create([
        'usuario_id' => $user1->id,
        'dia_semana' => WeekDay::Tuesday,
        'tipo' => TipoHorario::TRABALHO,
    ]);

    // Criar horário para user2
    Horarios::factory()->create([
        'usuario_id' => $user2->id,
        'dia_semana' => WeekDay::Monday,
        'tipo' => TipoHorario::AULA,
    ]);

    $horariosUser1Monday = Horarios::where('usuario_id', $user1->id)
        ->where('dia_semana', WeekDay::Monday)
        ->get();

    $horariosUser1All = Horarios::where('usuario_id', $user1->id)->get();

    expect($horariosUser1Monday)->toHaveCount(1);
    expect($horariosUser1All)->toHaveCount(2);
    expect($horariosUser1Monday->first()->tipo)->toBe(TipoHorario::AULA);
});

test('horário pode ser atualizado corretamente', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $horario = Horarios::factory()->create([
        'usuario_id' => $user->id,
        'tipo' => TipoHorario::AULA,
        'dia_semana' => WeekDay::Monday,
    ]);

    $originalUpdatedAt = $horario->updated_at;

    $horario->update([
        'tipo' => TipoHorario::TRABALHO,
        'dia_semana' => WeekDay::Tuesday,
    ]);

    $horario->refresh();

    expect($horario->tipo)->toBe(TipoHorario::TRABALHO);
    expect($horario->dia_semana)->toBe(WeekDay::Tuesday);
    expect($horario->updated_at)->toBeGreaterThan($originalUpdatedAt);
});

test('horário pode ser excluído corretamente', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $horario = Horarios::factory()->create([
        'usuario_id' => $user->id,
    ]);

    $horarioId = $horario->id;

    $horario->delete();

    $this->assertDatabaseMissing('horarios', [
        'id' => $horarioId,
    ]);
});

test('buscar horários por período específico', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $horarioManha = Horarios::factory()->create([
        'usuario_id' => $user->id,
        'hora_inicio' => '08:00:00',
        'hora_fim' => '12:00:00',
        'tipo' => TipoHorario::AULA,
    ]);

    $horarioTarde = Horarios::factory()->create([
        'usuario_id' => $user->id,
        'hora_inicio' => '14:00:00',
        'hora_fim' => '18:00:00',
        'tipo' => TipoHorario::TRABALHO,
    ]);

    $horariosManha = Horarios::where('usuario_id', $user->id)
        ->whereTime('hora_inicio', '<', '12:00:00')
        ->get();

    $horariosTarde = Horarios::where('usuario_id', $user->id)
        ->whereTime('hora_inicio', '>=', '12:00:00')
        ->get();

    expect($horariosManha)->toHaveCount(1);
    expect($horariosTarde)->toHaveCount(1);
    expect($horariosManha->first()->tipo)->toBe(TipoHorario::AULA);
    expect($horariosTarde->first()->tipo)->toBe(TipoHorario::TRABALHO);
});
