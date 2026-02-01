<?php

namespace App\Http\Controllers;

use App\Models\{Indicator, Theme};
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

/**
 * IndicatorController
 * 
 * Handles indicator management operations for the KMC M&E System
 */
class IndicatorController extends Controller
{
    use ApiResponseTrait;
    /**
     * Display a listing of indicators
     */
    public function index(Request $request)
    {
        $query = Indicator::with(['theme', 'projectIndicators']);

        // Apply filters
        if ($request->has('theme') && $request->theme != '') {
            $query->byTheme($request->theme);
        }

        if ($request->has('type') && $request->type != '') {
            $query->byType($request->type);
        }

        if ($request->has('frequency') && $request->frequency != '') {
            $query->byFrequency($request->frequency);
        }

        if ($request->has('status') && $request->status != '') {
            if ($request->status === 'active') {
                $query->active();
            } else {
                $query->where('is_active', false);
            }
        }

        if ($request->has('search') && $request->search != '') {
            $query->search($request->search);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'code');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $indicators = $query->paginate(20);

        // Get filter options
        $themes = Theme::active()->ordered()->get();
        $types = Indicator::types();
        $frequencies = Indicator::frequencies();

        // Calculate statistics
        $stats = [
            'total' => Indicator::count(),
            'active' => Indicator::active()->count(),
            'quantitative' => Indicator::byType('QUANTITATIVE')->count(),
            'qualitative' => Indicator::byType('QUALITATIVE')->count(),
            'with_projects' => Indicator::has('projectIndicators')->count(),
        ];

        return view('indicators.index', compact(
            'indicators',
            'themes',
            'types',
            'frequencies',
            'stats'
        ));
    }

    /**
     * Show the form for creating a new indicator
     */
    public function create()
    {
        $themes = Theme::active()->ordered()->get();
        $types = Indicator::types();
        $units = Indicator::units();
        $directions = Indicator::directions();
        $frequencies = Indicator::frequencies();

        return view('indicators.create', compact(
            'themes',
            'types',
            'units',
            'directions',
            'frequencies'
        ));
    }

    /**
     * Store a newly created indicator
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:indicators',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'theme_id' => 'nullable|exists:themes,id',
            'type' => 'required|in:' . implode(',', Indicator::types()),
            'unit' => 'required|in:' . implode(',', Indicator::units()),
            'unit_label' => 'nullable|string|max:50',
            'baseline_value' => 'nullable|numeric|min:0',
            'target_value' => 'nullable|numeric|min:0',
            'direction' => 'required|in:' . implode(',', Indicator::directions()),
            'frequency' => 'required|in:' . implode(',', Indicator::frequencies()),
            'data_source' => 'nullable|string|max:255',
            'calculation_method' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;

        $indicator = Indicator::create($validated);

        return redirect()
            ->route('indicators.show', $indicator)
            ->with('success', 'Indicator created successfully!');
    }

    /**
     * Display the specified indicator
     */
    public function show(Indicator $indicator)
    {
        $indicator->load(['theme', 'projectIndicators.project', 'dataEntries']);

        $statistics = $indicator->getStatistics();

        return view('indicators.show', compact('indicator', 'statistics'));
    }

    /**
     * Show the form for editing the specified indicator
     */
    public function edit(Indicator $indicator)
    {
        $themes = Theme::active()->ordered()->get();
        $types = Indicator::types();
        $units = Indicator::units();
        $directions = Indicator::directions();
        $frequencies = Indicator::frequencies();

        return view('indicators.edit', compact(
            'indicator',
            'themes',
            'types',
            'units',
            'directions',
            'frequencies'
        ));
    }

    /**
     * Update the specified indicator
     */
    public function update(Request $request, Indicator $indicator)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:indicators,code,' . $indicator->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'theme_id' => 'nullable|exists:themes,id',
            'type' => 'required|in:' . implode(',', Indicator::types()),
            'unit' => 'required|in:' . implode(',', Indicator::units()),
            'unit_label' => 'nullable|string|max:50',
            'baseline_value' => 'nullable|numeric|min:0',
            'target_value' => 'nullable|numeric|min:0',
            'direction' => 'required|in:' . implode(',', Indicator::directions()),
            'frequency' => 'required|in:' . implode(',', Indicator::frequencies()),
            'data_source' => 'nullable|string|max:255',
            'calculation_method' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $indicator->update($validated);

        return redirect()
            ->route('indicators.show', $indicator)
            ->with('success', 'Indicator updated successfully!');
    }

    /**
     * Remove the specified indicator
     */
    public function destroy(Indicator $indicator)
    {
        if (!$indicator->canBeDeleted()) {
            return back()
                ->with('error', 'Cannot delete indicator with associated projects.');
        }

        $indicator->delete();

        return redirect()
            ->route('indicators.index')
            ->with('success', 'Indicator deleted successfully!');
    }

    /**
     * Toggle indicator active status
     */
    public function toggleStatus(Indicator $indicator)
    {
        $indicator->is_active = !$indicator->is_active;
        $indicator->save();

        $status = $indicator->is_active ? 'activated' : 'deactivated';

        return back()
            ->with('success', "Indicator {$status} successfully!");
    }

    /**
     * Get indicators for API (for dropdowns, etc.)
     */
    public function apiIndex(Request $request)
    {
        $query = Indicator::active();

        if ($request->has('theme_id')) {
            $query->byTheme($request->theme_id);
        }

        if ($request->has('type')) {
            $query->byType($request->type);
        }

        if ($request->has('with_stats')) {
            $indicators = $query->withCount(['projectIndicators', 'dataEntries'])->get();
        } else {
            $indicators = $query->get();
        }

        return $this->successResponse(
            $indicators,
            'Indicators retrieved successfully'
        );
    }

    /**
     * Get indicator details (API)
     */
    public function showApi(Indicator $indicator)
    {
        return $this->successResponse(
            ['indicator' => $indicator->getSummary()],
            'Indicator details retrieved successfully'
        );
    }

    /**
     * Get indicator performance data (API)
     */
    public function performance(Indicator $indicator, Request $request)
    {
        $startDate = $request->get('start_date', now()->subMonths(12)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $dataEntries = $indicator->dataEntries()
            ->whereBetween('data_date', [$startDate, $endDate])
            ->verified()
            ->orderBy('data_date')
            ->get();

        $projectIndicators = $indicator->projectIndicators()
            ->with('project')
            ->get();

        return $this->successResponse([
            'indicator' => $indicator->getSummary(),
            'data_entries' => $dataEntries->map(function ($entry) {
                return [
                    'date' => $entry->data_date->format('Y-m-d'),
                    'value' => $entry->value,
                    'formatted_value' => $entry->formatted_value,
                    'project' => $entry->project?->name,
                ];
            }),
            'project_indicators' => $projectIndicators->map(function ($pi) {
                return [
                    'project' => $pi->project->name,
                    'baseline' => $pi->baseline_value,
                    'target' => $pi->target_value,
                    'current' => $pi->current_value,
                    'achievement' => $pi->achievement_percentage,
                    'status' => $pi->achievement_status,
                ];
            }),
        ], 'Indicator performance data retrieved successfully');
    }
}
