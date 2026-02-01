<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataEntry;
use App\Models\Project;
use App\Models\Indicator;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

/**
 * Admin Data Entry Review Controller
 */
class AdminDataEntryController extends Controller
{
    /**
     * Display a listing of data entries
     */
    public function index(Request $request)
    {
        $query = DataEntry::with(['project', 'indicator', 'enteredBy', 'verifiedBy']);

        // Search
        if ($request->search) {
            $query->whereHas('project', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%");
            });
        }

        // Filter by verification status
        if ($request->status) {
            $query->where('verification_status', $request->status);
        }

        // Filter by project
        if ($request->project_id) {
            $query->where('project_id', $request->project_id);
        }

        // Filter by date range
        if ($request->date_from) {
            $query->whereDate('data_date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('data_date', '<=', $request->date_to);
        }

        // Sort
        $sortField = $request->sort_field ?? 'created_at';
        $sortDirection = $request->sort_direction ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        $dataEntries = $query->paginate($request->per_page ?? 15);

        return Inertia::render('Admin/DataEntries/Index', [
            'dataEntries' => $dataEntries,
            'filters' => $request->only(['search', 'status', 'project_id', 'date_from', 'date_to']),
            'projects' => Project::all(['id', 'name', 'code']),
            'statuses' => DataEntry::verificationStatuses(),
        ]);
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
            'fileAttachments',
        ]);

        return Inertia::render('Admin/DataEntries/Show', [
            'dataEntry' => $dataEntry,
        ]);
    }

    /**
     * Verify a data entry
     */
    public function verify(Request $request, DataEntry $dataEntry)
    {
        $validated = $request->validate([
            'verification_notes' => 'nullable|string',
        ]);

        $dataEntry->update([
            'verification_status' => DataEntry::STATUS_VERIFIED,
            'verified_by' => auth()->id(),
            'verified_at' => Carbon::now(),
            'verification_notes' => $validated['verification_notes'] ?? null,
        ]);

        return back()->with('success', 'Data entry verified successfully');
    }

    /**
     * Reject a data entry
     */
    public function reject(Request $request, DataEntry $dataEntry)
    {
        $validated = $request->validate([
            'verification_notes' => 'required|string',
        ]);

        $dataEntry->update([
            'verification_status' => DataEntry::STATUS_REJECTED,
            'verified_by' => auth()->id(),
            'verified_at' => Carbon::now(),
            'verification_notes' => $validated['verification_notes'],
        ]);

        return back()->with('success', 'Data entry rejected');
    }

    /**
     * Bulk verify data entries
     */
    public function bulkVerify(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:data_entries,id',
        ]);

        DataEntry::whereIn('id', $validated['ids'])
            ->where('verification_status', DataEntry::STATUS_PENDING)
            ->update([
                'verification_status' => DataEntry::STATUS_VERIFIED,
                'verified_by' => auth()->id(),
                'verified_at' => Carbon::now(),
            ]);

        return back()->with('success', count($validated['ids']) . ' data entries verified');
    }

    /**
     * Export data entries to CSV
     */
    public function export(Request $request)
    {
        $query = DataEntry::with(['project', 'indicator', 'enteredBy']);

        // Apply same filters as index
        if ($request->status) {
            $query->where('verification_status', $request->status);
        }
        if ($request->project_id) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->date_from) {
            $query->whereDate('data_date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('data_date', '<=', $request->date_to);
        }

        $dataEntries = $query->get();

        $filename = 'data_entries_' . Carbon::now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($dataEntries) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'ID', 'Project Code', 'Project Name', 'Indicator', 'Value', 'Unit',
                'Data Date', 'Frequency', 'Status', 'Entered By', 'Created At'
            ]);

            // Data
            foreach ($dataEntries as $entry) {
                fputcsv($file, [
                    $entry->id,
                    $entry->project->code ?? '',
                    $entry->project->name ?? '',
                    $entry->indicator->name ?? '',
                    $entry->value,
                    $entry->unit,
                    $entry->data_date->format('Y-m-d'),
                    $entry->frequency,
                    $entry->verification_status,
                    $entry->enteredBy->full_name ?? '',
                    $entry->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
