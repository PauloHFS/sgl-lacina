<?php

namespace App\Logging;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Monolog\LogRecord;

class DiscordLogHandler extends AbstractProcessingHandler
{
    private string $webhookUrl;

    private Client $httpClient;

    public function __construct(string $webhookUrl, $level = Logger::ERROR, bool $bubble = true)
    {
        $this->webhookUrl = $webhookUrl;
        $this->httpClient = new Client;
        parent::__construct($level, $bubble);
    }

    protected function write(LogRecord $record): void
    {
        $embed = [
            'title' => '🚨 Erro no Sistema LaCInA',
            'color' => $this->getColorByLevel($record->level->value),
            'timestamp' => now()->toISOString(),
            'fields' => [
                [
                    'name' => 'Ambiente',
                    'value' => Config::get('app.env'),
                    'inline' => true,
                ],
                [
                    'name' => 'Nível',
                    'value' => $record->level->name,
                    'inline' => true,
                ],
                [
                    'name' => 'Mensagem',
                    'value' => $this->truncateMessage($record->message),
                    'inline' => false,
                ],
            ],
        ];

        // Adicionar contexto se existir
        if (! empty($record->context)) {
            $embed['fields'][] = [
                'name' => 'Contexto',
                'value' => '```json'."\n".json_encode($record->context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n".'```',
                'inline' => false,
            ];
        }

        // Adicionar stack trace se for uma exception
        if (isset($record->context['exception'])) {
            $exception = $record->context['exception'];
            if ($exception instanceof \Throwable) {
                $embed['fields'][] = [
                    'name' => 'Arquivo',
                    'value' => $exception->getFile().':'.$exception->getLine(),
                    'inline' => false,
                ];

                $embed['fields'][] = [
                    'name' => 'Stack Trace',
                    'value' => '```'."\n".$this->truncateMessage($exception->getTraceAsString())."\n".'```',
                    'inline' => false,
                ];
            }
        }

        $payload = [
            'username' => 'SGL - LaCInA',
            'embeds' => [$embed],
        ];

        try {
            $this->httpClient->post($this->webhookUrl, [
                'json' => $payload,
                'timeout' => 5,
            ]);
        } catch (\Exception $e) {
            // Não fazer nada se falhar ao enviar para Discord
            // para evitar loop infinito de erros
        }
    }

    private function getColorByLevel(int $level): int
    {
        return match ($level) {
            Logger::DEBUG => 0x808080,      // Cinza
            Logger::INFO => 0x0099FF,       // Azul
            Logger::NOTICE => 0x00FF99,     // Verde claro
            Logger::WARNING => 0xFF9900,    // Laranja
            Logger::ERROR => 0xFF0000,      // Vermelho
            Logger::CRITICAL => 0x990000,   // Vermelho escuro
            Logger::ALERT => 0xFF0099,      // Rosa
            Logger::EMERGENCY => 0x000000,  // Preto
            default => 0x808080
        };
    }

    private function truncateMessage(string $message, int $limit = 1024): string
    {
        if (strlen($message) <= $limit) {
            return $message;
        }

        return substr($message, 0, $limit - 3).'...';
    }
}

class DiscordLogger
{
    public function __invoke(array $config)
    {
        $handler = new DiscordLogHandler(
            $config['webhook_url'],
            $config['level'] ?? Logger::ERROR
        );

        return new Logger('discord', [$handler]);
    }
}
