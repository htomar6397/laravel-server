<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Project;
use App\Models\DataEntry;
use App\Models\Expenditure;
use App\Models\PhotoCapture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

/**
 * Admin Dashboard Controller
 * 
 * Handles main admin dashboard with analytics and KPIs
 */
class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard
     */
    public function index()
    {
        // Get key metrics
        $metrics = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'total_projects' => Project::count(),
            'active_projects' => Project::where('status', 'ACTIVE')->count(),
            'completed_projects' => Project::where('status', 'COMPLETED')->count(),
            'total_data_entries' => DataEntry::count(),
            'pending_data_entries' => DataEntry::where('verification_status', 'PENDING')->count(),
            'total_expenditures' => Expenditure::count(),
            'pending_expenditures' => Expenditure::where('status', 'PENDING')->count(),
            'total_photos' => PhotoCapture::count(),
            'total_budget' => Project::sum('budget'),
            'total_spent' => Expenditure::where('status', 'APPROVED')->sum('amount'),
        ];

        // Recent activity
        $recentDataEntries = DataEntry::with(['project', 'indicator', 'enteredBy'])
            ->latest()
            ->take(10)
            ->get();

        $recentExpenditures = Expenditure::with(['project', 'enteredBy'])
            ->latest()
            ->take(10)
            ->get();

        $recentPhotos = PhotoCapture::with(['project', 'capturedBy'])
            ->latest()
            ->take(10)
            ->get();

        // Project statistics by status
        $projectsByStatus = Project::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        // Data entries by month (last 12 months)
        $dataEntriesByMonth = DataEntry::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('count(*) as count')
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Expenditures by category
        $expendituresByCategory = Expenditure::select('category', DB::raw('SUM(amount) as total'))
            ->where('status', 'APPROVED')
            ->groupBy('category')
            ->get();

        // Top projects by budget
        $topProjectsByBudget = Project::select('id', 'name', 'budget', 'status')
            ->orderBy('budget', 'desc')
            ->take(10)
            ->get();

        // Field officer activity
        $fieldOfficerActivity = User::withCount([
                'dataEntries' => function($query) {
                    $query->where('created_at', '>=', Carbon::now()->subDays(30));
                },
                'expenditures' => function($query) {
                    $query->where('created_at', '>=', Carbon::now()->subDays(30));
                },
                'photoCaptures' => function($query) {
                    $query->where('created_at', '>=', Carbon::now()->subDays(30));
                }
            ])
            ->where('is_active', true)
            ->orderBy('data_entries_count', 'desc')
            ->take(10)
            ->get();

        // Sync statistics (unsynced items from mobile)
        $syncStats = [
            'pending_sync' => 0, // This would come from a sync queue table if implemented
            'last_sync' => Carbon::now()->subHours(2), // Mock data
            'sync_success_rate' => 98.5, // Mock data
        ];

        return Inertia::render('Admin/Dashboard', [
            'metrics' => $metrics,
            'recentDataEntries' => $recentDataEntries,
            'recentExpenditures' => $recentExpenditures,
            'recentPhotos' => $recentPhotos,
            'projectsByStatus' => $projectsByStatus,
            'dataEntriesByMonth' => $dataEntriesByMonth,
            'expendituresByCategory' => $expendituresByCategory,
            'topProjectsByBudget' => $topProjectsByBudget,
            'fieldOfficerActivity' => $fieldOfficerActivity,
            'syncStats' => $syncStats,
        ]);
    }

    /**
     * Get real-time statistics for dashboard widgets
     */
    public function getStats()
    {
        return response()->json([
            'users_online' => User::where('last_login_at', '>=', Carbon::now()->subMinutes(15))->count(),
            'data_entries_today' => DataEntry::whereDate('created_at', Carbon::today())->count(),
            'expenditures_today' => Expenditure::whereDate('created_at', Carbon::today())->count(),
            'photos_today' => PhotoCapture::whereDate('created_at', Carbon::today())->count(),
        ]);
    }
}
