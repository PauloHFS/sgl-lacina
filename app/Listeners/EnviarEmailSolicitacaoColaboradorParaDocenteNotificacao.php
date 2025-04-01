<?php

namespace App\Listeners;

use App\Events\ColaboradorRegistrado;
use Illuminate\Support\Facades\Mail;
use App\Mail\ColaboradorRegistrado as ColaboradorRegistradoMail;

class EnviarEmailSolicitacaoColaboradorParaDocenteNotificacao
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
    public function handle(ColaboradorRegistrado $event): void
    {
        // enviar email para o docente
        Mail::to($event->docente_email)->send(new ColaboradorRegistradoMail(
            $event->user,
            config('app.url') . '/validar-pre-candidato/' . $event->user->id
        ));
    }
}
