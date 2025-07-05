<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogJobFailedToDiscord
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
    public function handle(JobFailed $event): void
    {
        $jobName = $event->job->resolveName();
        $connection = $event->connectionName;
        $queue = $event->job->getQueue();
        $exception = $event->exception;

        $message = sprintf(
            "**❌ Job Falhou**: `%s`\n**Conexão**: `%s`\n**Fila**: `%s`\n**Erro**: `%s`",
            $jobName,
            $connection,
            $queue,
            $exception->getMessage()
        );

        Log::channel('discord')->error($message, [
            'job' => $jobName,
            'connection' => $connection,
            'queue' => $queue,
            'exception' => $exception,
        ]);
    }
}
