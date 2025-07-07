<?php

namespace App\Listeners;

use App\Events\CadastroAceito;
use App\Mail\CadastroAceito as CadastroAceitoMail;
use App\Services\HorarioService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendCadastroAceitoNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * Number of seconds to wait before retrying.
     */
    public $backoff = [10, 30, 60];

    /**
     * The maximum number of seconds the job should run.
     */
    public $timeout = 120;

    /**
     * Create the event listener.
     */
    public function __construct(
        private HorarioService $horarioService
    ) {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CadastroAceito $event): void
    {
        try {
            // Envia o email de notificação (apenas se ainda não foi enviado)
            $this->enviarEmailNotificacao($event);

            // Cria horários automaticamente se não existirem
            $this->horarioService->criarHorariosParaUsuario($event->user);

            Log::info('Processamento de cadastro aceito finalizado com sucesso', [
                'user_id' => $event->user->id,
                'email' => $event->user->email,
                'attempt' => $this->attempts()
            ]);
        } catch (Exception $e) {
            Log::error('Erro ao processar cadastro aceito', [
                'user_id' => $event->user->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'attempt' => $this->attempts(),
                'max_attempts' => $this->tries
            ]);

            // Re-throw para que o job seja marcado como falhado e possa ser tentado novamente
            throw $e;
        }
    }

    /**
     * Envia email de notificação para o usuário
     */
    private function enviarEmailNotificacao(CadastroAceito $event): void
    {
        $cacheKey = "cadastro_aceito_email_sent_{$event->user->id}";

        if (Cache::get($cacheKey)) {
            Log::info('Email já foi enviado anteriormente, pulando envio', [
                'user_id' => $event->user->id
            ]);
            return;
        }

        $url = $event->url ?? config('app.url') . '/dashboard';
        $observacao = $event->observacao ?? '';

        Mail::to($event->user->email)->send(
            new CadastroAceitoMail($event->user, $url, $observacao)
        );

        Cache::put($cacheKey, true, now()->addDay());


        Log::info('Email de cadastro aceito enviado', [
            'user_id' => $event->user->id,
            'email' => $event->user->email
        ]);
    }

    /**
     * Determine if the job should be retried.
     */
    public function shouldRetry(Exception $exception): bool
    {
        // Não tenta novamente se for erro de validação ou de negócio
        if ($exception instanceof \InvalidArgumentException) {
            return false;
        }

        // Retry para erros de conexão/timeout
        return true;
    }

    /**
     * Handle a job failure.
     */
    public function failed(CadastroAceito $event, Exception $exception): void
    {
        $errorData = [
            'user_id' => $event->user->id,
            'user_email' => $event->user->email,
            'error' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'attempts' => $this->attempts()
        ];

        Log::error('Job de cadastro aceito falhou completamente', $errorData);

        Log::channel('discord')->critical('🚨 FALHA CRÍTICA: Cadastro aceito não processado', [
            'usuario' => $event->user->name . ' (' . $event->user->email . ')',
            'erro' => $exception->getMessage(),
            'tentativas' => $this->attempts() . '/' . $this->tries,
            'comando_retry' => $this->job ? "php artisan queue:retry {$this->job->getJobId()}" : "php artisan queue:retry all",
            'user_id' => $event->user->id
        ]);
    }
}
