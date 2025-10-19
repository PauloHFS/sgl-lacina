<?php

use App\Models\Horario;
use App\Models\User;
use App\Services\HorariosCacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

test('pode calcular e cachear horas por dia da semana', function () {
    // Arrange
    $user = User::factory()->create();
    $service = app(HorariosCacheService::class);

    // Criar horários de trabalho para segunda-feira (2 horas)
    Horario::factory()->create([
        'usuario_id' => $user->id,
        'dia_da_semana' => 'SEGUNDA',
        'tipo' => 'TRABALHO_PRESENCIAL',
        'horario' => 8,
    ]);

    Horario::factory()->create([
        'usuario_id' => $user->id,
        'dia_da_semana' => 'SEGUNDA',
        'tipo' => 'TRABALHO_REMOTO',
        'horario' => 14,
    ]);

    // Terça-feira (1 hora)
    Horario::factory()->create([
        'usuario_id' => $user->id,
        'dia_da_semana' => 'TERCA',
        'tipo' => 'TRABALHO_PRESENCIAL',
        'horario' => 9,
    ]);

    // Act
    $horas = $service->getHorasPorDiaDaSemana($user);

    // Assert
    expect($horas)->toBeArray()
        ->and($horas['SEGUNDA'])->toBe(2)
        ->and($horas['TERCA'])->toBe(1)
        ->and($horas['QUARTA'])->toBe(0)
        ->and($horas['QUINTA'])->toBe(0)
        ->and($horas['SEXTA'])->toBe(0)
        ->and($horas['SABADO'])->toBe(0)
        ->and($horas['DOMINGO'])->toBe(0);
});

test('cache é invalidado quando horário é alterado', function () {
    // Arrange
    $user = User::factory()->create();
    $service = app(HorariosCacheService::class);

    // Criar horário inicial
    $horario = Horario::factory()->create([
        'usuario_id' => $user->id,
        'dia_da_semana' => 'SEGUNDA',
        'tipo' => 'TRABALHO_PRESENCIAL',
        'horario' => 8,
    ]);

    // Act - Primeira busca (deve cachear)
    $horasInicial = $service->getHorasPorDiaDaSemana($user);

    // Verificar se está no cache
    $cacheKey = 'horarios_usuario_'.$user->id;
    expect(Cache::has($cacheKey))->toBeTrue();

    // Criar novo horário (deve invalidar cache)
    Horario::factory()->create([
        'usuario_id' => $user->id,
        'dia_da_semana' => 'SEGUNDA',
        'tipo' => 'TRABALHO_REMOTO',
        'horario' => 14,
    ]);

    // Verificar se cache foi invalidado
    expect(Cache::has($cacheKey))->toBeFalse();

    // Segunda busca (deve recalcular)
    $horasDepois = $service->getHorasPorDiaDaSemana($user);

    // Assert
    expect($horasInicial['SEGUNDA'])->toBe(1)
        ->and($horasDepois['SEGUNDA'])->toBe(2);
});

test('pode obter horas para data específica', function () {
    // Arrange
    $user = User::factory()->create();
    $service = app(HorariosCacheService::class);

    // Criar horários para segunda-feira
    Horario::factory()->create([
        'usuario_id' => $user->id,
        'dia_da_semana' => 'SEGUNDA',
        'tipo' => 'TRABALHO_PRESENCIAL',
        'horario' => 8,
    ]);

    Horario::factory()->create([
        'usuario_id' => $user->id,
        'dia_da_semana' => 'SEGUNDA',
        'tipo' => 'TRABALHO_REMOTO',
        'horario' => 14,
    ]);

    // Act & Assert
    $horas = $service->getHorasParaData($user, '2025-01-06'); // Segunda-feira
    expect($horas)->toBe(2);

    $horas = $service->getHorasParaData($user, '2025-01-07'); // Terça-feira
    expect($horas)->toBe(0);
});
