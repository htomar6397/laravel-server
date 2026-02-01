<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expenditure;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

/**
 * Admin Expenditure Approval Controller
 */
class AdminExpenditureController extends Controller
{
    /**
     * Display a listing of expenditures
     */
    public function index(Request $request)
    {
        $query = Expenditure::with(['project', 'enteredBy', 'approvedBy', 'verifiedBy']);

        // Search
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('description', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%")
                  ->orWhere('vendor', 'like', "%{$request->search}%");
            });
        }

        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->category) {
            $query->where('category', $request->category);
        }

        // Filter by project
        if ($request->project_id) {
            $query->where('project_id', $request->project_id);
        }

        // Filter by date range
        if ($request->date_from) {
            $query->whereDate('expenditure_date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('expenditure_date', '<=', $request->date_to);
        }

        // Sort
        $sortField = $request->sort_field ?? 'created_at';
        $sortDirection = $request->sort_direction ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        $expenditures = $query->paginate($request->per_page ?? 15);

        return Inertia::render('Admin/Expenditures/Index', [
            'expenditures' => $expenditures,
            'filters' => $request->only(['search', 'status', 'category', 'project_id', 'date_from', 'date_to']),
            'projects' => Project::all(['id', 'name', 'code']),
            'statuses' => Expenditure::statuses(),
            'categories' => Expenditure::categories(),
        ]);
    }

    /**
     * Display the specified expenditure
     */
    public function show(Expenditure $expenditure)
    {
        $expenditure->load([
            'project',
            'enteredBy',
            'approvedBy',
            'verifiedBy',
            'fileAttachments',
        ]);

        return Inertia::render('Admin/Expenditures/Show', [
            'expenditure' => $expenditure,
        ]);
    }

    /**
     * Approve an expenditure
     */
    public function approve(Request $request, Expenditure $expenditure)
    {
        $validated = $request->validate([
            'approval_notes' => 'nullable|string',
        ]);

        $expenditure->update([
            'status' => Expenditure::STATUS_APPROVED,
            'approved_by' => auth()->id(),
            'approved_at' => Carbon::now(),
            'approval_notes' => $validated['approval_notes'] ?? null,
        ]);

        return back()->with('success', 'Expenditure approved successfully');
    }

    /**
     * Reject an expenditure
     */
    public function reject(Request $request, Expenditure $expenditure)
    {
        $validated = $request->validate([
            'approval_notes' => 'required|string',
        ]);

        $expenditure->update([
            'status' => Expenditure::STATUS_REJECTED,
            'approved_by' => auth()->id(),
            'approved_at' => Carbon::now(),
            'approval_notes' => $validated['approval_notes'],
        ]);

        return back()->with('success', 'Expenditure rejected');
    }

    /**
     * Verify an expenditure (second level approval)
     */
    public function verify(Request $request, Expenditure $expenditure)
    {
        if ($expenditure->status !== Expenditure::STATUS_APPROVED) {
            return back()->with('error', 'Only approved expenditures can be verified');
        }

        $validated = $request->validate([
            'verification_notes' => 'nullable|string',
        ]);

        $expenditure->update([
            'status' => Expenditure::STATUS_VERIFIED,
            'verified_by' => auth()->id(),
            'verified_at' => Carbon::now(),
            'verification_notes' => $validated['verification_notes'] ?? null,
        ]);

        return back()->with('success', 'Expenditure verified successfully');
    }

    /**
     * Bulk approve expenditures
     */
    public function bulkApprove(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:expenditures,id',
        ]);

        Expenditure::whereIn('id', $validated['ids'])
            ->where('status', Expenditure::STATUS_PENDING)
            ->update([
                'status' => Expenditure::STATUS_APPROVED,
                'approved_by' => auth()->id(),
                'approved_at' => Carbon::now(),
            ]);

        return back()->with('success', count($validated['ids']) . ' expenditures approved');
    }

    /**
     * Export expenditures to CSV
     */
    public function export(Request $request)
    {
        $query = Expenditure::with(['project', 'enteredBy', 'approvedBy']);

        // Apply filters
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->category) {
            $query->where('category', $request->category);
        }
        if ($request->project_id) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->date_from) {
            $query->whereDate('expenditure_date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('expenditure_date', '<=', $request->date_to);
        }

        $expenditures = $query->get();

        $filename = 'expenditures_' . Carbon::now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($expenditures) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'Code', 'Project', 'Description', 'Amount', 'Currency', 'Date',
                'Category', 'Vendor', 'Invoice #', 'Status', 'Entered By', 'Created At'
            ]);

            // Data
            foreach ($expenditures as $exp) {
                fputcsv($file, [
                    $exp->code,
                    $exp->project->name ?? '',
                    $exp->description,
                    $exp->amount,
                    $exp->currency,
                    $exp->expenditure_date->format('Y-m-d'),
                    $exp->category,
                    $exp->vendor ?? '',
                    $exp->invoice_number ?? '',
                    $exp->status,
                    $exp->enteredBy->full_name ?? '',
                    $exp->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
