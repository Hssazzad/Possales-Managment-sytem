<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  $permission
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, $permission = null): Response
    {
        if (!auth()->check()) {
            abort(403, 'Unauthorized');
        }

        if (!$permission) {
            abort(403, 'Permission not specified');
        }

        if (!auth()->user()->hasPermission($permission)) {
            abort(403, 'You do not have permission to access this resource');
        }

        return $next($request);
    }
}
