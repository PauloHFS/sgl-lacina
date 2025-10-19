<?php

namespace App\Console\Commands;

use App\Enums\DiaDaSemana;
use App\Enums\TipoHorario;
use App\Models\Horario;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CriarHorariosUsuarios extends Command
{
    protected $signature = 'horarios:criar-usuarios {--force : Força recriação mesmo se já existirem}';

    protected $description = 'Cria horários completos para usuários que não possuem';

    public function handle()
    {
        $usuarios = User::whereDoesntHave('horarios')->get();

        if ($this->option('force')) {
            $usuarios = User::all();
            Horario::truncate();
        }

        $this->info("Criando horários para {$usuarios->count()} usuários...");

        $usuarios->each(function (User $user) {
            $this->criarHorariosCompletos($user);
            $this->line("✓ Horários criados para: {$user->name}");
        });

        $this->info('Concluído!');
    }

    private function criarHorariosCompletos(User $user): void
    {
        $horarios = [];

        foreach (DiaDaSemana::cases() as $dia) {
            for ($hora = 0; $hora < 24; $hora++) {
                $horarios[] = [
                    'id' => Str::uuid(),
                    'usuario_id' => $user->id,
                    'horario' => $hora,
                    'dia_da_semana' => $dia->value,
                    'tipo' => TipoHorario::AUSENTE->value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        Horario::insert($horarios);
    }
}
