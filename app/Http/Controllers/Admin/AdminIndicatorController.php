<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Indicator;
use App\Models\Theme;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminIndicatorController extends Controller
{
    /**
     * Display a listing of indicators
     */
    public function index(Request $request)
    {
        $query = Indicator::with('theme');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by theme
        if ($request->filled('theme')) {
            $query->where('theme_id', $request->theme);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $indicators = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('Admin/Indicators/Index', [
            'indicators' => $indicators,
            'filters' => $request->only(['search', 'theme', 'type', 'status']),
            'themes' => Theme::select('id', 'name')->get(),
        ]);
    }

    /**
     * Show the form for creating a new indicator
     */
    public function create()
    {
        return Inertia::render('Admin/Indicators/Create', [
            'themes' => Theme::select('id', 'name')->get(),
        ]);
    }

    /**
     * Store a newly created indicator
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:indicators,code',
            'name' => 'required|string|max:500',
            'description' => 'nullable|string',
            'theme_id' => 'nullable|exists:themes,id',
            'type' => 'required|in:output,outcome,impact,process',
            'unit' => 'required|in:number,percentage,currency,text',
            'unit_label' => 'nullable|string|max:50',
            'baseline_value' => 'nullable|numeric',
            'target_value' => 'nullable|numeric',
            'direction' => 'required|in:increasing,decreasing,stable',
            'frequency' => 'required|in:daily,weekly,monthly,quarterly,annually',
            'data_source' => 'nullable|string|max:255',
            'calculation_method' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        Indicator::create($validated);

        return redirect()->route('admin.indicators.index')
            ->with('success', 'Indicator created successfully.');
    }

    /**
     * Display the specified indicator
     */
    public function show(Indicator $indicator)
    {
        $indicator->load(['theme', 'projectIndicators.project']);

        // Get performance data
        $performance = $indicator->projectIndicators()
            ->with('project')
            ->get()
            ->map(function ($pi) {
                return [
                    'project' => $pi->project,
                    'target' => $pi->target_value,
                    'actual' => $pi->actual_value,
                    'achievement' => $pi->achievement_percentage,
                    'status' => $pi->status,
                ];
            });

        return Inertia::render('Admin/Indicators/Show', [
            'indicator' => $indicator,
            'performance' => $performance,
        ]);
    }

    /**
     * Show the form for editing the specified indicator
     */
    public function edit(Indicator $indicator)
    {
        return Inertia::render('Admin/Indicators/Edit', [
            'indicator' => $indicator,
            'themes' => Theme::select('id', 'name')->get(),
        ]);
    }

    /**
     * Update the specified indicator
     */
    public function update(Request $request, Indicator $indicator)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:indicators,code,' . $indicator->id,
            'name' => 'required|string|max:500',
            'description' => 'nullable|string',
            'theme_id' => 'nullable|exists:themes,id',
            'type' => 'required|in:output,outcome,impact,process',
            'unit' => 'required|in:number,percentage,currency,text',
            'unit_label' => 'nullable|string|max:50',
            'baseline_value' => 'nullable|numeric',
            'target_value' => 'nullable|numeric',
            'direction' => 'required|in:increasing,decreasing,stable',
            'frequency' => 'required|in:daily,weekly,monthly,quarterly,annually',
            'data_source' => 'nullable|string|max:255',
            'calculation_method' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $indicator->update($validated);

        return redirect()->route('admin.indicators.index')
            ->with('success', 'Indicator updated successfully.');
    }

    /**
     * Remove the specified indicator
     */
    public function destroy(Indicator $indicator)
    {
        $indicator->delete();

        return redirect()->route('admin.indicators.index')
            ->with('success', 'Indicator deleted successfully.');
    }
}
