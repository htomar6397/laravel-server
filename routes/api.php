<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{AuthController, DashboardController, UserController, ProjectController, ThemeController, IndicatorController, ExpenditureController, PhotoCaptureController, DataEntryController, NotificationController, ReportController, SyncController};

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public API Routes
Route::prefix('v1')->group(function () {
    // Authentication Routes
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);
    
    // Public data (if any)
    Route::get('/themes', [ThemeController::class, 'apiIndex']);
    Route::get('/indicators', [IndicatorController::class, 'apiIndex']);
});

// Protected API Routes
Route::middleware(['auth:sanctum', 'user.active'])->prefix('v1')->group(function () {
    
    // Authentication Routes
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::get('/auth/check', [AuthController::class, 'check']);
    
    // Dashboard Routes
    Route::get('/dashboard', [DashboardController::class, 'apiDashboard']);
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/dashboard/project/{project}/stats', [DashboardController::class, 'projectStats']);
    Route::get('/dashboard/notifications', [DashboardController::class, 'getNotifications']);
    Route::post('/notifications/{id}/read', [DashboardController::class, 'markNotificationRead']);
    Route::post('/notifications/read-all', [DashboardController::class, 'markAllNotificationsRead']);
    
    // User Management Routes
    Route::middleware(['permission:view_users'])->group(function () {
        Route::get('/users', [UserController::class, 'apiIndex']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/{user}', [UserController::class, 'showApi']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
        Route::get('/users/statistics', [UserController::class, 'statistics']);
        Route::get('/users/search', [UserController::class, 'search']);
        Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus']);
        Route::put('/users/{user}/roles', [UserController::class, 'updateRoles']);
        Route::put('/users/{user}/reset-password', [UserController::class, 'resetPassword']);
    });
    
    // Project Management Routes
    Route::middleware(['permission:view_projects'])->group(function () {
        Route::get('/projects', [ProjectController::class, 'apiIndex'])->middleware('can:viewAny,App\\Models\\Project');
        Route::post('/projects', [ProjectController::class, 'store'])->middleware('can:create,App\\Models\\Project');
        Route::get('/projects/{project}', [ProjectController::class, 'showApi'])->middleware('can:view,project');
        Route::put('/projects/{project}', [ProjectController::class, 'update'])->middleware('can:update,project');
        Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->middleware('can:delete,project');
        Route::get('/projects/statistics', [ProjectController::class, 'statistics']);
        Route::get('/projects/search', [ProjectController::class, 'search']);
        Route::put('/projects/{project}/status', [ProjectController::class, 'updateStatus'])->middleware('can:update,project');
    });
    
    // Theme Management Routes
    Route::middleware(['permission:view_themes'])->group(function () {
        Route::get('/themes', [ThemeController::class, 'apiIndex']);
        Route::post('/themes', [ThemeController::class, 'store']);
        Route::get('/themes/{theme}', [ThemeController::class, 'showApi']);
        Route::put('/themes/{theme}', [ThemeController::class, 'update']);
        Route::delete('/themes/{theme}', [ThemeController::class, 'destroy']);
        Route::post('/themes/{theme}/toggle-status', [ThemeController::class, 'toggleStatus']);
    });
    
    // Indicator Management Routes
    Route::middleware(['permission:view_indicators'])->group(function () {
        Route::get('/indicators', [IndicatorController::class, 'apiIndex']);
        Route::post('/indicators', [IndicatorController::class, 'store']);
        Route::get('/indicators/{indicator}', [IndicatorController::class, 'showApi']);
        Route::put('/indicators/{indicator}', [IndicatorController::class, 'update']);
        Route::delete('/indicators/{indicator}', [IndicatorController::class, 'destroy']);
        Route::post('/indicators/{indicator}/toggle-status', [IndicatorController::class, 'toggleStatus']);
        Route::get('/indicators/{indicator}/performance', [IndicatorController::class, 'performance']);
    });
    
    // Expenditure Management Routes
    Route::middleware(['permission:view_expenditures'])->group(function () {
        Route::get('/expenditures', [ExpenditureController::class, 'apiIndex'])->middleware('can:viewAny,App\\Models\\Expenditure');
        Route::post('/expenditures', [ExpenditureController::class, 'store'])->middleware('can:create,App\\Models\\Expenditure');
        Route::get('/expenditures/{expenditure}', [ExpenditureController::class, 'showApi'])->middleware('can:view,expenditure');
        Route::put('/expenditures/{expenditure}', [ExpenditureController::class, 'update'])->middleware('can:update,expenditure');
        Route::delete('/expenditures/{expenditure}', [ExpenditureController::class, 'destroy'])->middleware('can:delete,expenditure');
        Route::get('/expenditures/statistics', [ExpenditureController::class, 'statistics']);
        Route::get('/expenditures/search', [ExpenditureController::class, 'search']);
        Route::post('/expenditures/{expenditure}/approve', [ExpenditureController::class, 'approve'])->middleware('can:approve,expenditure');
        Route::post('/expenditures/{expenditure}/reject', [ExpenditureController::class, 'reject'])->middleware('can:update,expenditure');
        Route::post('/expenditures/{expenditure}/verify', [ExpenditureController::class, 'verify'])->middleware('can:verify,expenditure');
        Route::post('/expenditures/{expenditure}/reset', [ExpenditureController::class, 'reset'])->middleware('can:update,expenditure');
    });
    
    // Photo Capture Routes
    Route::middleware(['permission:view_photos'])->group(function () {
        Route::get('/photos', [PhotoCaptureController::class, 'apiIndex'])->middleware('can:viewAny,App\\Models\\PhotoCapture');
        Route::post('/photos', [PhotoCaptureController::class, 'uploadApi'])->middleware('can:create,App\\Models\\PhotoCapture');
        Route::get('/photos/{photo}', [PhotoCaptureController::class, 'showApi'])->middleware('can:view,photo');
        Route::put('/photos/{photo}', [PhotoCaptureController::class, 'update'])->middleware('can:update,photo');
        Route::delete('/photos/{photo}', [PhotoCaptureController::class, 'destroy'])->middleware('can:delete,photo');
        Route::get('/photos/statistics', [PhotoCaptureController::class, 'statistics']);
        Route::get('/photos/search', [PhotoCaptureController::class, 'search']);
        Route::get('/photos/project/{project}', [PhotoCaptureController::class, 'byProject']);
        Route::get('/photos/with-gps', [PhotoCaptureController::class, 'withGPS']);
        Route::post('/photos/{photo}/restore', [PhotoCaptureController::class, 'restore'])->middleware('can:update,photo');
        Route::post('/photos/bulk-delete', [PhotoCaptureController::class, 'bulkDelete'])->middleware('can:delete,App\\Models\\PhotoCapture');
        
        // Alias routes for Flutter frontend compatibility (photo-captures)
        Route::get('/photo-captures', [PhotoCaptureController::class, 'apiIndex'])->middleware('can:viewAny,App\\Models\\PhotoCapture');
        Route::post('/photo-captures', [PhotoCaptureController::class, 'uploadApi'])->middleware('can:create,App\\Models\\PhotoCapture');
        Route::get('/photo-captures/{photo}', [PhotoCaptureController::class, 'showApi'])->middleware('can:view,photo');
        Route::delete('/photo-captures/{photo}', [PhotoCaptureController::class, 'destroy'])->middleware('can:delete,photo');
    });
    
    // Data Entry Routes
    Route::middleware(['permission:view_data'])->group(function () {
        Route::get('/data-entries', [DataEntryController::class, 'apiIndex'])->middleware('can:viewAny,App\\Models\\DataEntry');
        Route::post('/data-entries', [DataEntryController::class, 'store'])->middleware('can:create,App\\Models\\DataEntry');
        Route::get('/data-entries/{dataEntry}', [DataEntryController::class, 'showApi'])->middleware('can:view,dataEntry');
        Route::put('/data-entries/{dataEntry}', [DataEntryController::class, 'update'])->middleware('can:update,dataEntry');
        Route::delete('/data-entries/{dataEntry}', [DataEntryController::class, 'destroy'])->middleware('can:delete,dataEntry');
        Route::get('/data-entries/statistics', [DataEntryController::class, 'statistics']);
        Route::get('/data-entries/search', [DataEntryController::class, 'search']);
        Route::post('/data-entries/{dataEntry}/verify', [DataEntryController::class, 'verify'])->middleware('can:verify,dataEntry');
        Route::post('/data-entries/{dataEntry}/reject', [DataEntryController::class, 'reject'])->middleware('can:update,dataEntry');
        Route::post('/data-entries/{dataEntry}/reset', [DataEntryController::class, 'reset'])->middleware('can:update,dataEntry');
        Route::get('/data-entries/indicator/{indicator}/trend', [DataEntryController::class, 'trendData']);
        Route::post('/data-entries/bulk-verify', [DataEntryController::class, 'bulkVerify'])->middleware('can:verify,App\\Models\\DataEntry');
    });
    
    // Notification Routes
    Route::get('/notifications', [NotificationController::class, 'apiIndex']);
    Route::get('/notifications/{notification}', [NotificationController::class, 'showApi']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsReadApi']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsReadApi']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroyApi']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::get('/notifications/recent', [NotificationController::class, 'recent']);
    Route::get('/notifications/statistics', [NotificationController::class, 'statistics']);
    
    // Custom Notification Routes (Admin only)
    Route::middleware(['permission:manage_notifications'])->group(function () {
        Route::post('/notifications/create-custom', [NotificationController::class, 'createCustom']);
    });
    
    // Report Routes
    Route::middleware(['permission:view_reports'])->group(function () {
        Route::get('/reports/data', [ReportController::class, 'apiData']);
        Route::get('/reports/project-performance', [ReportController::class, 'projectPerformance']);
        Route::get('/reports/financial', [ReportController::class, 'financial']);
        Route::get('/reports/data-quality', [ReportController::class, 'dataQuality']);
        Route::get('/reports/user-activity', [ReportController::class, 'userActivity']);
        Route::get('/reports/dashboard', [ReportController::class, 'dashboard']);
        Route::get('/reports/export', [ReportController::class, 'export']);
    });
    
    // Profile Management Routes
    Route::get('/profile', [AuthController::class, 'showProfileForm']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/change-password', [AuthController::class, 'changePassword']);
    
    // User Profile Routes (alias for Flutter compatibility)
    Route::get('/user', [AuthController::class, 'showProfileForm']);
    Route::put('/user', [AuthController::class, 'updateProfile']);
    
    // Sync Routes
    Route::get('/sync/status', [SyncController::class, 'status']);
    Route::post('/sync/bulk', [SyncController::class, 'bulkSync']);
});

// API Documentation Route
Route::get('/v1/docs', function () {
    return response()->json([
        'name' => 'KMC M&E System API',
        'version' => '1.0.0',
        'description' => 'Kibaha Municipal Council Monitoring and Evaluation System API',
        'endpoints' => [
            'Authentication' => [
                'POST /api/v1/auth/login' => 'User login',
                'POST /api/v1/auth/logout' => 'User logout',
                'POST /api/v1/auth/refresh' => 'Refresh token',
                'GET /api/v1/auth/me' => 'Get current user info',
            ],
            'Projects' => [
                'GET /api/v1/projects' => 'List projects',
                'POST /api/v1/projects' => 'Create project',
                'GET /api/v1/projects/{id}' => 'Get project details',
                'PUT /api/v1/projects/{id}' => 'Update project',
                'DELETE /api/v1/projects/{id}' => 'Delete project',
            ],
            'Users' => [
                'GET /api/v1/users' => 'List users',
                'POST /api/v1/users' => 'Create user',
                'GET /api/v1/users/{id}' => 'Get user details',
                'PUT /api/v1/users/{id}' => 'Update user',
                'DELETE /api/v1/users/{id}' => 'Delete user',
            ],
            // Add more endpoint documentation as needed
        ],
        'authentication' => 'Bearer Token required for protected routes',
        'permissions' => 'Role-based permissions apply to various endpoints',
        'swagger_ui' => env('APP_URL') . '/api/documentation',
    ]);
});

// Swagger UI Documentation Route
Route::get('/documentation', function () {
    return view('swagger.index');
});

// Health Check Endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0',
        'environment' => config('app.env'),
    ]);
});
