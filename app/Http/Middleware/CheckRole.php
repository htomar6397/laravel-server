<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        $user = Auth::user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated. Please login to continue.',
                    'error_code' => 'UNAUTHENTICATED'
                ], 401);
            }

            return redirect()->route('login');
        }

        if (!$user->hasRole($role)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You do not have the required role to access this resource.',
                    'error_code' => 'INSUFFICIENT_ROLE',
                    'required_role' => $role
                ], 403);
            }

            abort(403, 'Unauthorized. You do not have the required role to access this resource.');
        }

        return $next($request);
    }
}
