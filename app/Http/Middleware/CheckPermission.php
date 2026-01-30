<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $permission)
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

        if (!$user->hasPermission($permission)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You do not have permission to access this resource.',
                    'error_code' => 'INSUFFICIENT_PERMISSIONS',
                    'required_permission' => $permission
                ], 403);
            }

            abort(403, 'Unauthorized. You do not have permission to access this resource.');
        }

        return $next($request);
    }
}
