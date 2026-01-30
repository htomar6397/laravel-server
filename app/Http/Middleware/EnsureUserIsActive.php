<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && !$user->is_active) {
            // Log out the inactive user
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been deactivated. Please contact the administrator.',
                    'error_code' => 'ACCOUNT_DEACTIVATED'
                ], 403);
            }

            return redirect()->route('login')
                ->with('error', 'Your account has been deactivated. Please contact the administrator.');
        }

        return $next($request);
    }
}
