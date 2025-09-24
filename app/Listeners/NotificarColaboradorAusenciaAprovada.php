<?php

namespace App\Listeners;

use App\Events\AusenciaAprovadaEvent;
use App\Mail\AusenciaAprovadaMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class NotificarColaboradorAusenciaAprovada implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(AusenciaAprovadaEvent $event): void
    {
        Mail::to($event->ausencia->user->email)
            ->queue(new AusenciaAprovadaMail($event->ausencia));
    }
}
