<?php

namespace App\Listeners;

use App\Events\AusenciaRejeitadaEvent;
use App\Mail\AusenciaRejeitadaMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class NotificarColaboradorAusenciaRejeitada implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(AusenciaRejeitadaEvent $event): void
    {
        Mail::to($event->ausencia->user->email)
            ->queue(new AusenciaRejeitadaMail($event->ausencia));
    }
}
