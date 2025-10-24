<?php

use App\Listeners\LogJobProcessedToDiscord;
use App\Listeners\LogJobFailedToDiscord;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Log;

describe('Discord Logging Listeners', function () {
    beforeEach(function () {
        // Mock do canal Discord para não enviar mensagens reais durante os testes
        Log::shouldReceive('channel')
            ->with('discord')
            ->andReturnSelf();
    });

    test('LogJobProcessedToDiscord loga job processado com sucesso', function () {
        // Arrange
        $jobMock = Mockery::mock();
        $jobMock->shouldReceive('resolveName')->andReturn('App\Jobs\TestJob');
        $jobMock->shouldReceive('getQueue')->andReturn('default');

        $event = new JobProcessed('database', $jobMock);

        // Assert
        Log::shouldReceive('info')
            ->once()
            ->with(Mockery::on(function ($message) {
                return str_contains($message, 'Job Processado com Sucesso') &&
                    str_contains($message, 'App\Jobs\TestJob') &&
                    str_contains($message, 'default');
            }));

        // Act
        $listener = new LogJobProcessedToDiscord();
        $listener->handle($event);
    });

    test('LogJobFailedToDiscord loga job que falhou', function () {
        // Arrange
        $jobMock = Mockery::mock();
        $jobMock->shouldReceive('resolveName')->andReturn('App\Jobs\TestJob');
        $jobMock->shouldReceive('getQueue')->andReturn('default');

        $exception = new Exception('Erro de teste');
        $event = new JobFailed('database', $jobMock, $exception);

        // Assert
        Log::shouldReceive('error')
            ->once()
            ->with(
                Mockery::on(function ($message) {
                    return str_contains($message, 'Job Falhou') &&
                        str_contains($message, 'App\Jobs\TestJob') &&
                        str_contains($message, 'Erro de teste');
                }),
                Mockery::on(function ($context) {
                    return isset($context['job']) &&
                        isset($context['exception']);
                })
            );

        // Act
        $listener = new LogJobFailedToDiscord();
        $listener->handle($event);
    });
});
