<?php

namespace App\Observers;

use App\Models\Horario;
use App\Services\HorariosCacheService;
use Illuminate\Support\Facades\Log;

class HorarioObserver
{
    protected HorariosCacheService $horariosCacheService;

    public function __construct(HorariosCacheService $horariosCacheService)
    {
        $this->horariosCacheService = $horariosCacheService;
    }

    /**
     * Handle the Horario "created" event.
     */
    public function created(Horario $horario): void
    {
        $this->invalidarCacheUsuario($horario);
    }

    /**
     * Handle the Horario "updated" event.
     */
    public function updated(Horario $horario): void
    {
        $this->invalidarCacheUsuario($horario);
    }

    /**
     * Handle the Horario "deleted" event.
     */
    public function deleted(Horario $horario): void
    {
        $this->invalidarCacheUsuario($horario);
    }

    /**
     * Handle the Horario "restored" event.
     */
    public function restored(Horario $horario): void
    {
        $this->invalidarCacheUsuario($horario);
    }

    /**
     * Handle the Horario "force deleted" event.
     */
    public function forceDeleted(Horario $horario): void
    {
        $this->invalidarCacheUsuario($horario);
    }

    /**
     * Invalidar cache do usuário
     */
    private function invalidarCacheUsuario(Horario $horario): void
    {
        if ($horario->usuario) {
            $this->horariosCacheService->invalidarCacheUsuario($horario->usuario);
            Log::info("Cache invalidado por mudança no horário", [
                'horario_id' => $horario->id,
                'usuario_id' => $horario->usuario_id,
                'dia_da_semana' => $horario->dia_da_semana,
                'tipo' => $horario->tipo,
            ]);
        }
    }
}
