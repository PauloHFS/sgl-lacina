<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarCoordenadorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verifica se o usuário está autenticado
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Verifica se o usuário é um coordenador
        $user = auth()->user();

        // Verifica se o usuário é coordenador através de projetos onde ele é coordenador
        $isCoordenador = \App\Models\UsuarioProjeto::where('usuario_id', $user->id)
            ->where('tipo_vinculo', \App\Enums\TipoVinculo::COORDENADOR)
            ->where('status', \App\Enums\StatusVinculoProjeto::APROVADO)
            ->exists();

        if (!$isCoordenador) {
            abort(403, 'Acesso negado. Apenas coordenadores podem acessar esta área.');
        }

        return $next($request);
    }
}
