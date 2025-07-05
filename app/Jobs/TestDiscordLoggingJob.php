<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TestDiscordLoggingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $message = 'Job de teste executado com sucesso!',
        public bool $shouldFail = false
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Executando job de teste: {$this->message}");

        if ($this->shouldFail) {
            throw new \Exception('Job de teste configurado para falhar');
        }

        // Simula algum processamento
        sleep(1);

        Log::info("Job de teste concluído com sucesso: {$this->message}");
    }
}
