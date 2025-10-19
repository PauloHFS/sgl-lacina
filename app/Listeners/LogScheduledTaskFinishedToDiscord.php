<?php

namespace App\Listeners;

use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Support\Facades\Log;

class LogScheduledTaskFinishedToDiscord
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
    public function handle(ScheduledTaskFinished $event): void
    {
        $taskName = $event->task->getSummaryForDisplay() ?? $event->task->command;
        $exitCode = $event->task->exitCode;
        $runtime = round($event->runtime / 1000, 2); // em segundos

        $message = sprintf(
            "**Task Agendada Finalizada**: `%s`\n**Status**: %s\n**Duração**: `%s segundos`",
            $taskName,
            $exitCode === 0 ? '✅ Sucesso' : '❌ Falha',
            $runtime
        );

        if ($exitCode === 0) {
            Log::channel('discord')->info($message);
        } else {
            Log::channel('discord')->error($message);
        }
    }
}
