<?php

namespace App\Http\Controllers;

use App\Models\Theme;
use Illuminate\Http\Request;

/**
 * ThemeController
 * 
 * Handles theme management operations for the KMC M&E System
 */
class ThemeController extends Controller
{
    /**
     * Display a listing of themes
     */
    public function index(Request $request)
    {
        $query = Theme::withCount(['projects', 'indicators']);

        // Apply filters
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
        $sortBy = $request->get('sort_by', 'sort_order');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $themes = $query->paginate(20);

        // Calculate statistics
        $stats = [
            'total' => Theme::count(),
            'active' => Theme::active()->count(),
            'inactive' => Theme::where('is_active', false)->count(),
            'with_projects' => Theme::has('projects')->count(),
        ];

        return view('themes.index', compact('themes', 'stats'));
    }

    /**
     * Show the form for creating a new theme
     */
    public function create()
    {
        return view('themes.create');
    }

    /**
     * Store a newly created theme
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:themes',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $validated['is_active'] ?? true;

        $theme = Theme::create($validated);

        return redirect()
            ->route('themes.show', $theme)
            ->with('success', 'Theme created successfully!');
    }

    /**
     * Display the specified theme
     */
    public function show(Theme $theme)
    {
        $theme->load(['projects' => function ($query) {
            $query->latest()->take(5);
        }, 'indicators' => function ($query) {
            $query->latest()->take(5);
        }]);

        $statistics = $theme->getStatistics();

        return view('themes.show', compact('theme', 'statistics'));
    }

    /**
     * Show the form for editing the specified theme
     */
    public function edit(Theme $theme)
    {
        return view('themes.edit', compact('theme'));
    }

    /**
     * Update the specified theme
     */
    public function update(Request $request, Theme $theme)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:themes,code,' . $theme->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $theme->update($validated);

        return redirect()
            ->route('themes.show', $theme)
            ->with('success', 'Theme updated successfully!');
    }

    /**
     * Remove the specified theme
     */
    public function destroy(Theme $theme)
    {
        if (!$theme->canBeDeleted()) {
            return back()
                ->with('error', 'Cannot delete theme with associated projects or indicators.');
        }

        $theme->delete();

        return redirect()
            ->route('themes.index')
            ->with('success', 'Theme deleted successfully!');
    }

    /**
     * Toggle theme active status
     */
    public function toggleStatus(Theme $theme)
    {
        $theme->is_active = !$theme->is_active;
        $theme->save();

        $status = $theme->is_active ? 'activated' : 'deactivated';

        return back()
            ->with('success', "Theme {$status} successfully!");
    }

    /**
     * Get themes for API (for dropdowns, etc.)
     */
    public function apiIndex(Request $request)
    {
        $query = Theme::active()->ordered();

        if ($request->has('with_stats')) {
            $themes = $query->withCount(['projects', 'indicators'])->get();
        } else {
            $themes = $query->get();
        }

        return response()->json($themes);
    }

    /**
     * Get theme details (API)
     */
    public function showApi(Theme $theme)
    {
        return response()->json([
            'theme' => $theme->getSummary(),
        ]);
    }
}
