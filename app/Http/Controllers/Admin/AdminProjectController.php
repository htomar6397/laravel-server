<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Theme;
use App\Models\OrganizationalUnit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

/**
 * Admin Project Management Controller
 */
class AdminProjectController extends Controller
{
    /**
     * Display a listing of projects
     */
    public function index(Request $request)
    {
        $query = Project::with(['theme', 'organizationalUnit', 'creator'])
            ->withCount(['dataEntries', 'expenditures', 'photoCaptures']);

        // Search
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Filter by theme
        if ($request->theme_id) {
            $query->where('theme_id', $request->theme_id);
        }

        // Sort
        $sortField = $request->sort_field ?? 'created_at';
        $sortDirection = $request->sort_direction ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        $projects = $query->paginate($request->per_page ?? 15);

        return Inertia::render('Admin/Projects/Index', [
            'projects' => $projects,
            'filters' => $request->only(['search', 'status', 'theme_id', 'sort_field', 'sort_direction']),
            'themes' => Theme::all(),
            'statuses' => Project::statuses(),
        ]);
    }

    /**
     * Show the form for creating a new project
     */
    public function create()
    {
        return Inertia::render('Admin/Projects/Create', [
            'themes' => Theme::all(),
            'organizationalUnits' => OrganizationalUnit::all(),
            'users' => User::where('is_active', true)->get(),
        ]);
    }

    /**
     * Store a newly created project
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:projects',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'theme_id' => 'nullable|exists:themes,id',
            'org_unit_id' => 'nullable|exists:organizational_units,id',
            'sector' => 'nullable|string|max:100',
            'donor' => 'nullable|string|max:255',
            'budget' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => ['required', Rule::in(Project::statuses())],
            'completion_percentage' => 'nullable|numeric|min:0|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'location_description' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['currency'] = $validated['currency'] ?? 'TZS';

        $project = Project::create($validated);

        return redirect()->route('admin.projects.index')
            ->with('success', 'Project created successfully');
    }

    /**
     * Display the specified project
     */
    public function show(Project $project)
    {
        $project->load([
            'theme',
            'organizationalUnit',
            'creator',
            'dataEntries' => fn($q) => $q->with(['indicator', 'enteredBy'])->latest()->take(20),
            'expenditures' => fn($q) => $q->with('enteredBy')->latest()->take(20),
            'photoCaptures' => fn($q) => $q->with('capturedBy')->latest()->take(20),
        ]);

        // Calculate statistics
        $stats = [
            'total_data_entries' => $project->dataEntries()->count(),
            'pending_verifications' => $project->dataEntries()->where('verification_status', 'PENDING')->count(),
            'total_expenditures' => $project->expenditures()->count(),
            'total_spent' => $project->expenditures()->where('status', 'APPROVED')->sum('amount'),
            'pending_expenditures' => $project->expenditures()->where('status', 'PENDING')->sum('amount'),
            'total_photos' => $project->photoCaptures()->count(),
            'budget_utilized' => $project->budget > 0 
                ? ($project->expenditures()->where('status', 'APPROVED')->sum('amount') / $project->budget * 100) 
                : 0,
        ];

        return Inertia::render('Admin/Projects/Show', [
            'project' => $project,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for editing the specified project
     */
    public function edit(Project $project)
    {
        return Inertia::render('Admin/Projects/Edit', [
            'project' => $project,
            'themes' => Theme::all(),
            'organizationalUnits' => OrganizationalUnit::all(),
        ]);
    }

    /**
     * Update the specified project
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('projects')->ignore($project->id)],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'theme_id' => 'nullable|exists:themes,id',
            'org_unit_id' => 'nullable|exists:organizational_units,id',
            'sector' => 'nullable|string|max:100',
            'donor' => 'nullable|string|max:255',
            'budget' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => ['required', Rule::in(Project::statuses())],
            'completion_percentage' => 'nullable|numeric|min:0|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'location_description' => 'nullable|string',
        ]);

        $project->update($validated);

        return redirect()->route('admin.projects.index')
            ->with('success', 'Project updated successfully');
    }

    /**
     * Remove the specified project
     */
    public function destroy(Project $project)
    {
        $project->delete();

        return redirect()->route('admin.projects.index')
            ->with('success', 'Project deleted successfully');
    }
}
