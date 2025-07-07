<?php

use App\Enums\DiaDaSemana;
use App\Enums\TipoHorario;
use App\Models\User;
use App\Services\HorarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->horarioService = app(HorarioService::class);
});

describe('HorarioService', function () {

    it('should create complete schedules for user', function () {
        expect($this->user->horarios()->count())->toBe(0);

        $this->horarioService->criarHorariosParaUsuario($this->user);

        $this->user->refresh();
        expect($this->user->horarios()->count())->toBe(168); // 7 dias * 24 horas
    });

    it('should create schedules with correct default values', function () {
        $this->horarioService->criarHorariosParaUsuario($this->user);

        $horario = $this->user->horarios()->first();

        expect($horario->usuario_id)->toBe($this->user->id);
        expect($horario->tipo)->toBe(TipoHorario::AUSENTE);
        expect($horario->horario)->toBeGreaterThanOrEqual(0);
        expect($horario->horario)->toBeLessThan(24);
        expect($horario->dia_da_semana)->toBeInstanceOf(DiaDaSemana::class);
    });

    it('should create schedules for all days of week', function () {
        $this->horarioService->criarHorariosParaUsuario($this->user);

        foreach (DiaDaSemana::cases() as $dia) {
            $count = $this->user->horarios()->where('dia_da_semana', $dia->value)->count();
            expect($count)->toBe(24, "Deveria ter 24 horários para {$dia->value}");
        }
    });

    it('should create schedules for all hours of day', function () {
        $this->horarioService->criarHorariosParaUsuario($this->user);

        for ($hora = 0; $hora < 24; $hora++) {
            $count = $this->user->horarios()->where('horario', $hora)->count();
            expect($count)->toBe(7, "Deveria ter 7 horários para hora {$hora}");
        }
    });

    it('should not create schedules if user already has them', function () {
        // Criar um horário manualmente
        $this->user->horarios()->create([
            'horario' => 9,
            'dia_da_semana' => DiaDaSemana::SEGUNDA,
            'tipo' => TipoHorario::TRABALHO_PRESENCIAL
        ]);

        $this->horarioService->criarHorariosParaUsuario($this->user);

        expect($this->user->horarios()->count())->toBe(1);
    });

    it('should use database-generated UUIDs', function () {
        $this->horarioService->criarHorariosParaUsuario($this->user);

        $horarios = $this->user->horarios()->get();

        foreach ($horarios as $horario) {
            expect($horario->id)->toBeString();
            expect(strlen($horario->id))->toBe(36); // UUID format
        }
    });

    it('should have proper timestamps', function () {
        $this->horarioService->criarHorariosParaUsuario($this->user);

        $horario = $this->user->horarios()->first();

        expect($horario->created_at)->not->toBeNull();
        expect($horario->updated_at)->not->toBeNull();
    });
});
