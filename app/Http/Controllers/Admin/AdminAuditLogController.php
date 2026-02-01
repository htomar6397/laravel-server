<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Admin Audit Logs Controller
 */
class AdminAuditLogController extends Controller
{
    /**
     * Display a listing of audit logs
     */
    public function index(Request $request)
    {
        $query = AuditLog::with('user:id,full_name,email');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                    ->orWhere('model_type', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('full_name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by model type
        if ($request->filled('model')) {
            $query->where('model_type', 'like', "%{$request->model}%");
        }

        // Filter by user
        if ($request->filled('user')) {
            $query->where('user_id', $request->user);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sort
        $sortField = $request->sort_field ?? 'created_at';
        $sortDirection = $request->sort_direction ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        $logs = $query->paginate($request->per_page ?? 20)->withQueryString();

        return Inertia::render('Admin/AuditLogs/Index', [
            'logs' => $logs,
            'filters' => $request->only(['search', 'action', 'model', 'user', 'date_from', 'date_to']),
            'actions' => AuditLog::distinct('action')->pluck('action'),
            'models' => AuditLog::distinct('model_type')->pluck('model_type')->map(function ($model) {
                return class_basename($model);
            })->unique()->values(),
        ]);
    }

    /**
     * Display the specified audit log
     */
    public function show(AuditLog $auditLog)
    {
        $auditLog->load('user:id,full_name,email,username');

        return Inertia::render('Admin/AuditLogs/Show', [
            'log' => $auditLog,
        ]);
    }
}
