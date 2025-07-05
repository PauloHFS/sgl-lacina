<?php

namespace App\Console\Commands;

use App\Jobs\TestDiscordLoggingJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestDiscordLogging extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:discord-logging {--fail : Fazer o job falhar intencionalmente}';

    /**
     * The console command description.
     */
    protected $description = 'Testa o sistema de logging do Discord disparando jobs e eventos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Iniciando testes do sistema de logging do Discord...');

        // Teste 1: Job que deve ser executado com sucesso
        $this->info('📤 Disparando job de teste (sucesso)...');
        TestDiscordLoggingJob::dispatch('Teste de job bem-sucedido');

        // Teste 2: Job que deve falhar (se solicitado)
        if ($this->option('fail')) {
            $this->info('💥 Disparando job de teste (falha)...');
            TestDiscordLoggingJob::dispatch('Teste de job que falhará', true);
        }

        // Teste 3: Log direto para Discord
        $this->info('📢 Enviando mensagem direta para Discord...');
        Log::channel('discord')->info('🧪 **Teste Manual**: Sistema de logging funcionando!');

        // Teste 4: Log de erro para Discord
        $this->info('❌ Enviando mensagem de erro para Discord...');
        Log::channel('discord')->error('🧪 **Teste Manual**: Log de erro funcionando!');

        $this->info('✅ Testes iniciados! Verifique seu canal do Discord para ver as mensagens.');
        $this->info('💡 Dica: Execute `php artisan queue:work` para processar os jobs.');

        return 0;
    }
}
