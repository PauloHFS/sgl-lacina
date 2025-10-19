<?php

namespace App\Listeners;

use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Log;

class LogJobProcessedToDiscord
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
    public function handle(JobProcessed $event): void
    {
        $jobName = $event->job->resolveName();
        $connection = $event->connectionName;
        $queue = $event->job->getQueue();

        $message = sprintf(
            "**Job Processado com Sucesso**: `%s`\n**Conexão**: `%s`\n**Fila**: `%s`",
            $jobName,
            $connection,
            $queue
        );

        Log::channel('discord')->info($message);
    }
}
