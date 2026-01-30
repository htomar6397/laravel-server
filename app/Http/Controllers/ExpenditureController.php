<?php

namespace App\Http\Controllers;

use App\Models\{Expenditure, FileAttachment, Notification, Project, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * ExpenditureController
 * 
 * Handles expenditure management operations for the KMC M&E System
 */
class ExpenditureController extends Controller
{
    /**
     * Display a listing of expenditures
     */
    public function index(Request $request)
    {
        $query = Expenditure::with(['project', 'enterer', 'approver', 'verifier']);

        // Apply filters
        if ($request->has('project') && $request->project != '') {
            $query->where('project_id', $request->project);
        }

        if ($request->has('category') && $request->category != '') {
            $query->byCategory($request->category);
        }

        if ($request->has('status') && $request->status != '') {
            $query->byStatus($request->status);
        }

        if ($request->has('date_from') && $request->date_from != '') {
            $query->where('expenditure_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to != '') {
            $query->where('expenditure_date', '<=', $request->date_to);
        }

        if ($request->has('search') && $request->search != '') {
            $query->search($request->search);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $expenditures = $query->paginate(20);

        // Get filter options
        $projects = Project::orderBy('name')->get();
        $categories = Expenditure::categories();
        $statuses = Expenditure::statuses();

        // Calculate statistics
        $stats = [
            'total' => Expenditure::count(),
            'pending' => Expenditure::pendingApproval()->count(),
            'approved' => Expenditure::approved()->count(),
            'verified' => Expenditure::verified()->count(),
            'total_amount' => Expenditure::sum('amount'),
            'pending_amount' => Expenditure::pendingApproval()->sum('amount'),
        ];

        return view('expenditures.index', compact(
            'expenditures',
            'projects',
            'categories',
            'statuses',
            'stats'
        ));
    }

    /**
     * Show the form for creating a new expenditure
     */
    public function create()
    {
        $projects = Project::orderBy('name')->get();
        $categories = Expenditure::categories();

        return view('expenditures.create', compact('projects', 'categories'));
    }

    /**
     * Store a newly created expenditure
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'expenditure_date' => 'required|date',
            'category' => 'required|in:' . implode(',', Expenditure::categories()),
            'subcategory' => 'nullable|string|max:100',
            'vendor' => 'nullable|string|max:255',
            'invoice_number' => 'nullable|string|max:100',
            'receipt_number' => 'nullable|string|max:100',
        ]);

        $validated['entered_by'] = Auth::id();

        $expenditure = Expenditure::create($validated);

        // Handle file attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                FileAttachment::uploadFile(
                    $file,
                    'Expenditure',
                    $expenditure->id,
                    null,
                    Auth::id()
                );
            }
        }

        // Notify relevant users
        $this->notifyRelevantUsers($expenditure, 'created');

        return redirect()
            ->route('expenditures.show', $expenditure)
            ->with('success', 'Expenditure created successfully!');
    }

    /**
     * Display the specified expenditure
     */
    public function show(Expenditure $expenditure)
    {
        $expenditure->load([
            'project',
            'enterer',
            'approver',
            'verifier',
            'fileAttachments.uploader'
        ]);

        return view('expenditures.show', compact('expenditure'));
    }

    /**
     * Show the form for editing the specified expenditure
     */
    public function edit(Expenditure $expenditure)
    {
        if (!$expenditure->can_be_edited) {
            abort(403, 'This expenditure cannot be edited.');
        }

        $projects = Project::orderBy('name')->get();
        $categories = Expenditure::categories();

        return view('expenditures.edit', compact('expenditure', 'projects', 'categories'));
    }

    /**
     * Update the specified expenditure
     */
    public function update(Request $request, Expenditure $expenditure)
    {
        if (!$expenditure->can_be_edited) {
            abort(403, 'This expenditure cannot be edited.');
        }

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'expenditure_date' => 'required|date',
            'category' => 'required|in:' . implode(',', Expenditure::categories()),
            'subcategory' => 'nullable|string|max:100',
            'vendor' => 'nullable|string|max:255',
            'invoice_number' => 'nullable|string|max:100',
            'receipt_number' => 'nullable|string|max:100',
        ]);

        $expenditure->update($validated);

        // Handle file attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                FileAttachment::uploadFile(
                    $file,
                    'Expenditure',
                    $expenditure->id,
                    null,
                    Auth::id()
                );
            }
        }

        return redirect()
            ->route('expenditures.show', $expenditure)
            ->with('success', 'Expenditure updated successfully!');
    }

    /**
     * Remove the specified expenditure
     */
    public function destroy(Expenditure $expenditure)
    {
        if (!$expenditure->can_be_edited) {
            abort(403, 'This expenditure cannot be deleted.');
        }

        $expenditure->delete();

        return redirect()
            ->route('expenditures.index')
            ->with('success', 'Expenditure deleted successfully!');
    }

    /**
     * Approve expenditure
     */
    public function approve(Request $request, Expenditure $expenditure)
    {
        if (!$expenditure->canUserPerformAction(Auth::user(), 'approve')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $expenditure->approve(Auth::id(), $validated['notes']);

        // Notify relevant users
        $this->notifyRelevantUsers($expenditure, 'approved');

        return back()
            ->with('success', 'Expenditure approved successfully!');
    }

    /**
     * Reject expenditure
     */
    public function reject(Request $request, Expenditure $expenditure)
    {
        if (!$expenditure->canUserPerformAction(Auth::user(), 'approve')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'notes' => 'required|string|max:1000',
        ]);

        $expenditure->reject(Auth::id(), $validated['notes']);

        // Notify relevant users
        $this->notifyRelevantUsers($expenditure, 'rejected');

        return back()
            ->with('success', 'Expenditure rejected successfully!');
    }

    /**
     * Verify expenditure
     */
    public function verify(Request $request, Expenditure $expenditure)
    {
        if (!$expenditure->canUserPerformAction(Auth::user(), 'verify')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $expenditure->verify(Auth::id(), $validated['notes']);

        // Notify relevant users
        $this->notifyRelevantUsers($expenditure, 'verified');

        return back()
            ->with('success', 'Expenditure verified successfully!');
    }

    /**
     * Reset expenditure to pending status
     */
    public function reset(Expenditure $expenditure)
    {
        if (!Auth::user()->hasPermission('manage_expenditures')) {
            abort(403, 'Unauthorized action.');
        }

        $expenditure->resetToPending();

        return back()
            ->with('success', 'Expenditure reset to pending status!');
    }

    /**
     * Get expenditure statistics (API)
     */
    public function statistics(Request $request)
    {
        $query = Expenditure::query();

        // Apply date range filter
        if ($request->has('start_date') && $request->start_date) {
            $query->where('expenditure_date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->where('expenditure_date', '<=', $request->end_date);
        }

        $stats = [
            'total_expenditures' => $query->count(),
            'total_amount' => $query->sum('amount'),
            'by_status' => [
                'pending' => $query->clone()->byStatus('PENDING')->count(),
                'approved' => $query->clone()->byStatus('APPROVED')->count(),
                'rejected' => $query->clone()->byStatus('REJECTED')->count(),
                'verified' => $query->clone()->byStatus('VERIFIED')->count(),
            ],
            'by_category' => Expenditure::categories()->mapWithKeys(function ($category) use ($query) {
                return [$category => [
                    'count' => $query->clone()->byCategory($category)->count(),
                    'amount' => $query->clone()->byCategory($category)->sum('amount'),
                ]];
            }),
            'monthly_trend' => $query->selectRaw('DATE_FORMAT(expenditure_date, "%Y-%m") as month, SUM(amount) as total, COUNT(*) as count')
                ->groupBy('month')
                ->orderBy('month')
                ->get(),
            'pending_approval' => $query->clone()->pendingApproval()->count(),
            'pending_amount' => $query->clone()->pendingApproval()->sum('amount'),
        ];

        return response()->json($stats);
    }

    /**
     * Search expenditures (API)
     */
    public function search(Request $request)
    {
        $search = $request->get('q');
        
        if (!$search) {
            return response()->json([]);
        }

        $expenditures = Expenditure::search($search)
            ->with(['project', 'enterer'])
            ->limit(10)
            ->get()
            ->map(function ($expenditure) {
                return [
                    'id' => $expenditure->id,
                    'code' => $expenditure->code,
                    'description' => $expenditure->description,
                    'amount' => $expenditure->getFormattedAmount(),
                    'project' => $expenditure->project?->name,
                    'status' => $expenditure->status,
                    'status_label' => $expenditure->status_label,
                    'expenditure_date' => $expenditure->expenditure_date->format('Y-m-d'),
                ];
            });

        return response()->json($expenditures);
    }

    /**
     * Get expenditure details (API)
     */
    public function showApi(Expenditure $expenditure)
    {
        $expenditure->load(['project', 'enterer', 'approver', 'verifier', 'fileAttachments']);
        
        return response()->json([
            'expenditure' => $expenditure->getSummary(),
        ]);
    }

    /**
     * Notify relevant users about expenditure actions
     */
    private function notifyRelevantUsers(Expenditure $expenditure, string $action)
    {
        $users = collect();

        // Add project creator
        if ($expenditure->project && $expenditure->project->creator) {
            $users->push($expenditure->project->creator);
        }

        // Add users who can approve expenditures
        $approvers = User::whereHas('roles', function ($query) {
            $query->whereJsonContains('permissions', 'approve_expenditures');
        })->get();

        $users = $users->merge($approvers);

        // Remove the current user from notifications
        $users = $users->where('id', '!=', Auth::id())->unique('id');

        foreach ($users as $user) {
            $title = "Expenditure {$action}";
            $message = "Expenditure '{$expenditure->description}' has been {$action}.";
            $actionUrl = route('expenditures.show', $expenditure);

            Notification::createExpenditureNotification(
                $user->id,
                $expenditure,
                $action,
                $action === 'approved' ? Notification::TYPE_SUCCESS : Notification::TYPE_INFO
            );
        }
    }
}
