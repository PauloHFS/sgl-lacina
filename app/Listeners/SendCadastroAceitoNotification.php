<?php

namespace App\Listeners;

use App\Events\CadastroAceito;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use App\Mail\CadastroAceito as CadastroAceitoMail;

class SendCadastroAceitoNotification
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
    public function handle(CadastroAceito $event): void
    {
        Mail::to($event->user_email)->send(new CadastroAceitoMail(
            $event->user_email,
            config('app.url') . '/dashboard'
        ));
    }
}
