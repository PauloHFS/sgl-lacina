<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'validarTipoVinculo' => \App\Http\Middleware\ValidarTipoVinculoMiddleware::class,
            'posCadastroNecessario' => \App\Http\Middleware\PosCadastroNecessarioMiddleware::class,
            'coordenador' => \App\Http\Middleware\VerificarCoordenadorMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Reportar erros críticos para Discord apenas em produção
        if (app()->environment('production')) {
            $exceptions->report(function (\Throwable $e) {
                $shouldReportToDiscord = function (\Throwable $e): bool {
                    // Não reportar alguns tipos de erros
                    $ignoredExceptions = [
                        \Illuminate\Auth\AuthenticationException::class,
                        \Illuminate\Validation\ValidationException::class,
                        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
                        \Illuminate\Session\TokenMismatchException::class,
                    ];

                    foreach ($ignoredExceptions as $ignoredException) {
                        if ($e instanceof $ignoredException) {
                            return false;
                        }
                    }

                    return true;
                };

                if ($shouldReportToDiscord($e)) {
                    Log::channel('discord')->error($e->getMessage(), [
                        'exception' => $e,
                        'user_id' => Auth::user()->id ?? null,
                        'url' => request()->url(),
                        'ip' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                    ]);
                }
            });
        }
    })->create();
