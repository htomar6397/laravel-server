<?php

namespace App\Http\Controllers;

use App\Models\{Project, User, Expenditure, PhotoCapture, DataEntry, Notification, Theme, OrganizationalUnit};
use App\Traits\ApiResponseTrait;
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
    use ApiResponseTrait;
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

        return $this->successResponse([
            'project_stats' => $this->getProjectStats($orgUnitId, $startDate, $endDate),
            'financial_stats' => $this->getFinancialStats($orgUnitId, $startDate, $endDate),
            'data_stats' => $this->getDataEntryStats($orgUnitId, $startDate, $endDate),
            'recent_activities' => $this->getRecentActivities($orgUnitId),
            'upcoming_deadlines' => $this->getUpcomingDeadlines($orgUnitId),
            'performance_overview' => $this->getPerformanceOverview($orgUnitId),
            'notifications' => $user->unreadNotifications()->take(5)->get()->map->getSummary(),
        ], 'Dashboard data retrieved successfully');
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

        return $this->successResponse([
            'notifications' => $notifications,
            'unread_count' => $user->unreadNotifications()->count(),
        ], 'Notifications retrieved successfully');
    }

    /**
     * Mark notification as read
     */
    public function markNotificationRead(Request $request, $id)
    {
        $user = $request->user();
        $notification = $user->notifications()->findOrFail($id);
        
        $notification->markAsRead();

        return $this->successResponse(null, 'Notification marked as read');
    }

    /**
     * Mark all notifications as read
     */
    public function markAllNotificationsRead(Request $request)
    {
        $user = $request->user();
        $user->unreadNotifications()->update(['is_read' => true, 'read_at' => now()]);

        return $this->successResponse(null, 'All notifications marked as read');
    }

    /**
     * Get dashboard statistics (API endpoint)
     */
    public function stats(Request $request)
    {
        $user = $request->user();
        $orgUnitId = $user->org_unit_id;

        $startDate = $request->get('start_date', now()->subMonths(12)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        return $this->successResponse([
            'projects' => $this->getProjectStats($orgUnitId, $startDate, $endDate),
            'financial' => $this->getFinancialStats($orgUnitId, $startDate, $endDate),
            'data_entries' => $this->getDataEntryStats($orgUnitId, $startDate, $endDate),
            'performance' => $this->getPerformanceOverview($orgUnitId),
            'themes' => $this->getThemeDistribution($orgUnitId),
            'metadata' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'generated_at' => now()->toISOString(),
            ]
        ], 'Dashboard statistics retrieved successfully');
    }

    /**
     * Get project-specific statistics (API endpoint)
     */
    public function projectStats(Request $request, $projectId)
    {
        $user = $request->user();
        $project = Project::with(['theme', 'organizationalUnit', 'indicators'])->findOrFail($projectId);

        // Check if user has access to this project
        if ($user->org_unit_id && $user->org_unit_id !== $project->org_unit_id && !$user->hasRole('Admin')) {
            return $this->errorResponse('Unauthorized access to this project', null, 403);
        }

        // Get date range
        $startDate = $request->get('start_date', $project->start_date ?? now()->subMonths(6)->format('Y-m-d'));
        $endDate = $request->get('end_date', $project->end_date ?? now()->format('Y-m-d'));

        // Data Entry Statistics
        $dataEntryStats = DataEntry::where('project_id', $projectId)
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->selectRaw('
                COUNT(*) as total_entries,
                COUNT(CASE WHEN verification_status = "verified" THEN 1 END) as verified_entries,
                COUNT(CASE WHEN verification_status = "pending" THEN 1 END) as pending_entries,
                COUNT(CASE WHEN verification_status = "rejected" THEN 1 END) as rejected_entries
            ')
            ->first();

        // Financial Statistics
        $financialStats = Expenditure::where('project_id', $projectId)
            ->whereBetween('expenditure_date', [$startDate, $endDate])
            ->selectRaw('
                COUNT(*) as total_expenditures,
                SUM(amount) as total_spent,
                SUM(CASE WHEN approval_status = "approved" THEN amount ELSE 0 END) as approved_amount,
                SUM(CASE WHEN approval_status = "pending" THEN amount ELSE 0 END) as pending_amount
            ')
            ->first();

        // Photo Statistics
        $photoStats = PhotoCapture::where('project_id', $projectId)
            ->whereBetween('captured_at', [$startDate, $endDate])
            ->selectRaw('
                COUNT(*) as total_photos,
                COUNT(CASE WHEN latitude IS NOT NULL AND longitude IS NOT NULL THEN 1 END) as geotagged_photos
            ')
            ->first();

        // Indicator Performance
        $indicatorPerformance = $project->indicators->map(function ($indicator) use ($projectId, $startDate, $endDate) {
            $entries = DataEntry::where('project_id', $projectId)
                ->where('indicator_id', $indicator->id)
                ->whereBetween('entry_date', [$startDate, $endDate])
                ->where('verification_status', 'verified')
                ->sum('actual_value');

            return [
                'indicator_id' => $indicator->id,
                'indicator_name' => $indicator->name,
                'target' => $indicator->pivot->target_value ?? $indicator->target_value,
                'achieved' => $entries,
                'percentage' => $indicator->pivot->target_value 
                    ? round(($entries / $indicator->pivot->target_value) * 100, 2) 
                    : 0,
            ];
        });

        return $this->successResponse([
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'code' => $project->code,
                'status' => $project->status,
                'budget' => $project->budget,
                'start_date' => $project->start_date,
                'end_date' => $project->end_date,
                'theme' => $project->theme?->name,
                'org_unit' => $project->organizationalUnit?->name,
            ],
            'data_entries' => [
                'total' => $dataEntryStats->total_entries ?? 0,
                'verified' => $dataEntryStats->verified_entries ?? 0,
                'pending' => $dataEntryStats->pending_entries ?? 0,
                'rejected' => $dataEntryStats->rejected_entries ?? 0,
            ],
            'financial' => [
                'budget' => $project->budget ?? 0,
                'total_spent' => $financialStats->total_spent ?? 0,
                'approved_amount' => $financialStats->approved_amount ?? 0,
                'pending_amount' => $financialStats->pending_amount ?? 0,
                'remaining_budget' => ($project->budget ?? 0) - ($financialStats->approved_amount ?? 0),
                'expenditure_count' => $financialStats->total_expenditures ?? 0,
            ],
            'photos' => [
                'total' => $photoStats->total_photos ?? 0,
                'geotagged' => $photoStats->geotagged_photos ?? 0,
            ],
            'indicators' => $indicatorPerformance,
            'metadata' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'generated_at' => now()->toISOString(),
            ]
        ], 'Project statistics retrieved successfully');
    }
}
