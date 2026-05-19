<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $allowedRoles = ['admin', 'kasir'];
        $requestedRoles = array_values(array_intersect($roles, $allowedRoles));

        if ($requestedRoles !== $roles) {
            abort(403, 'Role tidak valid');
        }

        $userRole = auth()->user()->role ?? null;

        if (! in_array($userRole, $requestedRoles, true)) {
            abort(403, 'Akses tidak diizinkan');
        }

        return $next($request);
    }
}
