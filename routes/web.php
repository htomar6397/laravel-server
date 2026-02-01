<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminRoleController;
use App\Http\Controllers\Admin\AdminProjectController;
use App\Http\Controllers\Admin\AdminIndicatorController;
use App\Http\Controllers\Admin\AdminDataEntryController;
use App\Http\Controllers\Admin\AdminExpenditureController;
use App\Http\Controllers\Admin\AdminPhotoController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AdminOrganizationalUnitController;
use App\Http\Controllers\Admin\AdminThemeController;
use App\Http\Controllers\Admin\AdminAuditLogController;
use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Admin\AdminAIReportController;
use App\Http\Controllers\Admin\AdminQuarterlyPackController;
use App\Http\Controllers\Admin\AdminDHIS2Controller;
use App\Http\Controllers\Admin\AdminProfileController;

Route::get('/', function () {
    // Redirect authenticated users to appropriate dashboard
    if (auth()->check()) {
        $user = auth()->user();
        
        // Check if user has admin role
        if ($user->hasAnyRole(['Admin', 'ADMIN', 'SUPER_ADMIN'])) {
            return redirect()->route('admin.dashboard');
        }
        
        // Regular users go to their dashboard (to be implemented)
        // For now, redirect to admin dashboard as well
        return redirect()->route('admin.dashboard');
    }
    
    return Inertia::render('welcome');
})->name('home');

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login'])->middleware('guest');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Admin Routes - Protected by admin middleware
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [AdminDashboardController::class, 'getStats'])->name('dashboard.stats');
    
    // Profile
    Route::get('/profile', [AdminProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
    
    // User Management
    Route::resource('users', AdminUserController::class);
    Route::post('users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');
    
    // Project Management
    Route::resource('projects', AdminProjectController::class);
    
    // Indicator Management
    Route::resource('indicators', AdminIndicatorController::class);
    
    // Role Management
    Route::resource('roles', AdminRoleController::class)->except(['show']);
    
    // Data Entry Review
    Route::get('data-entries', [AdminDataEntryController::class, 'index'])->name('data-entries.index');
    Route::get('data-entries/{dataEntry}', [AdminDataEntryController::class, 'show'])->name('data-entries.show');
    Route::post('data-entries/{dataEntry}/verify', [AdminDataEntryController::class, 'verify'])->name('data-entries.verify');
    Route::post('data-entries/{dataEntry}/reject', [AdminDataEntryController::class, 'reject'])->name('data-entries.reject');
    Route::post('data-entries/bulk-verify', [AdminDataEntryController::class, 'bulkVerify'])->name('data-entries.bulk-verify');
    Route::get('data-entries/export/csv', [AdminDataEntryController::class, 'export'])->name('data-entries.export');
    
    // Expenditure Approval
    Route::get('expenditures', [AdminExpenditureController::class, 'index'])->name('expenditures.index');
    Route::get('expenditures/{expenditure}', [AdminExpenditureController::class, 'show'])->name('expenditures.show');
    Route::post('expenditures/{expenditure}/approve', [AdminExpenditureController::class, 'approve'])->name('expenditures.approve');
    Route::post('expenditures/{expenditure}/reject', [AdminExpenditureController::class, 'reject'])->name('expenditures.reject');
    Route::post('expenditures/{expenditure}/verify', [AdminExpenditureController::class, 'verify'])->name('expenditures.verify');
    Route::post('expenditures/bulk-approve', [AdminExpenditureController::class, 'bulkApprove'])->name('expenditures.bulk-approve');
    Route::get('expenditures/export/csv', [AdminExpenditureController::class, 'export'])->name('expenditures.export');
    
    // Photo Gallery
    Route::get('photos', [AdminPhotoController::class, 'index'])->name('photos.index');
    Route::get('photos/stats', [AdminPhotoController::class, 'stats'])->name('photos.stats');
    Route::get('photos/{photo}', [AdminPhotoController::class, 'show'])->name('photos.show');
    Route::get('photos/{photo}/download', [AdminPhotoController::class, 'download'])->name('photos.download');
    Route::delete('photos/{photo}', [AdminPhotoController::class, 'destroy'])->name('photos.destroy');
    
    // Reports
    Route::get('reports', [AdminReportController::class, 'index'])->name('reports');
    
    // Settings
    Route::get('settings', [AdminSettingsController::class, 'index'])->name('settings');
    Route::post('settings', [AdminSettingsController::class, 'update'])->name('settings.update');
    
    // Organizational Units
    Route::resource('organizational-units', AdminOrganizationalUnitController::class);
    
    // Theme Management
    Route::resource('themes', AdminThemeController::class);
    
    // Audit Logs
    Route::get('audit-logs', [AdminAuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('audit-logs/{auditLog}', [AdminAuditLogController::class, 'show'])->name('audit-logs.show');
    
    // Notifications
    Route::get('notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
    
    // AI Reports
    Route::get('ai-reports', [AdminAIReportController::class, 'index'])->name('ai-reports.index');
    
    // Quarterly Pack
    Route::get('quarterly-pack', [AdminQuarterlyPackController::class, 'index'])->name('quarterly-pack.index');
    Route::post('quarterly-pack/download', [AdminQuarterlyPackController::class, 'download'])->name('quarterly-pack.download');
    
    // DHIS2 Integration
    Route::get('dhis2', [AdminDHIS2Controller::class, 'index'])->name('dhis2.index');
    Route::get('dhis2/config', [AdminDHIS2Controller::class, 'config'])->name('dhis2.config');
    Route::post('dhis2/config', [AdminDHIS2Controller::class, 'updateConfig'])->name('dhis2.config.update');
    Route::post('dhis2/test-connection', [AdminDHIS2Controller::class, 'testConnection'])->name('dhis2.test-connection');
    Route::post('dhis2/sync', [AdminDHIS2Controller::class, 'sync'])->name('dhis2.sync');
    
    // PlanRep Integration
    Route::get('planrep', [AdminPlanRepController::class, 'index'])->name('planrep.index');
    Route::get('planrep/config', [AdminPlanRepController::class, 'config'])->name('planrep.config');
    Route::post('planrep/config', [AdminPlanRepController::class, 'updateConfig'])->name('planrep.config.update');
    Route::post('planrep/test-connection', [AdminPlanRepController::class, 'testConnection'])->name('planrep.test-connection');
    Route::post('planrep/sync', [AdminPlanRepController::class, 'sync'])->name('planrep.sync');
});

