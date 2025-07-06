<?php

namespace App\Services;

use App\Models\User;
use App\Models\Horario;
use App\Enums\DiaDaSemana;
use App\Enums\TipoHorario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HorarioService
{
    /**
     * Cria horários completos para o usuário se não existirem
     */
    public function criarHorariosParaUsuario(User $user): void
    {
        // Verifica se o usuário já possui horários
        if ($user->horarios()->exists()) {
            Log::info('Usuário já possui horários, pulando criação', [
                'user_id' => $user->id,
                'horarios_count' => $user->horarios()->count()
            ]);
            return;
        }

        DB::transaction(function () use ($user) {
            // Verifica novamente dentro da transação para evitar race conditions
            if ($user->horarios()->exists()) {
                return;
            }

            $this->criarHorariosCompletos($user);
        });

        Log::info('Horários criados com sucesso', [
            'user_id' => $user->id,
            'total_horarios' => count(DiaDaSemana::cases()) * 24
        ]);
    }

    /**
     * Cria todos os horários para o usuário (7 dias x 24 horas)
     */
    private function criarHorariosCompletos(User $user): void
    {
        $horarios = [];

        foreach (DiaDaSemana::cases() as $dia) {
            for ($hora = 0; $hora < 24; $hora++) {
                $horarios[] = [
                    'usuario_id' => $user->id,
                    'horario' => $hora,
                    'dia_da_semana' => $dia->value,
                    'tipo' => TipoHorario::AUSENTE->value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Insere em lotes para melhor performance
        $chunks = array_chunk($horarios, 50);

        foreach ($chunks as $chunk) {
            Horario::insert($chunk);
        }
    }
}
