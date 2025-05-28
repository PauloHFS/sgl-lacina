<?php

namespace App\Listeners;

use App\Events\CadastroAceito;
use Illuminate\Contracts\Queue\ShouldQueue;
// use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use App\Mail\CadastroAceito as CadastroAceitoMail;

class SendCadastroAceitoNotification implements ShouldQueue
{
    // use InteractsWithQueue;
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
        // $event->user is already an instance of App\Models\User due to the CadastroAceito event definition
        Mail::to($event->user->email)->send(new CadastroAceitoMail(
            $event->user,
            config('app.url') . '/dashboard'
        ));
    }
}
