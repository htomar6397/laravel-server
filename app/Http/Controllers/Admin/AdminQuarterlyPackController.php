<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Project;
use App\Models\DataEntry;
use App\Models\Expenditure;
use App\Models\ProjectResult;

/**
 * Admin Quarterly Pack Controller
 */
class AdminQuarterlyPackController extends Controller
{
    /**
     * Display quarterly pack dashboard
     */
    public function index(Request $request)
    {
        // Determine current quarter
        $currentQuarter = ceil(now()->month / 3);
        $selectedQuarter = $request->quarter ?? $currentQuarter;
        $selectedYear = $request->year ?? now()->year;

        // Calculate quarter date range
        $quarterStart = now()->setYear($selectedYear)->setMonth(($selectedQuarter - 1) * 3 + 1)->startOfMonth();
        $quarterEnd = now()->setYear($selectedYear)->setMonth($selectedQuarter * 3)->endOfMonth();

        // Get quarterly statistics
        $stats = [
            'projects_active' => Project::where('status', 'active')->count(),
            'data_entries' => DataEntry::whereBetween('created_at', [$quarterStart, $quarterEnd])->count(),
            'expenditures' => Expenditure::whereBetween('created_at', [$quarterStart, $quarterEnd])->count(),
            'total_spent' => Expenditure::whereBetween('created_at', [$quarterStart, $quarterEnd])
                ->where('status', 'approved')
                ->sum('amount'),
            'results_achieved' => ProjectResult::whereBetween('created_at', [$quarterStart, $quarterEnd])->count(),
        ];

        // Get project summaries for the quarter
        $projects = Project::with(['theme', 'organizationalUnit'])
            ->withCount([
                'dataEntries' => function ($q) use ($quarterStart, $quarterEnd) {
                    $q->whereBetween('created_at', [$quarterStart, $quarterEnd]);
                },
                'expenditures' => function ($q) use ($quarterStart, $quarterEnd) {
                    $q->whereBetween('created_at', [$quarterStart, $quarterEnd]);
                },
            ])
            ->where('status', 'active')
            ->get();

        return Inertia::render('Admin/QuarterlyPack/Index', [
            'stats' => $stats,
            'projects' => $projects,
            'selectedQuarter' => $selectedQuarter,
            'selectedYear' => $selectedYear,
            'quarterStart' => $quarterStart->format('Y-m-d'),
            'quarterEnd' => $quarterEnd->format('Y-m-d'),
        ]);
    }

    /**
     * Generate and download quarterly pack report
     */
    public function download(Request $request)
    {
        $request->validate([
            'quarter' => 'required|integer|min:1|max:4',
            'year' => 'required|integer|min:2020|max:2100',
        ]);

        // TODO: Implement PDF/Excel generation logic
        return response()->json([
            'message' => 'Quarterly pack generation initiated',
            'quarter' => $request->quarter,
            'year' => $request->year,
        ]);
    }
}
