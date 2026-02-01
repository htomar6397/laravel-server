<?php

namespace App\Http\Controllers;

use App\Models\{DataEntry, Indicator, Notification, Project, User};
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * DataEntryController
 * 
 * Handles data entry management operations for the KMC M&E System
 */
class DataEntryController extends Controller
{
    use ApiResponseTrait;
    /**
     * Display a listing of data entries
     */
    public function index(Request $request)
    {
        $query = DataEntry::with(['project', 'indicator', 'enterer', 'verifier']);

        // Apply filters
        if ($request->has('project') && $request->project != '') {
            $query->byProject($request->project);
        }

        if ($request->has('indicator') && $request->indicator != '') {
            $query->byIndicator($request->indicator);
        }

        if ($request->has('frequency') && $request->frequency != '') {
            $query->byFrequency($request->frequency);
        }

        if ($request->has('verification_status') && $request->verification_status != '') {
            $query->byVerificationStatus($request->verification_status);
        }

        if ($request->has('date_from') && $request->date_from != '') {
            $query->where('data_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to != '') {
            $query->where('data_date', '<=', $request->date_to);
        }

        if ($request->has('search') && $request->search != '') {
            $query->search($request->search);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'data_date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $dataEntries = $query->paginate(20);

        // Get filter options
        $projects = Project::orderBy('name')->get();
        $indicators = Indicator::active()->ordered()->get();
        $frequencies = DataEntry::frequencies();
        $verificationStatuses = DataEntry::verificationStatuses();

        // Calculate statistics
        $stats = [
            'total' => DataEntry::count(),
            'pending' => DataEntry::pendingVerification()->count(),
            'verified' => DataEntry::verified()->count(),
            'rejected' => DataEntry::byVerificationStatus('REJECTED')->count(),
        ];

        return view('data-entries.index', compact(
            'dataEntries',
            'projects',
            'indicators',
            'frequencies',
            'verificationStatuses',
            'stats'
        ));
    }

    /**
     * Get data entries (API)
     */
    public function apiIndex(Request $request)
    {
        $query = DataEntry::with(['project', 'indicator', 'enteredBy', 'verifiedBy']);

        // Apply filters
        if ($request->has('project_id') && $request->project_id != '') {
            $query->byProject($request->project_id);
        }

        if ($request->has('indicator_id') && $request->indicator_id != '') {
            $query->byIndicator($request->indicator_id);
        }

        if ($request->has('frequency') && $request->frequency != '') {
            $query->byFrequency($request->frequency);
        }

        if ($request->has('verification_status') && $request->verification_status != '') {
            $query->byVerificationStatus($request->verification_status);
        }

        if ($request->has('date_from') && $request->date_from != '') {
            $query->where('data_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to != '') {
            $query->where('data_date', '<=', $request->date_to);
        }

        if ($request->has('search') && $request->search != '') {
            $query->search($request->search);
        }

        $dataEntries = $query->latest()->paginate($request->per_page ?? 15);

        return $this->paginatedResponse($dataEntries, 'Data entries retrieved successfully');
    }

    /**
     * Show the form for creating a new data entry
     */
    public function create()
    {
        $projects = Project::orderBy('name')->get();
        $indicators = Indicator::active()->ordered()->get();
        $frequencies = DataEntry::frequencies();

        return view('data-entries.create', compact(
            'projects',
            'indicators',
            'frequencies'
        ));
    }

    /**
     * Store a newly created data entry
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'indicator_id' => 'required|exists:indicators,id',
            'value' => 'required|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'data_date' => 'required|date',
            'frequency' => 'required|in:' . implode(',', DataEntry::frequencies()),
            'notes' => 'nullable|string|max:1000',
            'data_source' => 'nullable|string|max:255',
        ]);

        $validated['entered_by'] = Auth::id();

        $dataEntry = DataEntry::create($validated);

        // Notify relevant users
        $this->notifyRelevantUsers($dataEntry, 'created');

        // Load relationships for response
        $dataEntry->load([
            'project',
            'indicator',
            'enteredBy',
            'verifiedBy',
        ]);

        return $this->successResponse(
            $dataEntry,
            'Data entry created successfully!',
            201
        );
    }

    /**
     * Display the specified data entry
     */
    public function show(DataEntry $dataEntry)
    {
        $dataEntry->load([
            'project',
            'indicator',
            'enteredBy',
            'verifiedBy',
        ]);

        return $this->successResponse($dataEntry);
    }

    /**
     * Show the form for editing the specified data entry
     */
    public function edit(DataEntry $dataEntry)
    {
        if (!$dataEntry->canUserPerformAction(Auth::user(), 'edit')) {
            abort(403, 'Unauthorized action.');
        }

        $projects = Project::orderBy('name')->get();
        $indicators = Indicator::active()->ordered()->get();
        $frequencies = DataEntry::frequencies();

        return view('data-entries.edit', compact(
            'dataEntry',
            'projects',
            'indicators',
            'frequencies'
        ));
    }

    /**
     * Update the specified data entry
     */
    public function update(Request $request, DataEntry $dataEntry)
    {
        if (!$dataEntry->canUserPerformAction(Auth::user(), 'edit')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'indicator_id' => 'required|exists:indicators,id',
            'value' => 'required|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'data_date' => 'required|date',
            'frequency' => 'required|in:' . implode(',', DataEntry::frequencies()),
            'notes' => 'nullable|string|max:1000',
            'data_source' => 'nullable|string|max:255',
        ]);

        $dataEntry->update($validated);

        // Load relationships for response
        $dataEntry->load([
            'project',
            'indicator',
            'enteredBy',
            'verifiedBy',
        ]);

        return $this->successResponse(
            $dataEntry,
            'Data entry updated successfully!'
        );
    }

    /**
     * Remove the specified data entry
     */
    public function destroy(DataEntry $dataEntry)
    {
        if (!$dataEntry->canUserPerformAction(Auth::user(), 'delete')) {
            abort(403, 'Unauthorized action.');
        }

        $dataEntry->delete();

        return $this->successResponse(
            null,
            'Data entry deleted successfully!'
        );
    }

    /**
     * Verify data entry
     */
    public function verify(Request $request, DataEntry $dataEntry)
    {
        if (!$dataEntry->canUserPerformAction(Auth::user(), 'verify')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $dataEntry->verify(Auth::id(), $validated['notes']);

        // Notify relevant users
        $this->notifyRelevantUsers($dataEntry, 'verified');

        return back()
            ->with('success', 'Data entry verified successfully!');
    }

    /**
     * Reject data entry
     */
    public function reject(Request $request, DataEntry $dataEntry)
    {
        if (!$dataEntry->canUserPerformAction(Auth::user(), 'verify')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'notes' => 'required|string|max:1000',
        ]);

        $dataEntry->reject(Auth::id(), $validated['notes']);

        // Notify relevant users
        $this->notifyRelevantUsers($dataEntry, 'rejected');

        return back()
            ->with('success', 'Data entry rejected successfully!');
    }

    /**
     * Reset data entry to pending status
     */
    public function reset(DataEntry $dataEntry)
    {
        if (!Auth::user()->hasPermission('verify_data')) {
            abort(403, 'Unauthorized action.');
        }

        $dataEntry->resetToPending();

        return back()
            ->with('success', 'Data entry reset to pending status!');
    }

    /**
     * Get data entries for project
     */
    public function byProject(Project $project, Request $request)
    {
        $query = $project->dataEntries()->with(['indicator', 'enterer', 'verifier']);

        // Apply filters
        if ($request->has('indicator') && $request->indicator != '') {
            $query->byIndicator($request->indicator);
        }

        if ($request->has('verification_status') && $request->verification_status != '') {
            $query->byVerificationStatus($request->verification_status);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'data_date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $dataEntries = $query->paginate(20);

        $indicators = $project->indicators;
        $verificationStatuses = DataEntry::verificationStatuses();

        return view('data-entries.by-project', compact(
            'project',
            'dataEntries',
            'indicators',
            'verificationStatuses'
        ));
    }

    /**
     * Get data entries for indicator
     */
    public function byIndicator(Indicator $indicator, Request $request)
    {
        $query = $indicator->dataEntries()->with(['project', 'enterer', 'verifier']);

        // Apply filters
        if ($request->has('project') && $request->project != '') {
            $query->byProject($request->project);
        }

        if ($request->has('verification_status') && $request->verification_status != '') {
            $query->byVerificationStatus($request->verification_status);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'data_date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $dataEntries = $query->paginate(20);

        $projects = Project::has('dataEntries')->get();
        $verificationStatuses = DataEntry::verificationStatuses();

        return view('data-entries.by-indicator', compact(
            'indicator',
            'dataEntries',
            'projects',
            'verificationStatuses'
        ));
    }

    /**
     * Get data entry statistics (API)
     */
    public function statistics(Request $request)
    {
        $query = DataEntry::query();

        // Apply date range filter
        if ($request->has('start_date') && $request->start_date) {
            $query->where('data_date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->where('data_date', '<=', $request->end_date);
        }

        $stats = [
            'total_entries' => $query->count(),
            'by_status' => [
                'pending' => $query->clone()->pendingVerification()->count(),
                'verified' => $query->clone()->verified()->count(),
                'rejected' => $query->clone()->byVerificationStatus('REJECTED')->count(),
            ],
            'by_frequency' => DataEntry::frequencies()->mapWithKeys(function ($frequency) use ($query) {
                return [$frequency => $query->clone()->byFrequency($frequency)->count()];
            }),
            'monthly_trend' => $query->selectRaw('DATE_FORMAT(data_date, "%Y-%m") as month, COUNT(*) as count')
                ->groupBy('month')
                ->orderBy('month')
                ->get(),
            'pending_verification' => $query->clone()->pendingVerification()->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Search data entries (API)
     */
    public function search(Request $request)
    {
        $search = $request->get('q');
        
        if (!$search) {
            return response()->json([]);
        }

        $dataEntries = DataEntry::search($search)
            ->with(['project', 'indicator', 'enterer'])
            ->limit(10)
            ->get()
            ->map(function ($entry) {
                return [
                    'id' => $entry->id,
                    'value' => $entry->formatted_value,
                    'project' => $entry->project?->name,
                    'indicator' => $entry->indicator?->name,
                    'status' => $entry->verification_status,
                    'status_label' => $entry->verification_status_label,
                    'data_date' => $entry->data_date->format('Y-m-d'),
                ];
            });

        return response()->json($dataEntries);
    }

    /**
     * Get data entry details (API)
     */
    public function showApi(DataEntry $dataEntry)
    {
        $dataEntry->load([
            'project',
            'indicator',
            'enteredBy',
            'verifiedBy',
        ]);

        return $this->successResponse($dataEntry);
    }

    /**
     * Get trend data for indicator (API)
     */
    public function trendData(Indicator $indicator, Request $request)
    {
        $startDate = $request->get('start_date', now()->subMonths(12)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $trendData = DataEntry::getTrendData(
            $request->get('project_id'),
            $indicator->id,
            $startDate,
            $endDate
        );

        return response()->json([
            'indicator' => $indicator->getSummary(),
            'trend_data' => $trendData,
        ]);
    }

    /**
     * Bulk verify data entries
     */
    public function bulkVerify(Request $request)
    {
        if (!Auth::user()->hasPermission('verify_data')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'entry_ids' => 'required|array',
            'entry_ids.*' => 'exists:data_entries,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $verifiedCount = 0;
        foreach ($validated['entry_ids'] as $entryId) {
            $dataEntry = DataEntry::findOrFail($entryId);
            if ($dataEntry->canUserPerformAction(Auth::user(), 'verify')) {
                $dataEntry->verify(Auth::id(), $validated['notes']);
                $verifiedCount++;
            }
        }

        return back()
            ->with('success', "Successfully verified {$verifiedCount} data entries!");
    }

    /**
     * Notify relevant users about data entry actions
     */
    private function notifyRelevantUsers(DataEntry $dataEntry, string $action)
    {
        $users = collect();

        // Add project creator
        if ($dataEntry->project && $dataEntry->project->creator) {
            $users->push($dataEntry->project->creator);
        }

        // Add users who can verify data
        $verifiers = User::whereHas('roles', function ($query) {
            $query->whereJsonContains('permissions', 'verify_data');
        })->get();

        $users = $users->merge($verifiers);

        // Remove the current user from notifications
        $users = $users->where('id', '!=', Auth::id())->unique('id');

        foreach ($users as $user) {
            $title = "Data Entry {$action}";
            $message = "Data entry for '{$dataEntry->indicator->name}' has been {$action}.";
            $actionUrl = route('data-entries.show', $dataEntry);

            Notification::create([
                'user_id' => $user->id,
                'title' => $title,
                'message' => $message,
                'type' => $action === 'verified' ? Notification::TYPE_SUCCESS : Notification::TYPE_INFO,
                'category' => 'data_entry',
                'action_url' => $actionUrl,
                'metadata' => [
                    'data_entry_id' => $dataEntry->id,
                    'project_id' => $dataEntry->project_id,
                    'indicator_id' => $dataEntry->indicator_id,
                ],
            ]);
        }
    }
}
