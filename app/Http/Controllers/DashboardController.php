<?php

namespace App\Http\Controllers;

use App\Models\{Project, User, Expenditure, PhotoCapture, DataEntry, Notification, Theme, OrganizationalUnit};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * DashboardController
 * 
 * Handles dashboard operations for the KMC M&E System
 */
class DashboardController extends Controller
{
    /**
     * Display the main dashboard
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $orgUnitId = $user->org_unit_id;

        // Get date range for filtering
        $startDate = $request->get('start_date', now()->subMonths(12)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Project Statistics
        $projectStats = $this->getProjectStats($orgUnitId, $startDate, $endDate);
        
        // Financial Statistics
        $financialStats = $this->getFinancialStats($orgUnitId, $startDate, $endDate);
        
        // Data Entry Statistics
        $dataStats = $this->getDataEntryStats($orgUnitId, $startDate, $endDate);
        
        // Recent Activities
        $recentActivities = $this->getRecentActivities($orgUnitId);
        
        // Upcoming Deadlines
        $upcomingDeadlines = $this->getUpcomingDeadlines($orgUnitId);
        
        // Performance Overview
        $performanceOverview = $this->getPerformanceOverview($orgUnitId);
        
        // User Notifications
        $notifications = $user->unreadNotifications()->take(5)->get();
        
        // Theme Distribution
        $themeDistribution = $this->getThemeDistribution($orgUnitId);
        
        // Organizational Unit Performance
        $orgUnitPerformance = $this->getOrgUnitPerformance();

        return view('dashboard.index', compact(
            'projectStats',
            'financialStats',
            'dataStats',
            'recentActivities',
            'upcomingDeadlines',
            'performanceOverview',
            'notifications',
            'themeDistribution',
            'orgUnitPerformance',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Get project statistics
     */
    private function getProjectStats(?int $orgUnitId, string $startDate, string $endDate): array
    {
        $query = Project::whereBetween('created_at', [$startDate, $endDate]);
        
        if ($orgUnitId) {
            $query->where('org_unit_id', $orgUnitId);
        }

        $projects = $query->get();

        return [
            'total' => $projects->count(),
            'active' => $projects->where('status', 'ACTIVE')->count(),
            'completed' => $projects->where('status', 'COMPLETED')->count(),
            'at_risk' => $projects->filter(function ($project) {
                return $project->health_status === 'at_risk';
            })->count(),
            'total_budget' => $projects->sum('budget'),
            'total_expenditure' => $projects->sum(function ($project) {
                return $project->total_expenditures;
            }),
            'budget_utilization' => $projects->sum('budget') > 0 
                ? ($projects->sum(function ($project) {
                    return $project->total_expenditures;
                }) / $projects->sum('budget')) * 100 
                : 0,
            'average_completion' => $projects->avg('completion_percentage'),
        ];
    }

    /**
     * Get financial statistics
     */
    private function getFinancialStats(?int $orgUnitId, string $startDate, string $endDate): array
    {
        $query = Expenditure::whereBetween('expenditure_date', [$startDate, $endDate]);
        
        if ($orgUnitId) {
            $query->whereHas('project', function ($q) use ($orgUnitId) {
                $q->where('org_unit_id', $orgUnitId);
            });
        }

        $expenditures = $query->get();

        return [
            'total_expenditures' => $expenditures->count(),
            'total_amount' => $expenditures->sum('amount'),
            'pending_approval' => $expenditures->where('status', 'PENDING')->count(),
            'approved' => $expenditures->where('status', 'APPROVED')->count(),
            'verified' => $expenditures->where('status', 'VERIFIED')->count(),
            'by_category' => $expenditures->groupBy('category')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'amount' => $group->sum('amount'),
                ];
            }),
            'monthly_trend' => $expenditures
                ->groupBy(function ($item) {
                    return Carbon::parse($item->expenditure_date)->format('Y-m');
                })
                ->map(function ($group) {
                    return $group->sum('amount');
                })
                ->sortKeys(),
        ];
    }

    /**
     * Get data entry statistics
     */
    private function getDataEntryStats(?int $orgUnitId, string $startDate, string $endDate): array
    {
        $query = DataEntry::whereBetween('data_date', [$startDate, $endDate]);
        
        if ($orgUnitId) {
            $query->whereHas('project', function ($q) use ($orgUnitId) {
                $q->where('org_unit_id', $orgUnitId);
            });
        }

        $dataEntries = $query->get();

        return [
            'total_entries' => $dataEntries->count(),
            'verified' => $dataEntries->where('verification_status', 'VERIFIED')->count(),
            'pending' => $dataEntries->where('verification_status', 'PENDING')->count(),
            'rejected' => $dataEntries->where('verification_status', 'REJECTED')->count(),
            'by_frequency' => $dataEntries->groupBy('frequency')->map->count(),
            'monthly_trend' => $dataEntries
                ->groupBy(function ($item) {
                    return Carbon::parse($item->data_date)->format('Y-m');
                })
                ->map->count()
                ->sortKeys(),
        ];
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities(?int $orgUnitId): array
    {
        $query = \App\Models\AuditLog::with('user')
            ->latest()
            ->take(10);

        if ($orgUnitId) {
            // Filter activities related to user's org unit
            $query->where(function ($q) use ($orgUnitId) {
                $q->whereHas('user', function ($subQ) use ($orgUnitId) {
                    $subQ->where('org_unit_id', $orgUnitId);
                })
                ->orWhere(function ($subQ) use ($orgUnitId) {
                    $subQ->whereIn('entity_type', ['Project', 'Expenditure', 'DataEntry'])
                         ->whereHasMorph('entity', ['Project', 'Expenditure', 'DataEntry'], function ($morphQ) use ($orgUnitId) {
                             $morphQ->where('org_unit_id', $orgUnitId);
                         });
                });
            });
        }

        return $query->get()->map(function ($log) {
            return [
                'id' => $log->id,
                'action' => $log->action_label,
                'entity_type' => $log->entity_type_label,
                'user' => $log->user?->full_name,
                'time_ago' => $log->time_ago,
                'ip_address' => $log->ip_address,
            ];
        })->toArray();
    }

    /**
     * Get upcoming deadlines
     */
    private function getUpcomingDeadlines(?int $orgUnitId): array
    {
        $query = Project::where('status', 'ACTIVE')
            ->whereNotNull('end_date')
            ->where('end_date', '<=', now()->addDays(30))
            ->orderBy('end_date');

        if ($orgUnitId) {
            $query->where('org_unit_id', $orgUnitId);
        }

        return $query->get()->map(function ($project) {
            return [
                'id' => $project->id,
                'name' => $project->name,
                'end_date' => $project->end_date->format('Y-m-d'),
                'days_remaining' => $project->days_remaining,
                'completion' => $project->completion_percentage,
                'health_status' => $project->health_status,
            ];
        })->toArray();
    }

    /**
     * Get performance overview
     */
    private function getPerformanceOverview(?int $orgUnitId): array
    {
        $query = Project::where('status', 'ACTIVE');
        
        if ($orgUnitId) {
            $query->where('org_unit_id', $orgUnitId);
        }

        $projects = $query->get();

        $healthStatuses = $projects->groupBy('health_status')->map->count();
        $completionRanges = [
            '0-25%' => $projects->where('completion_percentage', '<=', 25)->count(),
            '26-50%' => $projects->whereBetween('completion_percentage', [26, 50])->count(),
            '51-75%' => $projects->whereBetween('completion_percentage', [51, 75])->count(),
            '76-100%' => $projects->where('completion_percentage', '>', 75)->count(),
        ];

        return [
            'health_status' => $healthStatuses,
            'completion_ranges' => $completionRanges,
            'on_track' => $projects->filter->is_on_track->count(),
            'at_risk' => $projects->filter(function ($project) {
                return !$project->is_on_track;
            })->count(),
        ];
    }

    /**
     * Get theme distribution
     */
    private function getThemeDistribution(?int $orgUnitId): array
    {
        $query = Project::with('theme');
        
        if ($orgUnitId) {
            $query->where('org_unit_id', $orgUnitId);
        }

        return $query->get()
            ->groupBy('theme.name')
            ->map(function ($projects, $themeName) {
                return [
                    'count' => $projects->count(),
                    'budget' => $projects->sum('budget'),
                    'completion' => $projects->avg('completion_percentage'),
                ];
            })
            ->toArray();
    }

    /**
     * Get organizational unit performance
     */
    private function getOrgUnitPerformance(): array
    {
        return OrganizationalUnit::with(['projects' => function ($query) {
                $query->select('org_unit_id', 'status', 'completion_percentage', 'budget');
            }])
            ->where('level', 'WARD')
            ->where('is_active', true)
            ->get()
            ->map(function ($orgUnit) {
                $projects = $orgUnit->projects;
                
                return [
                    'id' => $orgUnit->id,
                    'name' => $orgUnit->name,
                    'total_projects' => $projects->count(),
                    'active_projects' => $projects->where('status', 'ACTIVE')->count(),
                    'completed_projects' => $projects->where('status', 'COMPLETED')->count(),
                    'average_completion' => $projects->avg('completion_percentage'),
                    'total_budget' => $projects->sum('budget'),
                ];
            })
            ->toArray();
    }

    /**
     * Get dashboard data for API
     */
    public function apiDashboard(Request $request)
    {
        $user = $request->user();
        $orgUnitId = $user->org_unit_id;

        $startDate = $request->get('start_date', now()->subMonths(12)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        return response()->json([
            'project_stats' => $this->getProjectStats($orgUnitId, $startDate, $endDate),
            'financial_stats' => $this->getFinancialStats($orgUnitId, $startDate, $endDate),
            'data_stats' => $this->getDataEntryStats($orgUnitId, $startDate, $endDate),
            'recent_activities' => $this->getRecentActivities($orgUnitId),
            'upcoming_deadlines' => $this->getUpcomingDeadlines($orgUnitId),
            'performance_overview' => $this->getPerformanceOverview($orgUnitId),
            'notifications' => $user->unreadNotifications()->take(5)->get()->map->getSummary(),
        ]);
    }

    /**
     * Get real-time notifications
     */
    public function getNotifications(Request $request)
    {
        $user = $request->user();
        
        $notifications = $user->unreadNotifications()
            ->latest()
            ->take(10)
            ->get()
            ->map->getSummary();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markNotificationRead(Request $request, $id)
    {
        $user = $request->user();
        $notification = $user->notifications()->findOrFail($id);
        
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllNotificationsRead(Request $request)
    {
        $user = $request->user();
        $user->unreadNotifications()->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
