<?php

namespace App\Observers;

use App\Models\Baia;

class BaiaObserver
{
    /**
     * Handle the Baia "deleting" event.
     */
    public function deleting(Baia $baia): void
    {
        $baia->horarios()->update(['baia_id' => null]);
    }

    /**
     * Handle the Baia "restored" event.
     */
    public function restored(Baia $baia): void
    {
        // Lógica para quando a baia for restaurada
        // Se necessário, pode restaurar os horários
    }
}
