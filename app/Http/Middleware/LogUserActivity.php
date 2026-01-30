<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AuditLog;

class LogUserActivity
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
        $response = $next($request);

        // Only log if user is authenticated and request is not GET
        if (Auth::check() && !$request->isMethod('GET')) {
            $user = Auth::user();
            
            // Skip logging for certain routes to avoid noise
            $skipRoutes = [
                'notifications.read',
                'notifications.mark-all-read',
                'dashboard.notifications',
            ];

            if (!in_array($request->route()->getName(), $skipRoutes)) {
                $this->logActivity($request, $user);
            }
        }

        return $response;
    }

    /**
     * Log user activity
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return void
     */
    private function logActivity(Request $request, $user)
    {
        try {
            $routeName = $request->route()->getName();
            $method = $request->method();
            
            // Determine action based on route and method
            $action = $this->determineAction($routeName, $method);
            
            // Determine entity type and ID
            $entityInfo = $this->determineEntity($request);
            
            AuditLog::create([
                'user_id' => $user->id,
                'action' => $action,
                'entity_type' => $entityInfo['type'],
                'entity_id' => $entityInfo['id'],
                'old_values' => $this->getOldValues($request),
                'new_values' => $this->getNewValues($request),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (\Exception $e) {
            // Log the error but don't break the application
            \Log::error('Failed to log user activity: ' . $e->getMessage());
        }
    }

    /**
     * Determine action based on route and method
     *
     * @param  string  $routeName
     * @param  string  $method
     * @return string
     */
    private function determineAction($routeName, $method)
    {
        $actionMap = [
            'POST' => 'CREATED',
            'PUT' => 'UPDATED',
            'PATCH' => 'UPDATED',
            'DELETE' => 'DELETED',
        ];

        // Special cases for specific actions
        if (str_contains($routeName, 'approve')) {
            return 'APPROVED';
        }
        if (str_contains($routeName, 'reject')) {
            return 'REJECTED';
        }
        if (str_contains($routeName, 'verify')) {
            return 'VERIFIED';
        }
        if (str_contains($routeName, 'toggle')) {
            return 'TOGGLED_STATUS';
        }
        if (str_contains($routeName, 'reset')) {
            return 'RESET';
        }
        if (str_contains($routeName, 'bulk')) {
            return 'BULK_ACTION';
        }

        return $actionMap[$method] ?? 'ACCESSED';
    }

    /**
     * Determine entity type and ID from request
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    private function determineEntity(Request $request)
    {
        $routeName = $request->route()->getName();
        $parameters = $request->route()->parameters();

        // Extract entity type from route name
        if (str_contains($routeName, 'project')) {
            return [
                'type' => 'Project',
                'id' => $parameters['project'] ?? null,
            ];
        }
        if (str_contains($routeName, 'user')) {
            return [
                'type' => 'User',
                'id' => $parameters['user'] ?? null,
            ];
        }
        if (str_contains($routeName, 'expenditure')) {
            return [
                'type' => 'Expenditure',
                'id' => $parameters['expenditure'] ?? null,
            ];
        }
        if (str_contains($routeName, 'photo')) {
            return [
                'type' => 'PhotoCapture',
                'id' => $parameters['photo'] ?? null,
            ];
        }
        if (str_contains($routeName, 'data-entr')) {
            return [
                'type' => 'DataEntry',
                'id' => $parameters['dataEntry'] ?? null,
            ];
        }
        if (str_contains($routeName, 'indicator')) {
            return [
                'type' => 'Indicator',
                'id' => $parameters['indicator'] ?? null,
            ];
        }
        if (str_contains($routeName, 'theme')) {
            return [
                'type' => 'Theme',
                'id' => $parameters['theme'] ?? null,
            ];
        }
        if (str_contains($routeName, 'notification')) {
            return [
                'type' => 'Notification',
                'id' => $parameters['notification'] ?? null,
            ];
        }

        return [
            'type' => 'System',
            'id' => null,
        ];
    }

    /**
     * Get old values for audit log
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|null
     */
    private function getOldValues(Request $request)
    {
        // This would typically be handled by model events
        // For middleware, we'll return null as it's harder to get old values here
        return null;
    }

    /**
     * Get new values for audit log
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|null
     */
    private function getNewValues(Request $request)
    {
        // Only log non-sensitive data
        $allowedData = [];
        $sensitiveFields = ['password', 'password_confirmation', 'current_password'];
        
        foreach ($request->all() as $key => $value) {
            if (!in_array($key, $sensitiveFields) && !is_object($value)) {
                $allowedData[$key] = $value;
            }
        }

        return empty($allowedData) ? null : $allowedData;
    }
}
