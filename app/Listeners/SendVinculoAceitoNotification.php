<?php

namespace App\Listeners;

use App\Events\VinculoAceito as VinculoAceitoEvent;
use App\Mail\VinculoAceito as VinculoAceitoMail;
// use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendVinculoAceitoNotification implements ShouldQueue
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
    public function handle(VinculoAceitoEvent $event): void
    {
        $url = route('projetos.show', ['projeto' => $event->projeto->id]);

        Mail::to($event->user->email)->queue(new VinculoAceitoMail(
            $event->user, // Correctly typed from the event
            $event->projeto, // Correctly typed from the event
            $url
        ));
    }
}
