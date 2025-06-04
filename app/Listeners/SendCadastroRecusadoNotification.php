<?php

namespace App\Listeners;

use App\Events\CadastroRecusado;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use App\Mail\CadastroRecusado as CadastroRecusadoMail;

class SendCadastroRecusadoNotification implements ShouldQueue
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
    public function handle(CadastroRecusado $event): void
    {
        $url = config('app.url') . '/register';

        Mail::to($event->dadosColaborador['email'])->send(new CadastroRecusadoMail(
            $event->dadosColaborador,
            $event->url ?? $url,
            $event->observacao ?? ''
        ));
    }
}
