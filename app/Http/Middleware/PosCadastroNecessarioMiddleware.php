<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Enums\StatusCadastro;
use Illuminate\Support\Facades\Log;

class PosCadastroNecessarioMiddleware
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    /** @var \App\Models\User $user */
    $user = Auth::user();

    if ($user->status_cadastro === StatusCadastro::IMCOMPLETO) {
      if (!$request->routeIs('pos-cadastro')) {
        return redirect()->route('pos-cadastro');
      }
    }

    if ($user->status_cadastro === StatusCadastro::PENDENTE) {
      if (!$request->routeIs('waiting-approval')) {
        return redirect()->route('waiting-approval');
      }
    }

    return $next($request);
  }
}
