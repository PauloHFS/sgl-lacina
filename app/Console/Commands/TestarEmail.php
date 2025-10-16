<?php

namespace App\Console\Commands;

use App\Mail\TestMail;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TestarEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test 
                           {destinatario : Email do destinatário} 
                           {--assunto=Teste do Sistema de Email : Assunto do email}
                           {--conteudo=Este é um email de teste do sistema SGL LACINA. : Conteúdo do email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia um email de teste para verificar se o sistema de emails está funcionando corretamente';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $destinatario = $this->argument('destinatario');
        $assunto = $this->option('assunto');
        $conteudo = $this->option('conteudo');

        // Validar email
        if (! filter_var($destinatario, FILTER_VALIDATE_EMAIL)) {
            $this->error('❌ Email inválido: '.$destinatario);

            return Command::FAILURE;
        }

        $this->info('📧 Enviando email de teste...');
        $this->line('');
        $this->line('🎯 Destinatário: '.$destinatario);
        $this->line('📋 Assunto: '.$assunto);
        $this->line('📝 Conteúdo: '.$conteudo);
        $this->line('');

        try {
            // Registrar tentativa no log
            Log::info('Tentativa de envio de email de teste', [
                'destinatario' => $destinatario,
                'assunto' => $assunto,
                'timestamp' => now(),
                'environment' => app()->environment(),
            ]);

            // Enviar o email
            Mail::to($destinatario)->send(new TestMail($assunto, $conteudo));

            $this->info('✅ Email enviado com sucesso!');
            $this->line('');
            $this->line('📝 Informações adicionais:');
            $this->line('• Ambiente: '.app()->environment());
            $this->line('• Mailer padrão: '.config('mail.default'));
            $this->line('• Host SMTP: '.config('mail.mailers.smtp.host'));
            $this->line('• Porta SMTP: '.config('mail.mailers.smtp.port'));
            $this->line('• Timestamp: '.now()->format('d/m/Y H:i:s'));

            // Log de sucesso
            Log::info('Email de teste enviado com sucesso', [
                'destinatario' => $destinatario,
                'assunto' => $assunto,
                'timestamp' => now(),
                'environment' => app()->environment(),
            ]);

            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error('❌ Erro ao enviar email: '.$e->getMessage());
            $this->line('');
            $this->line('💡 Possíveis causas:');
            $this->line('• Configuração incorreta do SMTP');
            $this->line('• Credenciais inválidas');
            $this->line('• Problemas de conectividade');
            $this->line('• Servidor SMTP indisponível');

            // Log do erro
            Log::error('Erro ao enviar email de teste', [
                'destinatario' => $destinatario,
                'assunto' => $assunto,
                'erro' => $e->getMessage(),
                'timestamp' => now(),
                'environment' => app()->environment(),
            ]);

            return Command::FAILURE;
        }
    }
}
