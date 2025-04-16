<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckUserRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {

        // Validar se o usuário tá authenticado
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($role === 'colaborador' && !$user->isColaborador()) {
            abort(403, 'Acesso permitido apenas para colaboradores.');
        }

        if ($role === 'coordenador' && !$user->isCoordenador()) {
            abort(403, 'Acesso permitido apenas para coordenadores.');
        }

        return $next($request);
    }
}
