<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Enums\Role;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (!$user) {
            // This case should ideally be handled by the 'auth' middleware first
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $userRole = $user->role; // Accessor from HasRole trait

        // The COORDENADOR_MASTER can access everything, but we check here for clarity
        if ($userRole === Role::COORDENADOR_MASTER) {
            return $next($request);
        }

        foreach ($roles as $role) {
            $roleEnum = Role::tryFrom($role);
            // Check if the user's role matches one of the required roles
            if ($roleEnum && $userRole === $roleEnum) {
                return $next($request);
            }
        }

        return response()->json(['message' => 'Forbidden'], 403);
    }
}
