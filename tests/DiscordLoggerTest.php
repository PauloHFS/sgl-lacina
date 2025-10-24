<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

uses(TestCase::class);

describe('Discord Logger Integration', function () {
    test('pode enviar erro para discord quando webhook está configurado', function () {
        if (!config('logging.channels.discord.webhook_url')) {
            $this->markTestSkipped('Discord webhook não configurado');
        }

        Log::channel('discord')->error('Teste de erro para Discord', [
            'teste' => true,
            'timestamp' => now(),
            'user_id' => 123,
        ]);

        $this->assertTrue(true); // Se chegou até aqui, não houve exception
    });

    test('pode enviar diferentes níveis de log', function () {
        if (!config('logging.channels.discord.webhook_url')) {
            $this->markTestSkipped('Discord webhook não configurado');
        }

        Log::channel('discord')->warning('Teste de warning para Discord');
        Log::channel('discord')->info('Teste de info para Discord');
        Log::channel('discord')->critical('Teste crítico para Discord');

        $this->assertTrue(true);
    });

    test('pode enviar log com exception', function () {
        if (!config('logging.channels.discord.webhook_url')) {
            $this->markTestSkipped('Discord webhook não configurado');
        }

        $exception = new \Exception('Erro de teste', 500);

        Log::channel('discord')->error('Erro com exception', [
            'exception' => $exception,
            'contexto_adicional' => 'dados extras',
        ]);

        $this->assertTrue(true);
    });

    test('não falha se discord estiver indisponível', function () {
        // Temporariamente configurar URL inválida
        $originalUrl = config('logging.channels.discord.webhook_url');
        Config::set('logging.channels.discord.webhook_url', 'https://invalid-discord-url.com/webhook/test');

        Log::channel('discord')->error('Teste com URL inválida', [
            'teste_resiliencia' => true,
        ]);

        // Restaurar configuração original
        Config::set('logging.channels.discord.webhook_url', $originalUrl);

        $this->assertTrue(true); // Deve continuar funcionando mesmo com erro
    });

    test('não falha se webhook_url não estiver configurado', function () {
        // Temporariamente remover configuração
        $originalUrl = config('logging.channels.discord.webhook_url');
        Config::set('logging.channels.discord.webhook_url', null);

        Log::channel('discord')->error('Teste sem webhook configurado');

        // Restaurar configuração original
        Config::set('logging.channels.discord.webhook_url', $originalUrl);

        $this->assertTrue(true);
    });

    test('funciona com contexto vazio', function () {
        if (!config('logging.channels.discord.webhook_url')) {
            $this->markTestSkipped('Discord webhook não configurado');
        }

        Log::channel('discord')->error('Mensagem simples sem contexto');

        $this->assertTrue(true);
    });

    test('funciona com contexto complexo', function () {
        if (!config('logging.channels.discord.webhook_url')) {
            $this->markTestSkipped('Discord webhook não configurado');
        }

        Log::channel('discord')->error('Teste com contexto complexo', [
            'projeto_id' => 'uuid-123',
            'usuario' => [
                'id' => 456,
                'email' => 'test@example.com',
            ],
            'dados_request' => [
                'ip' => '192.168.1.1',
                'user_agent' => 'Test Browser',
            ],
            'arrays' => [1, 2, 3],
            'boolean' => true,
            'null_value' => null,
        ]);

        $this->assertTrue(true);
    });
});
