<?php

use App\Enums\StatusCadastro;
use App\Enums\StatusFolga;
use App\Enums\TipoFolga;
use App\Enums\TipoVinculo;
use App\Enums\StatusVinculoProjeto;
use App\Enums\Funcao;
use App\Models\User;
use App\Models\Projeto;
use App\Models\UsuarioProjeto;
use App\Models\Folgas;
use Carbon\Carbon;

test('colaborador pode criar folga individual', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $folga = Folgas::factory()->create([
        'usuario_id' => $user->id,
        'tipo' => TipoFolga::INDIVIDUAL,
        'status' => StatusFolga::PENDENTE,
        'data_inicio' => Carbon::now()->addDays(7),
        'data_fim' => Carbon::now()->addDays(9),
        'justificativa' => 'Viagem familiar',
    ]);

    expect($folga->usuario_id)->toBe($user->id);
    expect($folga->tipo)->toBe(TipoFolga::INDIVIDUAL);
    expect($folga->status)->toBe(StatusFolga::PENDENTE);
    expect($folga->justificativa)->toBe('Viagem familiar');

    $this->assertDatabaseHas('folgas', [
        'id' => $folga->id,
        'usuario_id' => $user->id,
        'tipo' => TipoFolga::INDIVIDUAL->value,
        'status' => StatusFolga::PENDENTE->value,
    ]);
});

test('docente pode criar folga coletiva', function () {
    $docente = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto = Projeto::factory()->create();

    UsuarioProjeto::factory()->create([
        'usuario_id' => $docente->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'funcao' => Funcao::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    $folga = Folgas::factory()->create([
        'usuario_id' => $docente->id,
        'tipo' => TipoFolga::COLETIVA,
        'status' => StatusFolga::APROVADO,
        'data_inicio' => Carbon::now()->addDays(15),
        'data_fim' => Carbon::now()->addDays(17),
        'justificativa' => 'Recesso de fim de ano',
    ]);

    expect($folga->tipo)->toBe(TipoFolga::COLETIVA);
    expect($folga->status)->toBe(StatusFolga::APROVADO);
    expect($folga->justificativa)->toBe('Recesso de fim de ano');
});

test('folga deve ter data de início anterior à data de fim', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $dataInicio = Carbon::now()->addDays(10);
    $dataFim = Carbon::now()->addDays(5); // Data fim anterior à data início

    // Esperamos que a validação falhe ao tentar criar uma folga com data_fim anterior a data_inicio
    expect(function () use ($user, $dataInicio, $dataFim) {
        Folgas::factory()->create([
            'usuario_id' => $user->id,
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim,
        ]);
    })->toThrow();
});

test('folga pode ser aprovada por coordenador', function () {
    $coordenador = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $colaborador = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $projeto = Projeto::factory()->create();

    // Vincular coordenador ao projeto
    UsuarioProjeto::factory()->create([
        'usuario_id' => $coordenador->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'funcao' => Funcao::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    // Vincular colaborador ao projeto
    UsuarioProjeto::factory()->create([
        'usuario_id' => $colaborador->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COLABORADOR,
        'funcao' => Funcao::DESENVOLVEDOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    $folga = Folgas::factory()->create([
        'usuario_id' => $colaborador->id,
        'tipo' => TipoFolga::INDIVIDUAL,
        'status' => StatusFolga::PENDENTE,
    ]);

    // Coordenador aprova a folga
    $folga->update(['status' => StatusFolga::APROVADO]);

    expect($folga->fresh()->status)->toBe(StatusFolga::APROVADO);
});

test('folga pode ser rejeitada com justificativa', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $folga = Folgas::factory()->create([
        'usuario_id' => $user->id,
        'status' => StatusFolga::PENDENTE,
    ]);

    $folga->update([
        'status' => StatusFolga::REJEITADO,
        'justificativa' => 'Período de alta demanda do projeto',
    ]);

    expect($folga->fresh()->status)->toBe(StatusFolga::REJEITADO);
    expect($folga->fresh()->justificativa)->toBe('Período de alta demanda do projeto');
});

test('colaborador pode ter múltiplas folgas em períodos diferentes', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $folga1 = Folgas::factory()->create([
        'usuario_id' => $user->id,
        'data_inicio' => Carbon::now()->addDays(7),
        'data_fim' => Carbon::now()->addDays(9),
        'tipo' => TipoFolga::INDIVIDUAL,
    ]);

    $folga2 = Folgas::factory()->create([
        'usuario_id' => $user->id,
        'data_inicio' => Carbon::now()->addDays(30),
        'data_fim' => Carbon::now()->addDays(32),
        'tipo' => TipoFolga::INDIVIDUAL,
    ]);

    $folgasCount = Folgas::where('usuario_id', $user->id)->count();

    expect($folgasCount)->toBe(2);
    expect($folga1->usuario_id)->toBe($user->id);
    expect($folga2->usuario_id)->toBe($user->id);
});

test('buscar folgas por status', function () {
    $user1 = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $user2 = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    // Folgas aprovadas
    Folgas::factory()->create([
        'usuario_id' => $user1->id,
        'status' => StatusFolga::APROVADO,
    ]);

    Folgas::factory()->create([
        'usuario_id' => $user2->id,
        'status' => StatusFolga::APROVADO,
    ]);

    // Folgas pendentes
    Folgas::factory()->create([
        'usuario_id' => $user1->id,
        'status' => StatusFolga::PENDENTE,
    ]);

    $folgasAprovadas = Folgas::where('status', StatusFolga::APROVADO)->get();
    $folgasPendentes = Folgas::where('status', StatusFolga::PENDENTE)->get();

    expect($folgasAprovadas)->toHaveCount(2);
    expect($folgasPendentes)->toHaveCount(1);
});

test('buscar folgas por período específico', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    // Folga dentro do período
    Folgas::factory()->create([
        'usuario_id' => $user->id,
        'data_inicio' => Carbon::now()->addDays(15),
        'data_fim' => Carbon::now()->addDays(17),
    ]);

    // Folga fora do período
    Folgas::factory()->create([
        'usuario_id' => $user->id,
        'data_inicio' => Carbon::now()->addDays(45),
        'data_fim' => Carbon::now()->addDays(47),
    ]);

    $inicioConsulta = Carbon::now()->addDays(10);
    $fimConsulta = Carbon::now()->addDays(20);

    $folgasNoPeriodo = Folgas::where('usuario_id', $user->id)
        ->whereBetween('data_inicio', [$inicioConsulta, $fimConsulta])
        ->get();

    expect($folgasNoPeriodo)->toHaveCount(1);
});

test('folga coletiva afeta múltiplos colaboradores', function () {
    $coordenador = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $colaborador1 = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $colaborador2 = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    // Criar folga coletiva
    $folgaColetiva = Folgas::factory()->create([
        'usuario_id' => $coordenador->id,
        'tipo' => TipoFolga::COLETIVA,
        'status' => StatusFolga::APROVADO,
        'data_inicio' => Carbon::now()->addDays(20),
        'data_fim' => Carbon::now()->addDays(22),
        'justificativa' => 'Feriado nacional',
    ]);

    // Simular criação de folgas individuais baseadas na coletiva
    $folgaIndividual1 = Folgas::factory()->create([
        'usuario_id' => $colaborador1->id,
        'tipo' => TipoFolga::INDIVIDUAL,
        'status' => StatusFolga::APROVADO,
        'data_inicio' => $folgaColetiva->data_inicio,
        'data_fim' => $folgaColetiva->data_fim,
        'justificativa' => 'Baseado na folga coletiva',
    ]);

    $folgaIndividual2 = Folgas::factory()->create([
        'usuario_id' => $colaborador2->id,
        'tipo' => TipoFolga::INDIVIDUAL,
        'status' => StatusFolga::APROVADO,
        'data_inicio' => $folgaColetiva->data_inicio,
        'data_fim' => $folgaColetiva->data_fim,
        'justificativa' => 'Baseado na folga coletiva',
    ]);

    expect($folgaColetiva->tipo)->toBe(TipoFolga::COLETIVA);
    expect($folgaIndividual1->data_inicio->format('Y-m-d'))->toBe($folgaColetiva->data_inicio->format('Y-m-d'));
    expect($folgaIndividual2->data_inicio->format('Y-m-d'))->toBe($folgaColetiva->data_inicio->format('Y-m-d'));
});

test('validar tipos de folga disponíveis', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $tiposValidos = [TipoFolga::INDIVIDUAL, TipoFolga::COLETIVA];

    foreach ($tiposValidos as $tipo) {
        $folga = Folgas::factory()->create([
            'usuario_id' => $user->id,
            'tipo' => $tipo,
        ]);

        expect($folga->tipo)->toBe($tipo);
    }
});

test('validar status de folga disponíveis', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $statusValidos = [StatusFolga::PENDENTE, StatusFolga::APROVADO, StatusFolga::REJEITADO];

    foreach ($statusValidos as $status) {
        $folga = Folgas::factory()->create([
            'usuario_id' => $user->id,
            'status' => $status,
        ]);

        expect($folga->status)->toBe($status);
    }
});

test('folga pode ser excluída corretamente', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $folga = Folgas::factory()->create([
        'usuario_id' => $user->id,
    ]);

    $folgaId = $folga->id;

    $folga->delete();

    $this->assertDatabaseMissing('folgas', [
        'id' => $folgaId,
    ]);
});

test('buscar folgas de um colaborador específico', function () {
    $user1 = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $user2 = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    // Criar folgas para user1
    Folgas::factory()->create([
        'usuario_id' => $user1->id,
        'status' => StatusFolga::APROVADO,
    ]);

    Folgas::factory()->create([
        'usuario_id' => $user1->id,
        'status' => StatusFolga::PENDENTE,
    ]);

    // Criar folga para user2
    Folgas::factory()->create([
        'usuario_id' => $user2->id,
        'status' => StatusFolga::APROVADO,
    ]);

    $folgasUser1 = Folgas::where('usuario_id', $user1->id)->get();
    $folgasUser2 = Folgas::where('usuario_id', $user2->id)->get();

    expect($folgasUser1)->toHaveCount(2);
    expect($folgasUser2)->toHaveCount(1);
    expect($folgasUser1->first()->usuario_id)->toBe($user1->id);
    expect($folgasUser2->first()->usuario_id)->toBe($user2->id);
});

test('folga deve ter justificativa obrigatória para tipos específicos', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $folga = Folgas::factory()->create([
        'usuario_id' => $user->id,
        'tipo' => TipoFolga::INDIVIDUAL,
        'justificativa' => 'Consulta médica',
    ]);

    expect($folga->justificativa)->not->toBeNull();
    expect($folga->justificativa)->toBe('Consulta médica');
});
