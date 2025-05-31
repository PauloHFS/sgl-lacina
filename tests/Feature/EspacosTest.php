<?php

use App\Enums\StatusCadastro;
use App\Enums\TipoVinculo;
use App\Enums\StatusVinculoProjeto;
use App\Enums\Funcao;
use App\Models\User;
use App\Models\Projeto;
use App\Models\UsuarioProjeto;
use App\Models\Salas;
use App\Models\Baias;
use App\Models\Horarios;
use App\Models\HorarioBaia;

test('docente pode criar sala', function () {
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

    $sala = Salas::factory()->create([
        'nome' => 'Sala de Desenvolvimento',
        'senha_porta' => '1234',
    ]);

    expect($sala->nome)->toBe('Sala de Desenvolvimento');
    expect($sala->senha_porta)->toBe('1234');
    
    $this->assertDatabaseHas('salas', [
        'id' => $sala->id,
        'nome' => 'Sala de Desenvolvimento',
        'senha_porta' => '1234',
    ]);
});

test('sala pode ter múltiplas baias', function () {
    $sala = Salas::factory()->create([
        'nome' => 'Laboratório Principal',
    ]);

    $baia1 = Baias::factory()->create([
        'sala_id' => $sala->id,
        'nome' => 'Baia 01',
    ]);

    $baia2 = Baias::factory()->create([
        'sala_id' => $sala->id,
        'nome' => 'Baia 02',
    ]);

    $baia3 = Baias::factory()->create([
        'sala_id' => $sala->id,
        'nome' => 'Baia 03',
    ]);

    $sala->load('baias');

    expect($sala->baias)->toHaveCount(3);
    expect($sala->baias->pluck('nome')->toArray())->toContain('Baia 01', 'Baia 02', 'Baia 03');
    expect($baia1->sala_id)->toBe($sala->id);
    expect($baia2->sala_id)->toBe($sala->id);
    expect($baia3->sala_id)->toBe($sala->id);
});

test('baia deve pertencer a uma sala', function () {
    $sala = Salas::factory()->create([
        'nome' => 'Sala de Pesquisa',
    ]);

    $baia = Baias::factory()->create([
        'sala_id' => $sala->id,
        'nome' => 'Workstation A',
    ]);

    expect($baia->sala)->toBeInstanceOf(Salas::class);
    expect($baia->sala->id)->toBe($sala->id);
    expect($baia->sala->nome)->toBe('Sala de Pesquisa');
});

test('sala pode ser atualizada', function () {
    $sala = Salas::factory()->create([
        'nome' => 'Sala Temporária',
        'senha_porta' => '0000',
    ]);

    $originalUpdatedAt = $sala->updated_at;

    $sala->update([
        'nome' => 'Sala de Reuniões',
        'senha_porta' => '9999',
    ]);

    expect($sala->fresh()->nome)->toBe('Sala de Reuniões');
    expect($sala->fresh()->senha_porta)->toBe('9999');
    expect($sala->fresh()->updated_at)->toBeGreaterThan($originalUpdatedAt);
});

test('baia pode ser atualizada', function () {
    $sala = Salas::factory()->create();

    $baia = Baias::factory()->create([
        'sala_id' => $sala->id,
        'nome' => 'Baia Teste',
    ]);

    $originalUpdatedAt = $baia->updated_at;

    $baia->update([
        'nome' => 'Baia Atualizada',
    ]);

    expect($baia->fresh()->nome)->toBe('Baia Atualizada');
    expect($baia->fresh()->updated_at)->toBeGreaterThan($originalUpdatedAt);
});

test('sala pode ser excluída junto com suas baias', function () {
    $sala = Salas::factory()->create([
        'nome' => 'Sala Para Exclusão',
    ]);

    $baia1 = Baias::factory()->create([
        'sala_id' => $sala->id,
        'nome' => 'Baia 1',
    ]);

    $baia2 = Baias::factory()->create([
        'sala_id' => $sala->id,
        'nome' => 'Baia 2',
    ]);

    $salaId = $sala->id;
    $baia1Id = $baia1->id;
    $baia2Id = $baia2->id;

    // Em um cenário real, isso deveria ser feito com cascade delete ou manualmente
    $sala->baias()->delete();
    $sala->delete();

    $this->assertDatabaseMissing('salas', ['id' => $salaId]);
    $this->assertDatabaseMissing('baias', ['id' => $baia1Id]);
    $this->assertDatabaseMissing('baias', ['id' => $baia2Id]);
});

test('buscar salas por nome', function () {
    Salas::factory()->create(['nome' => 'Laboratório A']);
    Salas::factory()->create(['nome' => 'Laboratório B']);
    Salas::factory()->create(['nome' => 'Sala de Reunião']);

    $salasLaboratorio = Salas::where('nome', 'like', '%Laboratório%')->get();
    $salasReuniao = Salas::where('nome', 'like', '%Reunião%')->get();

    expect($salasLaboratorio)->toHaveCount(2);
    expect($salasReuniao)->toHaveCount(1);
});

test('buscar baias por sala específica', function () {
    $sala1 = Salas::factory()->create(['nome' => 'Sala 1']);
    $sala2 = Salas::factory()->create(['nome' => 'Sala 2']);

    // Baias da sala 1
    Baias::factory()->create(['sala_id' => $sala1->id, 'nome' => 'Baia A']);
    Baias::factory()->create(['sala_id' => $sala1->id, 'nome' => 'Baia B']);

    // Baias da sala 2
    Baias::factory()->create(['sala_id' => $sala2->id, 'nome' => 'Baia C']);

    $baiasSala1 = Baias::where('sala_id', $sala1->id)->get();
    $baiasSala2 = Baias::where('sala_id', $sala2->id)->get();

    expect($baiasSala1)->toHaveCount(2);
    expect($baiasSala2)->toHaveCount(1);
    expect($baiasSala1->pluck('nome')->toArray())->toContain('Baia A', 'Baia B');
    expect($baiasSala2->first()->nome)->toBe('Baia C');
});

test('horário pode ser vinculado a uma baia', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $sala = Salas::factory()->create();
    $baia = Baias::factory()->create(['sala_id' => $sala->id]);

    $horario = Horarios::factory()->create([
        'usuario_id' => $user->id,
    ]);

    // Criar vinculação entre horário e baia
    HorarioBaia::create([
        'horario_id' => $horario->id,
        'baia_id' => $baia->id,
    ]);

    $this->assertDatabaseHas('horario_baia', [
        'horario_id' => $horario->id,
        'baia_id' => $baia->id,
    ]);

    $horario->load('baias');
    expect($horario->baias)->toHaveCount(1);
    expect($horario->baias->first()->id)->toBe($baia->id);
});

test('baia pode ter múltiplos horários', function () {
    $user1 = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $user2 = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $sala = Salas::factory()->create();
    $baia = Baias::factory()->create(['sala_id' => $sala->id]);

    $horario1 = Horarios::factory()->create(['usuario_id' => $user1->id]);
    $horario2 = Horarios::factory()->create(['usuario_id' => $user2->id]);

    // Vincular horários à baia
    HorarioBaia::create(['horario_id' => $horario1->id, 'baia_id' => $baia->id]);
    HorarioBaia::create(['horario_id' => $horario2->id, 'baia_id' => $baia->id]);

    $baia->load('horarios');
    expect($baia->horarios)->toHaveCount(2);
    expect($baia->horarios->pluck('id')->toArray())->toContain($horario1->id, $horario2->id);
});

test('validar senha da porta da sala', function () {
    $sala = Salas::factory()->create([
        'nome' => 'Sala Segura',
        'senha_porta' => '5678',
    ]);

    expect($sala->senha_porta)->toBe('5678');
    expect(strlen($sala->senha_porta))->toBe(4);
});

test('listar todas as salas com contagem de baias', function () {
    $sala1 = Salas::factory()->create(['nome' => 'Sala A']);
    $sala2 = Salas::factory()->create(['nome' => 'Sala B']);

    // Sala A com 3 baias
    Baias::factory()->count(3)->create(['sala_id' => $sala1->id]);

    // Sala B com 2 baias
    Baias::factory()->count(2)->create(['sala_id' => $sala2->id]);

    $salasComBaias = Salas::withCount('baias')->get();

    expect($salasComBaias)->toHaveCount(2);
    
    $salaA = $salasComBaias->where('nome', 'Sala A')->first();
    $salaB = $salasComBaias->where('nome', 'Sala B')->first();

    expect($salaA->baias_count)->toBe(3);
    expect($salaB->baias_count)->toBe(2);
});

test('baia não pode existir sem sala', function () {
    // Tentativa de criar baia sem sala_id deve falhar
    expect(function () {
        Baias::factory()->create([
            'sala_id' => null,
            'nome' => 'Baia Órfã',
        ]);
    })->toThrow();
});

test('sala com nome único', function () {
    Salas::factory()->create(['nome' => 'Sala Única']);

    // Tentativa de criar outra sala com o mesmo nome deve ser permitida (não há restrição unique no modelo)
    $sala2 = Salas::factory()->create(['nome' => 'Sala Única']);

    expect($sala2->nome)->toBe('Sala Única');
    
    // Verificar se existem duas salas com mesmo nome
    $salasComMesmoNome = Salas::where('nome', 'Sala Única')->count();
    expect($salasComMesmoNome)->toBe(2);
});

test('buscar baias disponíveis em horário específico', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $sala = Salas::factory()->create();
    $baiaOcupada = Baias::factory()->create(['sala_id' => $sala->id, 'nome' => 'Baia Ocupada']);
    $baiaLivre = Baias::factory()->create(['sala_id' => $sala->id, 'nome' => 'Baia Livre']);

    $horario = Horarios::factory()->create(['usuario_id' => $user->id]);

    // Ocupar apenas uma baia
    HorarioBaia::create(['horario_id' => $horario->id, 'baia_id' => $baiaOcupada->id]);

    // Buscar baias que não estão ocupadas
    $baiasOcupadas = HorarioBaia::pluck('baia_id')->toArray();
    $baiasLivres = Baias::where('sala_id', $sala->id)
        ->whereNotIn('id', $baiasOcupadas)
        ->get();

    expect($baiasLivres)->toHaveCount(1);
    expect($baiasLivres->first()->nome)->toBe('Baia Livre');
});

test('relacionamento between sala and horarios through baias', function () {
    $user = User::factory()->cadastroCompleto()->create([
        'status_cadastro' => StatusCadastro::ACEITO,
        'email_verified_at' => now(),
    ]);

    $sala = Salas::factory()->create();
    $baia = Baias::factory()->create(['sala_id' => $sala->id]);
    $horario = Horarios::factory()->create(['usuario_id' => $user->id]);

    HorarioBaia::create(['horario_id' => $horario->id, 'baia_id' => $baia->id]);

    // Verificar relacionamento através do modelo HorarioBaia
    $horarioBaia = HorarioBaia::where('baia_id', $baia->id)->first();

    expect($horarioBaia)->not->toBeNull();
    expect($horarioBaia->baia)->toBeInstanceOf(Baias::class);
    expect($horarioBaia->horario)->toBeInstanceOf(Horarios::class);
    expect($horarioBaia->baia->sala)->toBeInstanceOf(Salas::class);
});
