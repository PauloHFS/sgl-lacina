<?php

namespace App\Providers;

use App\Listeners\LogJobFailedToDiscord;
use App\Listeners\LogJobProcessedToDiscord;
use App\Listeners\LogScheduledTaskFinishedToDiscord;
use App\Listeners\NotificarCoordenadoresSolicitacaoVinculo;
use App\Events\SolicitacaoVinculoCriada;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     */
    protected $listen = [
        JobProcessed::class => [
            LogJobProcessedToDiscord::class,
        ],
        JobFailed::class => [
            LogJobFailedToDiscord::class,
        ],
        ScheduledTaskFinished::class => [
            LogScheduledTaskFinishedToDiscord::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
