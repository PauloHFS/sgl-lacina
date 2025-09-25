<?php

namespace App\Listeners;

use App\Events\NovaAusenciaEvent;
use App\Mail\NovaAusenciaMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class NotificarCoordenadoresNovaAusencia implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(NovaAusenciaEvent $event): void
    {
        $coordenadores = $event->ausencia->projeto->coordenadores;

        if ($coordenadores->isNotEmpty()) {
            Mail::to($coordenadores)->queue(new NovaAusenciaMail($event->ausencia));
        }
    }
}
