<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin Middleware
 * 
 * Ensures only admin users can access admin routes
 */
class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Check if user has admin role
        $user = auth()->user();
        
        // Check if user has admin role
        $isAdmin = $user->roles()->whereIn('name', ['Admin', 'ADMIN', 'SUPER_ADMIN'])->exists();

        if (!$isAdmin) {
            abort(403, 'Unauthorized access. Admin privileges required.');
        }

        return $next($request);
    }
}
