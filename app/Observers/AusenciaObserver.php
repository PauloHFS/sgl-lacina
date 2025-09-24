<?php

namespace App\Observers;

use App\Enums\StatusAusencia;
use App\Events\AusenciaAprovadaEvent;
use App\Events\AusenciaRejeitadaEvent;
use App\Models\Ausencia;

class AusenciaObserver
{
    /**
     * Handle the Ausencia "updated" event.
     */
    public function updated(Ausencia $ausencia): void
    {
        if ($ausencia->isDirty('status')) {
            if ($ausencia->status === StatusAusencia::Aprovado) {
                event(new AusenciaAprovadaEvent($ausencia));
            }

            if ($ausencia->status === StatusAusencia::Rejeitado) {
                event(new AusenciaRejeitadaEvent($ausencia));
            }
        }
    }
}
