<?php

namespace App\Http\Controllers;

use App\Models\{Project, Theme, OrganizationalUnit};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * ProjectController
 * 
 * Handles all project management operations
 */
class ProjectController extends Controller
{
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
     * Update the specified project
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:projects,code,' . $project->id,
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
            'completion_percentage' => 'nullable|numeric|between:0,100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'location_description' => 'nullable|string|max:255',
        ]);

        $project->update($validated);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project updated successfully!');
    }

    /**
     * Remove the specified project
     */
    public function destroy(Project $project)
    {
        $project->delete();

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

        return response()->json($projects);
    }

    /**
     * API: Store a newly created project
     * 
     * @OA\Post(
     *     path="/api/v1/projects",
     *     summary="Create a new project",
     *     tags={"Projects"},
     *     security={{"BearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","description","theme_id","org_unit_id","start_date","end_date","budget"},
     *             @OA\Property(property="name", type="string", maxLength=255, example="School Construction Project"),
     *             @OA\Property(property="description", type="string", example="Construction of new primary school in Kibaha"),
     *             @OA\Property(property="code", type="string", maxLength=50, example="KMC/EDU/2026/001"),
     *             @OA\Property(property="theme_id", type="integer", example=1),
     *             @OA\Property(property="org_unit_id", type="integer", example=1),
     *             @OA\Property(property="start_date", type="string", format="date", example="2026-01-01"),
     *             @OA\Property(property="end_date", type="string", format="date", example="2026-12-31"),
     *             @OA\Property(property="budget", type="number", format="float", example=50000000.00),
     *             @OA\Property(property="priority", type="string", enum={"LOW","MEDIUM","HIGH","CRITICAL"}, example="HIGH"),
     *             @OA\Property(property="location_description", type="string", maxLength=255, example="Kibaha Town Center")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Project created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Project created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Project")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'code' => 'nullable|string|max:50|unique:projects,code',
            'theme_id' => 'required|exists:themes,id',
            'org_unit_id' => 'required|exists:organizational_units,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'budget' => 'required|numeric|min:0',
            'priority' => 'required|in:' . implode(',', Project::priorities()),
            'location_description' => 'nullable|string|max:255',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['status'] = Project::STATUS_PLANNING;

        $project = Project::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Project created successfully',
            'data' => $project->load(['theme', 'organizationalUnit', 'creator'])
        ], 201);
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

        return response()->json([
            'status' => 'success',
            'data' => $project
        ]);
    }

    /**
     * API: Update the specified project
     * 
     * @OA\Put(
     *     path="/api/v1/projects/{id}",
     *     summary="Update project",
     *     tags={"Projects"},
     *     security={{"BearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Project ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", maxLength=255, example="Updated School Construction Project"),
     *             @OA\Property(property="description", type="string", example="Updated description"),
     *             @OA\Property(property="code", type="string", maxLength:50, example="KMC/EDU/2026/002"),
     *             @OA\Property(property="theme_id", type="integer", example=2),
     *             @OA\Property(property="org_unit_id", type="integer", example=2),
     *             @OA\Property(property="start_date", type="string", format="date", example="2026-02-01"),
     *             @OA\Property(property="end_date", type="string", format="date", example="2026-12-31"),
     *             @OA\Property(property="budget", type="number", format="float", example=60000000.00),
     *             @OA\Property(property="priority", type="string", enum={"LOW","MEDIUM","HIGH","CRITICAL"}, example="MEDIUM"),
     *             @OA\Property(property="status", type="string", enum={"PLANNING","ACTIVE","COMPLETED","SUSPENDED","CANCELLED"}, example="ACTIVE"),
     *             @OA\Property(property="location_description", type="string", maxLength=255, example="Updated location")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Project updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Project updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Project")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Project not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'code' => 'nullable|string|max:50|unique:projects,code,' . $project->id,
            'theme_id' => 'sometimes|required|exists:themes,id',
            'org_unit_id' => 'sometimes|required|exists:organizational_units,id',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after:start_date',
            'budget' => 'sometimes|required|numeric|min:0',
            'priority' => 'sometimes|required|in:' . implode(',', Project::priorities()),
            'status' => 'sometimes|required|in:' . implode(',', Project::statuses()),
            'location_description' => 'nullable|string|max:255',
        ]);

        $project->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Project updated successfully',
            'data' => $project->load(['theme', 'organizationalUnit', 'creator'])
        ]);
    }

    /**
     * API: Remove the specified project
     * 
     * @OA\Delete(
     *     path="/api/v1/projects/{id}",
     *     summary="Delete project",
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
     *         description="Project deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Project deleted successfully")
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
    public function destroy(Project $project)
    {
        $project->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Project deleted successfully'
        ]);
    }
}
