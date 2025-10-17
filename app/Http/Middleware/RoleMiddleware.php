<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user || !in_array($user->role, $roles, true)) {
            // 403 si autenticado y no tiene permiso; 302 al login si no autenticado
            abort(403, 'No tienes permisos para acceder a esta función.');
        }

        return $next($request);
    }
}
