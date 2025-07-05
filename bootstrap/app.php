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
        // Exceções que não devem ser reportadas para o Discord.
        // São erros comuns ou esperados que não indicam uma falha na aplicação.
        $exceptions->dontReport([
            \Illuminate\Auth\AuthenticationException::class, // 401 - Não autenticado
            \Illuminate\Auth\Access\AuthorizationException::class, // 403 - Não autorizado
            \Illuminate\Validation\ValidationException::class, // 422 - Erro de validação
            \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class, // 404 - Página não encontrada
            \Illuminate\Session\TokenMismatchException::class, // Erro de token CSRF
            \Illuminate\Http\Exceptions\ThrottleRequestsException::class, // Limite de requisições
        ]);

        // Reportar todas as outras exceções para o Discord, se o canal estiver configurado.
        $exceptions->report(function (\Throwable $e) {
            // Usar try-catch para garantir que, se o log do Discord falhar,
            // o erro original ainda seja logado no canal padrão.
            try {
                if (config('logging.channels.discord.webhook_url')) {
                    Log::channel('discord')->error($e->getMessage(), [
                        'exception' => $e,
                        'user_id' => Auth::check() ? Auth::id() : 'Guest',
                        'url' => request()->fullUrl(),
                        'ip' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                    ]);
                }
            } catch (\Throwable $logException) {
                Log::error('Falha ao logar no Discord. Erro original: ' . $e->getMessage());
                Log::error('Exceção do logger: ' . $logException->getMessage());
            }
        });
    })->create();
