<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Admin Theme Management Controller
 */
class AdminThemeController extends Controller
{
    /**
     * Display a listing of themes
     */
    public function index(Request $request)
    {
        $query = Theme::withCount('projects');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Sort
        $sortField = $request->sort_field ?? 'sort_order';
        $sortDirection = $request->sort_direction ?? 'asc';
        $query->orderBy($sortField, $sortDirection);

        $themes = $query->paginate($request->per_page ?? 15)->withQueryString();

        return Inertia::render('Admin/Themes/Index', [
            'themes' => $themes,
            'filters' => $request->only(['search', 'status', 'sort_field', 'sort_direction']),
        ]);
    }

    /**
     * Show the form for creating a new theme
     */
    public function create()
    {
        return Inertia::render('Admin/Themes/Create');
    }

    /**
     * Store a newly created theme
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:themes,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required|string|max:7',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        Theme::create($validated);

        return redirect()->route('admin.themes.index')
            ->with('success', 'Theme created successfully.');
    }

    /**
     * Show the form for editing the specified theme
     */
    public function edit(Theme $theme)
    {
        return Inertia::render('Admin/Themes/Edit', [
            'theme' => $theme->load('projects:id,name'),
        ]);
    }

    /**
     * Update the specified theme
     */
    public function update(Request $request, Theme $theme)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:themes,code,' . $theme->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required|string|max:7',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $theme->update($validated);

        return redirect()->route('admin.themes.index')
            ->with('success', 'Theme updated successfully.');
    }

    /**
     * Remove the specified theme
     */
    public function destroy(Theme $theme)
    {
        // Check if theme has projects
        if ($theme->projects()->count() > 0) {
            return redirect()->route('admin.themes.index')
                ->with('error', 'Cannot delete theme with associated projects.');
        }

        $theme->delete();

        return redirect()->route('admin.themes.index')
            ->with('success', 'Theme deleted successfully.');
    }
}
