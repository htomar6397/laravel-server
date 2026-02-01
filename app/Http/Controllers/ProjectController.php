<?php

namespace App\Http\Controllers;

use App\Models\{Project, Theme, OrganizationalUnit};
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * ProjectController
 * 
 * Handles all project management operations
 */
class ProjectController extends Controller
{
    use ApiResponseTrait;
    /**
     * Display a listing of projects
     */
    public function index(Request $request)
    {
        $query = Project::with(['theme', 'organizationalUnit', 'creator']);

        // Apply filters
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('theme') && $request->theme != '') {
            $query->where('theme_id', $request->theme);
        }

        if ($request->has('org_unit') && $request->org_unit != '') {
            $query->where('org_unit_id', $request->org_unit);
        }

        if ($request->has('search') && $request->search != '') {
            $query->search($request->search);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $projects = $query->paginate(20);

        // Get filter options
        $themes = Theme::where('is_active', true)->get();
        $orgUnits = OrganizationalUnit::where('is_active', true)->get();
        $statuses = Project::statuses();

        // Calculate statistics
        $stats = [
            'total' => Project::count(),
            'active' => Project::active()->count(),
            'completed' => Project::completed()->count(),
            'at_risk' => Project::atRisk()->count(),
        ];

        return view('projects.index', compact(
            'projects',
            'themes',
            'orgUnits',
            'statuses',
            'stats'
        ));
    }

    /**
     * Show the form for creating a new project
     */
    public function create()
    {
        $themes = Theme::where('is_active', true)->get();
        $orgUnits = OrganizationalUnit::where('is_active', true)->get();
        $statuses = Project::statuses();

        return view('projects.create', compact('themes', 'orgUnits', 'statuses'));
    }

    /**
     * Store a newly created project (Web & API)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'nullable|string|max:50|unique:projects',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'theme_id' => 'nullable|exists:themes,id',
            'org_unit_id' => 'nullable|exists:organizational_units,id',
            'sector' => 'nullable|string|max:100',
            'donor' => 'nullable|string|max:100',
            'budget' => 'nullable|numeric|min:0',
            'currency' => 'required|string|max:3',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => 'required|in:' . implode(',', Project::statuses()),
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'location_description' => 'nullable|string|max:255',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['completion_percentage'] = 0;

        $project = Project::create($validated);

        // Return JSON for API requests
        if ($request->expectsJson()) {
            return $this->successResponse(
                $project->load(['theme', 'organizationalUnit', 'creator']),
                'Project created successfully',
                201
            );
        }

        // Return redirect for web requests
        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project created successfully!');
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
            'projectIndicators.indicator',
            'expenditures' => function ($query) {
                $query->latest()->take(10);
            },
            'photoCaptures' => function ($query) {
                $query->latest()->take(6);
            }
        ]);

        // Calculate statistics
        $stats = [
            'total_indicators' => $project->projectIndicators()->count(),
            'total_expenditure' => $project->total_expenditures,
            'budget_utilization' => $project->budget_utilization,
            'completion' => $project->completion_percentage,
            'average_achievement' => $project->average_achievement,
            'health_status' => $project->health_status,
            'days_remaining' => $project->days_remaining,
        ];

        return view('projects.show', compact('project', 'stats'));
    }

    /**
     * Show the form for editing the specified project
     */
    public function edit(Project $project)
    {
        $themes = Theme::where('is_active', true)->get();
        $orgUnits = OrganizationalUnit::where('is_active', true)->get();
        $statuses = Project::statuses();

        return view('projects.edit', compact('project', 'themes', 'orgUnits', 'statuses'));
    }

    /**
     * Update the specified project (Web & API)
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'code' => 'sometimes|string|max:50|unique:projects,code,' . $project->id,
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'theme_id' => 'nullable|exists:themes,id',
            'org_unit_id' => 'nullable|exists:organizational_units,id',
            'sector' => 'nullable|string|max:100',
            'donor' => 'nullable|string|max:100',
            'budget' => 'nullable|numeric|min:0',
            'currency' => 'sometimes|string|max:3',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => 'sometimes|in:' . implode(',', Project::statuses()),
            'completion_percentage' => 'nullable|numeric|between:0,100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'location_description' => 'nullable|string|max:255',
        ]);

        $project->update($validated);

        // Return JSON for API requests
        if ($request->expectsJson()) {
            return $this->successResponse(
                $project->load(['theme', 'organizationalUnit', 'creator']),
                'Project updated successfully'
            );
        }

        // Return redirect for web requests
        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project updated successfully!');
    }

    /**
     * Remove the specified project (Web & API)
     */
    public function destroy(Project $project)
    {
        $project->delete();

        // Return JSON for API requests
        if (request()->expectsJson()) {
            return $this->successResponse(
                null,
                'Project deleted successfully'
            );
        }

        // Return redirect for web requests
        return redirect()
            ->route('projects.index')
            ->with('success', 'Project deleted successfully!');
    }

    /**
     * Show project indicators page
     */
    public function indicators(Project $project)
    {
        $project->load(['projectIndicators.indicator']);

        return view('projects.indicators', compact('project'));
    }

    /**
     * Update project status
     */
    public function updateStatus(Request $request, Project $project)
    {
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', Project::statuses()),
        ]);

        $project->update($validated);

        if ($validated['status'] === Project::STATUS_COMPLETED) {
            $project->markAsCompleted();
        }

        return back()->with('success', 'Project status updated successfully!');
    }

    /**
     * API: Display a listing of projects
     * 
     * @OA\Get(
     *     path="/api/v1/projects",
     *     summary="Get list of projects",
     *     tags={"Projects"},
     *     security={{"BearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by project status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"PLANNING","ACTIVE","COMPLETED","SUSPENDED","CANCELLED"})
     *     ),
     *     @OA\Parameter(
     *         name="theme",
     *         in="query",
     *         description="Filter by theme ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search projects by name or description",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=20)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Projects retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/PaginatedResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function apiIndex(Request $request)
    {
        $query = Project::with(['theme', 'organizationalUnit', 'creator']);

        // Apply filters
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('theme') && $request->theme != '') {
            $query->where('theme_id', $request->theme);
        }

        if ($request->has('org_unit') && $request->org_unit != '') {
            $query->where('org_unit_id', $request->org_unit);
        }

        if ($request->has('search') && $request->search != '') {
            $query->search($request->search);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $projects = $query->paginate($request->get('per_page', 20));

        return $this->paginatedResponse($projects, 'Projects retrieved successfully');
    }

    /**
     * API: Display the specified project
     * 
     * @OA\Get(
     *     path="/api/v1/projects/{id}",
     *     summary="Get project details",
     *     tags={"Projects"},
     *     security={{"BearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Project ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Project retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", ref="#/components/schemas/Project")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Project not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function showApi(Project $project)
    {
        $project->load(['theme', 'organizationalUnit', 'creator', 'indicators', 'expenditures']);

        return $this->successResponse($project, 'Project retrieved successfully');
    }
}
