<?php

namespace App\Listeners;

use App\Events\VinculoAceito as VinculoAceitoEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use App\Mail\VinculoAceito as VinculoAceitoMail;

class SendVinculoAceitoNotification
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
    public function handle(VinculoAceitoEvent $event): void
    {
        $url = route('projetos.show', ['projeto' => $event->projeto->id]);

        Mail::to($event->user->email)->send(new VinculoAceitoMail(
            $event->user, // Correctly typed from the event
            $event->projeto, // Correctly typed from the event
            $url
        ));
    }
}
