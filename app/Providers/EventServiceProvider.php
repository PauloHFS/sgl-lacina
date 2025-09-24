<?php

namespace App\Providers;

use App\Events\AusenciaAprovadaEvent;
use App\Events\AusenciaRejeitadaEvent;
use App\Listeners\NotificarColaboradorAusenciaAprovada;
use App\Listeners\NotificarColaboradorAusenciaRejeitada;
use App\Models\Ausencia;
use App\Observers\AusenciaObserver;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     */
    protected $listen = [
        AusenciaAprovadaEvent::class => [
            NotificarColaboradorAusenciaAprovada::class,
        ],
        AusenciaRejeitadaEvent::class => [
            NotificarColaboradorAusenciaRejeitada::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();

        Ausencia::observe(AusenciaObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
