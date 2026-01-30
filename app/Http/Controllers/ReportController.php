<?php

namespace App\Http\Controllers;

use App\Models\{Project, Expenditure, DataEntry, PhotoCapture, User, Theme, OrganizationalUnit};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * ReportController
 * 
 * Handles reporting operations for the KMC M&E System
 */
class ReportController extends Controller
{
    /**
     * Show reports dashboard
     */
    public function index()
    {
        return view('reports.index');
    }

    /**
     * Generate project performance report
     */
    public function projectPerformance(Request $request)
    {
        $startDate = $request->get('start_date', now()->subMonths(12)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $orgUnitId = $request->get('org_unit_id');
        $themeId = $request->get('theme_id');

        $query = Project::with(['theme', 'organizationalUnit', 'creator'])
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($orgUnitId) {
            $query->where('org_unit_id', $orgUnitId);
        }

        if ($themeId) {
            $query->where('theme_id', $themeId);
        }

        $projects = $query->get();

        // Calculate statistics
        $totalProjects = $projects->count();
        $activeProjects = $projects->where('status', 'ACTIVE')->count();
        $completedProjects = $projects->where('status', 'COMPLETED')->count();
        $totalBudget = $projects->sum('budget');
        $totalExpenditure = $projects->sum(function ($project) {
            return $project->total_expenditures;
        });
        $averageCompletion = $projects->avg('completion_percentage');

        // Group by status
        $byStatus = $projects->groupBy('status')->map->count();

        // Group by theme
        $byTheme = $projects->groupBy('theme.name')->map(function ($themeProjects) {
            return [
                'count' => $themeProjects->count(),
                'budget' => $themeProjects->sum('budget'),
                'expenditure' => $themeProjects->sum(function ($project) {
                    return $project->total_expenditures;
                }),
                'completion' => $themeProjects->avg('completion_percentage'),
            ];
        });

        // Group by organizational unit
        $byOrgUnit = $projects->groupBy('organizationalUnit.name')->map(function ($unitProjects) {
            return [
                'count' => $unitProjects->count(),
                'budget' => $unitProjects->sum('budget'),
                'expenditure' => $unitProjects->sum(function ($project) {
                    return $project->total_expenditures;
                }),
                'completion' => $unitProjects->avg('completion_percentage'),
            ];
        });

        // Health status distribution
        $healthStatus = $projects->groupBy('health_status')->map->count();

        // Projects at risk
        $atRiskProjects = $projects->filter(function ($project) {
            return !$project->is_on_track;
        });

        return view('reports.project-performance', compact(
            'projects',
            'totalProjects',
            'activeProjects',
            'completedProjects',
            'totalBudget',
            'totalExpenditure',
            'averageCompletion',
            'byStatus',
            'byTheme',
            'byOrgUnit',
            'healthStatus',
            'atRiskProjects',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Generate financial report
     */
    public function financial(Request $request)
    {
        $startDate = $request->get('start_date', now()->subMonths(12)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $orgUnitId = $request->get('org_unit_id');
        $projectId = $request->get('project_id');

        $query = Expenditure::with(['project', 'enterer', 'approver', 'verifier'])
            ->whereBetween('expenditure_date', [$startDate, $endDate]);

        if ($orgUnitId) {
            $query->whereHas('project', function ($q) use ($orgUnitId) {
                $q->where('org_unit_id', $orgUnitId);
            });
        }

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $expenditures = $query->get();

        // Calculate statistics
        $totalExpenditures = $expenditures->count();
        $totalAmount = $expenditures->sum('amount');
        $pendingAmount = $expenditures->where('status', 'PENDING')->sum('amount');
        $approvedAmount = $expenditures->where('status', 'APPROVED')->sum('amount');
        $verifiedAmount = $expenditures->where('status', 'VERIFIED')->sum('amount');

        // Group by category
        $byCategory = $expenditures->groupBy('category')->map(function ($categoryExpenditures) {
            return [
                'count' => $categoryExpenditures->count(),
                'amount' => $categoryExpenditures->sum('amount'),
            ];
        });

        // Group by project
        $byProject = $expenditures->groupBy('project.name')->map(function ($projectExpenditures) {
            return [
                'count' => $projectExpenditures->count(),
                'amount' => $projectExpenditures->sum('amount'),
            ];
        });

        // Group by status
        $byStatus = $expenditures->groupBy('status')->map(function ($statusExpenditures) {
            return [
                'count' => $statusExpenditures->count(),
                'amount' => $statusExpenditures->sum('amount'),
            ];
        });

        // Monthly trend
        $monthlyTrend = $expenditures
            ->groupBy(function ($item) {
                return Carbon::parse($item->expenditure_date)->format('Y-m');
            })
            ->map(function ($monthExpenditures) {
                return [
                    'count' => $monthExpenditures->count(),
                    'amount' => $monthExpenditures->sum('amount'),
                ];
            })
            ->sortKeys();

        // Top expenditures
        $topExpenditures = $expenditures->sortByDesc('amount')->take(10);

        return view('reports.financial', compact(
            'expenditures',
            'totalExpenditures',
            'totalAmount',
            'pendingAmount',
            'approvedAmount',
            'verifiedAmount',
            'byCategory',
            'byProject',
            'byStatus',
            'monthlyTrend',
            'topExpenditures',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Generate data quality report
     */
    public function dataQuality(Request $request)
    {
        $startDate = $request->get('start_date', now()->subMonths(12)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $projectId = $request->get('project_id');
        $indicatorId = $request->get('indicator_id');

        $query = DataEntry::with(['project', 'indicator', 'enterer', 'verifier'])
            ->whereBetween('data_date', [$startDate, $endDate]);

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        if ($indicatorId) {
            $query->where('indicator_id', $indicatorId);
        }

        $dataEntries = $query->get();

        // Calculate statistics
        $totalEntries = $dataEntries->count();
        $verifiedEntries = $dataEntries->where('verification_status', 'VERIFIED')->count();
        $pendingEntries = $dataEntries->where('verification_status', 'PENDING')->count();
        $rejectedEntries = $dataEntries->where('verification_status', 'REJECTED')->count();
        $verificationRate = $totalEntries > 0 ? ($verifiedEntries / $totalEntries) * 100 : 0;

        // Group by frequency
        $byFrequency = $dataEntries->groupBy('frequency')->map->count();

        // Group by project
        $byProject = $dataEntries->groupBy('project.name')->map(function ($projectEntries) {
            $verified = $projectEntries->where('verification_status', 'VERIFIED')->count();
            return [
                'total' => $projectEntries->count(),
                'verified' => $verified,
                'verification_rate' => $projectEntries->count() > 0 ? ($verified / $projectEntries->count()) * 100 : 0,
            ];
        });

        // Group by indicator
        $byIndicator = $dataEntries->groupBy('indicator.name')->map(function ($indicatorEntries) {
            $verified = $indicatorEntries->where('verification_status', 'VERIFIED')->count();
            return [
                'total' => $indicatorEntries->count(),
                'verified' => $verified,
                'verification_rate' => $indicatorEntries->count() > 0 ? ($verified / $indicatorEntries->count()) * 100 : 0,
            ];
        });

        // Monthly trend
        $monthlyTrend = $dataEntries
            ->groupBy(function ($item) {
                return Carbon::parse($item->data_date)->format('Y-m');
            })
            ->map(function ($monthEntries) {
                $verified = $monthEntries->where('verification_status', 'VERIFIED')->count();
                return [
                    'total' => $monthEntries->count(),
                    'verified' => $verified,
                    'verification_rate' => $monthEntries->count() > 0 ? ($verified / $monthEntries->count()) * 100 : 0,
                ];
            })
            ->sortKeys();

        // Data entry by users
        $byUser = $dataEntries->groupBy('enterer.full_name')->map(function ($userEntries) {
            return [
                'total' => $userEntries->count(),
                'verified' => $userEntries->where('verification_status', 'VERIFIED')->count(),
            ];
        });

        return view('reports.data-quality', compact(
            'dataEntries',
            'totalEntries',
            'verifiedEntries',
            'pendingEntries',
            'rejectedEntries',
            'verificationRate',
            'byFrequency',
            'byProject',
            'byIndicator',
            'monthlyTrend',
            'byUser',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Generate user activity report
     */
    public function userActivity(Request $request)
    {
        $startDate = $request->get('start_date', now()->subMonths(12)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $userId = $request->get('user_id');
        $action = $request->get('action');

        $query = \App\Models\AuditLog::with(['user', 'entity'])
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($action) {
            $query->where('action', $action);
        }

        $logs = $query->latest()->get();

        // Calculate statistics
        $totalActions = $logs->count();
        $uniqueUsers = $logs->pluck('user_id')->unique()->count();

        // Group by action
        $byAction = $logs->groupBy('action')->map->count();

        // Group by user
        $byUser = $logs->groupBy('user.full_name')->map->count();

        // Group by entity type
        $byEntityType = $logs->groupBy('entity_type')->map->count();

        // Daily activity
        $dailyActivity = $logs
            ->groupBy(function ($item) {
                return Carbon::parse($item->created_at)->format('Y-m-d');
            })
            ->map->count()
            ->sortKeys()
            ->take(30); // Last 30 days

        // Top active users
        $topUsers = $logs->groupBy('user.full_name')
            ->map->count()
            ->sortDesc()
            ->take(10);

        return view('reports.user-activity', compact(
            'logs',
            'totalActions',
            'uniqueUsers',
            'byAction',
            'byUser',
            'byEntityType',
            'dailyActivity',
            'topUsers',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Generate comprehensive dashboard report
     */
    public function dashboard(Request $request)
    {
        $startDate = $request->get('start_date', now()->subMonths(12)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Project metrics
        $projectMetrics = [
            'total' => Project::whereBetween('created_at', [$startDate, $endDate])->count(),
            'active' => Project::where('status', 'ACTIVE')->count(),
            'completed' => Project::where('status', 'COMPLETED')->count(),
            'total_budget' => Project::sum('budget'),
            'average_completion' => Project::avg('completion_percentage'),
        ];

        // Financial metrics
        $financialMetrics = [
            'total_expenditures' => Expenditure::whereBetween('expenditure_date', [$startDate, $endDate])->count(),
            'total_amount' => Expenditure::whereBetween('expenditure_date', [$startDate, $endDate])->sum('amount'),
            'pending_amount' => Expenditure::whereBetween('expenditure_date', [$startDate, $endDate])
                ->where('status', 'PENDING')->sum('amount'),
            'verified_amount' => Expenditure::whereBetween('expenditure_date', [$startDate, $endDate])
                ->where('status', 'VERIFIED')->sum('amount'),
        ];

        // Data metrics
        $dataMetrics = [
            'total_entries' => DataEntry::whereBetween('data_date', [$startDate, $endDate])->count(),
            'verified_entries' => DataEntry::whereBetween('data_date', [$startDate, $endDate])
                ->where('verification_status', 'VERIFIED')->count(),
            'verification_rate' => 0,
        ];

        if ($dataMetrics['total_entries'] > 0) {
            $dataMetrics['verification_rate'] = ($dataMetrics['verified_entries'] / $dataMetrics['total_entries']) * 100;
        }

        // Photo metrics
        $photoMetrics = [
            'total_photos' => PhotoCapture::whereBetween('captured_at', [$startDate, $endDate])->count(),
            'with_gps' => PhotoCapture::whereBetween('captured_at', [$startDate, $endDate])
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->count(),
        ];

        // User metrics
        $userMetrics = [
            'total_users' => User::count(),
            'active_users' => User::active()->count(),
            'new_users' => User::whereBetween('created_at', [$startDate, $endDate])->count(),
        ];

        return view('reports.dashboard', compact(
            'projectMetrics',
            'financialMetrics',
            'dataMetrics',
            'photoMetrics',
            'userMetrics',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Export report to PDF/Excel
     */
    public function export(Request $request)
    {
        $type = $request->get('type');
        $format = $request->get('format', 'pdf');

        // Implementation would depend on the specific export library used
        // This is a placeholder for the export functionality

        return response()->json([
            'message' => 'Export functionality would be implemented here',
            'type' => $type,
            'format' => $format,
        ]);
    }

    /**
     * Get report data via API
     */
    public function apiData(Request $request)
    {
        $type = $request->get('type');
        $startDate = $request->get('start_date', now()->subMonths(12)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        switch ($type) {
            case 'project_performance':
                return $this->getProjectPerformanceData($startDate, $endDate);
            
            case 'financial':
                return $this->getFinancialData($startDate, $endDate);
            
            case 'data_quality':
                return $this->getDataQualityData($startDate, $endDate);
            
            case 'user_activity':
                return $this->getUserActivityData($startDate, $endDate);
            
            default:
                return response()->json(['error' => 'Invalid report type'], 400);
        }
    }

    /**
     * Get project performance data for API
     */
    private function getProjectPerformanceData($startDate, $endDate)
    {
        $projects = Project::with(['theme', 'organizationalUnit'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        return response()->json([
            'total_projects' => $projects->count(),
            'active_projects' => $projects->where('status', 'ACTIVE')->count(),
            'completed_projects' => $projects->where('status', 'COMPLETED')->count(),
            'total_budget' => $projects->sum('budget'),
            'total_expenditure' => $projects->sum(function ($project) {
                return $project->total_expenditures;
            }),
            'average_completion' => $projects->avg('completion_percentage'),
            'by_status' => $projects->groupBy('status')->map->count(),
            'by_theme' => $projects->groupBy('theme.name')->map->count(),
            'by_org_unit' => $projects->groupBy('organizationalUnit.name')->map->count(),
        ]);
    }

    /**
     * Get financial data for API
     */
    private function getFinancialData($startDate, $endDate)
    {
        $expenditures = Expenditure::with(['project'])
            ->whereBetween('expenditure_date', [$startDate, $endDate])
            ->get();

        return response()->json([
            'total_expenditures' => $expenditures->count(),
            'total_amount' => $expenditures->sum('amount'),
            'by_status' => $expenditures->groupBy('status')->map(function ($statusExpenditures) {
                return [
                    'count' => $statusExpenditures->count(),
                    'amount' => $statusExpenditures->sum('amount'),
                ];
            }),
            'by_category' => $expenditures->groupBy('category')->map(function ($categoryExpenditures) {
                return [
                    'count' => $categoryExpenditures->count(),
                    'amount' => $categoryExpenditures->sum('amount'),
                ];
            }),
            'monthly_trend' => $expenditures
                ->groupBy(function ($item) {
                    return Carbon::parse($item->expenditure_date)->format('Y-m');
                })
                ->map(function ($monthExpenditures) {
                    return [
                        'count' => $monthExpenditures->count(),
                        'amount' => $monthExpenditures->sum('amount'),
                    ];
                })
                ->sortKeys(),
        ]);
    }

    /**
     * Get data quality data for API
     */
    private function getDataQualityData($startDate, $endDate)
    {
        $dataEntries = DataEntry::with(['project', 'indicator'])
            ->whereBetween('data_date', [$startDate, $endDate])
            ->get();

        $totalEntries = $dataEntries->count();
        $verifiedEntries = $dataEntries->where('verification_status', 'VERIFIED')->count();

        return response()->json([
            'total_entries' => $totalEntries,
            'verified_entries' => $verifiedEntries,
            'verification_rate' => $totalEntries > 0 ? ($verifiedEntries / $totalEntries) * 100 : 0,
            'by_status' => $dataEntries->groupBy('verification_status')->map->count(),
            'by_frequency' => $dataEntries->groupBy('frequency')->map->count(),
            'by_project' => $dataEntries->groupBy('project.name')->map->count(),
            'by_indicator' => $dataEntries->groupBy('indicator.name')->map->count(),
        ]);
    }

    /**
     * Get user activity data for API
     */
    private function getUserActivityData($startDate, $endDate)
    {
        $logs = \App\Models\AuditLog::with(['user'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        return response()->json([
            'total_actions' => $logs->count(),
            'unique_users' => $logs->pluck('user_id')->unique()->count(),
            'by_action' => $logs->groupBy('action')->map->count(),
            'by_user' => $logs->groupBy('user.full_name')->map->count(),
            'by_entity_type' => $logs->groupBy('entity_type')->map->count(),
            'daily_activity' => $logs
                ->groupBy(function ($item) {
                    return Carbon::parse($item->created_at)->format('Y-m-d');
                })
                ->map->count()
                ->sortKeys()
                ->take(30),
        ]);
    }
}
