<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Admin Notifications Controller
 */
class AdminNotificationController extends Controller
{
    /**
     * Display a listing of notifications
     */
    public function index(Request $request)
    {
        $query = Notification::with('user:id,full_name,email');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('full_name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by read status
        if ($request->filled('read_status')) {
            if ($request->read_status === 'read') {
                $query->whereNotNull('read_at');
            } elseif ($request->read_status === 'unread') {
                $query->whereNull('read_at');
            }
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

        $notifications = $query->paginate($request->per_page ?? 20)->withQueryString();

        // Get stats
        $stats = [
            'total' => Notification::count(),
            'unread' => Notification::whereNull('read_at')->count(),
            'today' => Notification::whereDate('created_at', today())->count(),
        ];

        return Inertia::render('Admin/Notifications/Index', [
            'notifications' => $notifications,
            'filters' => $request->only(['search', 'type', 'read_status', 'user', 'date_from', 'date_to']),
            'stats' => $stats,
            'types' => Notification::distinct('type')->pluck('type'),
        ]);
    }
}
