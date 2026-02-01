<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Project;
use App\Models\DataEntry;
use App\Models\Expenditure;

/**
 * Admin AI Reports Controller
 */
class AdminAIReportController extends Controller
{
    /**
     * Display AI-powered reports and analytics
     */
    public function index(Request $request)
    {
        // Get statistics for AI analysis
        $stats = [
            'total_projects' => Project::count(),
            'active_projects' => Project::where('status', 'active')->count(),
            'total_data_entries' => DataEntry::count(),
            'verified_entries' => DataEntry::whereNotNull('verified_at')->count(),
            'total_expenditures' => Expenditure::count(),
            'approved_expenditures' => Expenditure::where('status', 'approved')->count(),
            'total_budget' => Project::sum('budget'),
            'total_spent' => Expenditure::where('status', 'approved')->sum('amount'),
        ];

        // Get recent activity trends
        $recentActivity = [
            'data_entries_this_month' => DataEntry::whereMonth('created_at', now()->month)->count(),
            'data_entries_last_month' => DataEntry::whereMonth('created_at', now()->subMonth()->month)->count(),
            'expenditures_this_month' => Expenditure::whereMonth('created_at', now()->month)->count(),
            'expenditures_last_month' => Expenditure::whereMonth('created_at', now()->subMonth()->month)->count(),
        ];

        // Get insights and recommendations (placeholder for AI integration)
        $insights = [
            [
                'type' => 'budget_utilization',
                'severity' => 'info',
                'title' => 'Budget Utilization Analysis',
                'message' => 'Overall budget utilization is at ' . round(($stats['total_spent'] / max($stats['total_budget'], 1)) * 100, 2) . '%',
            ],
            [
                'type' => 'data_verification',
                'severity' => 'warning',
                'title' => 'Data Verification Rate',
                'message' => 'Verification rate is at ' . round(($stats['verified_entries'] / max($stats['total_data_entries'], 1)) * 100, 2) . '%',
            ],
            [
                'type' => 'project_progress',
                'severity' => 'success',
                'title' => 'Active Projects',
                'message' => $stats['active_projects'] . ' projects are currently active',
            ],
        ];

        return Inertia::render('Admin/AIReports/Index', [
            'stats' => $stats,
            'recentActivity' => $recentActivity,
            'insights' => $insights,
            'filters' => $request->only(['date_from', 'date_to', 'project', 'theme']),
        ]);
    }
}
