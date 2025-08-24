<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        
        // Admin has access to everything
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Check if user has any of the required roles
        foreach ($roles as $role) {
            switch ($role) {
                case 'salesperson':
                    if ($user->isSalesperson()) {
                        return $next($request);
                    }
                    break;
                case 'unit_head':
                    if ($user->isUnitHead()) {
                        return $next($request);
                    }
                    break;
                case 'storekeeper':
                    if ($user->isStorekeeper()) {
                        return $next($request);
                    }
                    break;
                case 'admin':
                    if ($user->isAdmin()) {
                        return $next($request);
                    }
                    break;
            }
        }

        // User doesn't have required role
        abort(403, 'Access denied. You do not have permission to access this resource.');
    }
}