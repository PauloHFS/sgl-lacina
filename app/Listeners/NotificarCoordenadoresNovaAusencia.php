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
        // 1. pegar os coordenadores do projeto da ausência
        // 2. enviar email para cada coordenador com o link para a ausência

        $coordenadores = $event->ausencia->projeto->coordenadores;
        foreach ($coordenadores as $coordenador) {
            Mail::to($coordenador->email)->queue(new NovaAusenciaMail($event->ausencia));
        }
    }
}
