<?php

namespace App\Listeners;

use App\Events\VinculoAceito;
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
    public function handle(VinculoAceito $event): void
    {
        Mail::to($event->user_email)->send(new VinculoAceitoMail(
            $event->user_email,
            config('app.url') . '/dashboard'
        ));
    }
}
