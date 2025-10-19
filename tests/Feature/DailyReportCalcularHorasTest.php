<?php

use App\Models\Horario;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('pode calcular horas trabalhadas baseado no horário cadastrado', function () {
    // Arrange
    $user = User::factory()->create();

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

    // Act
    $response = $this->actingAs($user)
        ->withoutMiddleware()
        ->postJson(route('daily-reports.calcular-horas'), [
            'data' => '2025-01-06', // Uma segunda-feira
        ]);

    // Assert
    $response
        ->assertStatus(200)
        ->assertJson([
            'horas_trabalhadas' => 2,
            'success' => true,
        ]);
});

test('retorna 0 horas quando usuário não tem horários cadastrados', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)
        ->withoutMiddleware()
        ->postJson(route('daily-reports.calcular-horas'), [
            'data' => '2025-01-06', // Uma segunda-feira
        ]);

    // Assert
    $response
        ->assertStatus(200)
        ->assertJson([
            'horas_trabalhadas' => 0,
            'success' => true,
        ]);
});

test('ignora horários que não são de trabalho', function () {
    // Arrange
    $user = User::factory()->create();

    // Criar horários variados para segunda-feira
    Horario::factory()->create([
        'usuario_id' => $user->id,
        'dia_da_semana' => 'SEGUNDA',
        'tipo' => 'TRABALHO_PRESENCIAL',
        'horario' => 8,
    ]);

    Horario::factory()->create([
        'usuario_id' => $user->id,
        'dia_da_semana' => 'SEGUNDA',
        'tipo' => 'EM_AULA', // Não deve ser contado
        'horario' => 10,
    ]);

    Horario::factory()->create([
        'usuario_id' => $user->id,
        'dia_da_semana' => 'SEGUNDA',
        'tipo' => 'AUSENTE', // Não deve ser contado
        'horario' => 16,
    ]);

    // Act
    $response = $this->actingAs($user)
        ->withoutMiddleware()
        ->postJson(route('daily-reports.calcular-horas'), [
            'data' => '2025-01-06', // Uma segunda-feira
        ]);

    // Assert
    $response
        ->assertStatus(200)
        ->assertJson([
            'horas_trabalhadas' => 1, // Apenas 1 hora de trabalho
            'success' => true,
        ]);
});

test('valida que data é obrigatória', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)
        ->withoutMiddleware()
        ->postJson(route('daily-reports.calcular-horas'), []);

    // Assert
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['data']);
});

test('calcula horas corretamente para diferentes dias da semana', function () {
    // Arrange
    $user = User::factory()->create();

    // Segunda-feira: 2 horas
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

    // Terça-feira: 1 hora
    Horario::factory()->create([
        'usuario_id' => $user->id,
        'dia_da_semana' => 'TERCA',
        'tipo' => 'TRABALHO_PRESENCIAL',
        'horario' => 9,
    ]);

    // Act & Assert - Segunda-feira
    $response = $this->actingAs($user)
        ->withoutMiddleware()
        ->postJson(route('daily-reports.calcular-horas'), [
            'data' => '2025-01-06', // Segunda-feira
        ]);

    $response->assertJson(['horas_trabalhadas' => 2]);

    // Act & Assert - Terça-feira
    $response = $this->actingAs($user)
        ->withoutMiddleware()
        ->postJson(route('daily-reports.calcular-horas'), [
            'data' => '2025-01-07', // Terça-feira
        ]);

    $response->assertJson(['horas_trabalhadas' => 1]);

    // Act & Assert - Quarta-feira (sem horários)
    $response = $this->actingAs($user)
        ->withoutMiddleware()
        ->postJson(route('daily-reports.calcular-horas'), [
            'data' => '2025-01-08', // Quarta-feira
        ]);

    $response->assertJson(['horas_trabalhadas' => 0]);
});
