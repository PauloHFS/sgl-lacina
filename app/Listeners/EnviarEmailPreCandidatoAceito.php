<?php

namespace App\Listeners;

use App\Events\PreColaboradorAceito;
use App\Mail\ColaboradorAceito as ColaboradorAceitoMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class EnviarEmailPreCandidatoAceito
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    public function handle(PreColaboradorAceito $event): void
    {
        Mail::to($event->user_email)->send(new ColaboradorAceitoMail(
            $event->user_email,
            config('app.url') . '/pos-cadastro/' . $event->user_id
        ));
    }
}
