<?php

namespace App\Http\Controllers;

use App\Models\{Notification, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * NotificationController
 * 
 * Handles notification management operations for the KMC M&E System
 */
class NotificationController extends Controller
{
    /**
     * Display a listing of notifications
     */
    public function index(Request $request)
    {
        $query = Auth::user()->notifications()->with(['user']);

        // Apply filters
        if ($request->has('type') && $request->type != '') {
            $query->byType($request->type);
        }

        if ($request->has('category') && $request->category != '') {
            $query->byCategory($request->category);
        }

        if ($request->has('read_status') && $request->read_status != '') {
            if ($request->read_status === 'read') {
                $query->read();
            } else {
                $query->unread();
            }
        }

        if ($request->has('search') && $request->search != '') {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('message', 'like', "%{$request->search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $notifications = $query->paginate(20);

        // Get filter options
        $types = Notification::types();
        $categories = ['project', 'expenditure', 'data_entry', 'system', 'user'];

        // Calculate statistics
        $stats = [
            'total' => Auth::user()->notifications()->count(),
            'unread' => Auth::user()->unreadNotifications()->count(),
            'read' => Auth::user()->notifications()->read()->count(),
            'email_sent' => Auth::user()->notifications()->where('email_sent', true)->count(),
        ];

        return view('notifications.index', compact(
            'notifications',
            'types',
            'categories',
            'stats'
        ));
    }

    /**
     * Display the specified notification
     */
    public function show(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Mark as read if unread
        if (!$notification->is_read) {
            $notification->markAsRead();
        }

        return view('notifications.show', compact('notification'));
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $notification->markAsRead();

        return back()
            ->with('success', 'Notification marked as read!');
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $notification->markAsUnread();

        return back()
            ->with('success', 'Notification marked as unread!');
    }

    /**
     * Delete notification
     */
    public function destroy(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $notification->delete();

        return redirect()
            ->route('notifications.index')
            ->with('success', 'Notification deleted successfully!');
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications()->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return back()
            ->with('success', 'All notifications marked as read!');
    }

    /**
     * Delete all read notifications
     */
    public function deleteReadNotifications()
    {
        $deletedCount = Auth::user()->notifications()
            ->read()
            ->delete();

        return back()
            ->with('success', "Deleted {$deletedCount} read notifications!");
    }

    /**
     * Get unread notifications count (API)
     */
    public function unreadCount(Request $request)
    {
        $count = $request->user()->unreadNotifications()->count();

        return response()->json([
            'unread_count' => $count,
        ]);
    }

    /**
     * Get recent notifications (API)
     */
    public function recent(Request $request)
    {
        $limit = $request->get('limit', 10);
        
        $notifications = $request->user()->notifications()
            ->with(['user'])
            ->latest()
            ->take($limit)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'category' => $notification->category,
                    'is_read' => $notification->is_read,
                    'created_at' => $notification->created_at->toISOString(),
                    'time_ago' => $notification->time_ago,
                    'action_url' => $notification->action_url,
                ];
            });

        return response()->json($notifications);
    }

    /**
     * Get notifications (API)
     */
    public function apiIndex(Request $request)
    {
        $query = $request->user()->notifications();

        // Apply filters
        if ($request->has('type')) {
            $query->byType($request->type);
        }

        if ($request->has('category')) {
            $query->byCategory($request->category);
        }

        if ($request->has('is_read')) {
            if ($request->is_read) {
                $query->read();
            } else {
                $query->unread();
            }
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $notifications = $query->paginate($request->get('per_page', 20));

        return response()->json($notifications);
    }

    /**
     * Mark notification as read (API)
     */
    public function markAsReadApi(Request $request, $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * Mark all notifications as read (API)
     */
    public function markAllAsReadApi(Request $request)
    {
        $count = $request->user()->unreadNotifications()->count();
        $request->user()->unreadNotifications()->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Marked {$count} notifications as read",
        ]);
    }

    /**
     * Delete notification (API)
     */
    public function destroyApi(Request $request, $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted',
        ]);
    }

    /**
     * Create custom notification (Admin only)
     */
    public function createCustom(Request $request)
    {
        if (!Auth::user()->hasPermission('manage_notifications')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'type' => 'required|in:' . implode(',', Notification::types()),
            'category' => 'required|string|max:50',
            'action_url' => 'nullable|url',
            'target_users' => 'required|array',
            'target_users.*' => 'exists:users,id',
            'send_email' => 'boolean',
        ]);

        $createdCount = 0;
        foreach ($validated['target_users'] as $userId) {
            Notification::create([
                'user_id' => $userId,
                'title' => $validated['title'],
                'message' => $validated['message'],
                'type' => $validated['type'],
                'category' => $validated['category'],
                'action_url' => $validated['action_url'],
                'email_sent' => $validated['send_email'] ?? false,
            ]);
            $createdCount++;
        }

        return back()
            ->with('success', "Notification sent to {$createdCount} users!");
    }

    /**
     * Show custom notification creation form
     */
    public function showCreateCustom()
    {
        if (!Auth::user()->hasPermission('manage_notifications')) {
            abort(403, 'Unauthorized action.');
        }

        $types = Notification::types();
        $users = User::active()->orderBy('full_name')->get();

        return view('notifications.create-custom', compact('types', 'users'));
    }

    /**
     * Get notification statistics (API)
     */
    public function statistics(Request $request)
    {
        $user = $request->user();
        
        $stats = [
            'total' => $user->notifications()->count(),
            'unread' => $user->unreadNotifications()->count(),
            'read' => $user->notifications()->read()->count(),
            'by_type' => Notification::types()->mapWithKeys(function ($type) use ($user) {
                return [$type => $user->notifications()->byType($type)->count()];
            }),
            'by_category' => ['project', 'expenditure', 'data_entry', 'system', 'user']
                ->mapWithKeys(function ($category) use ($user) {
                    return [$category => $user->notifications()->byCategory($category)->count()];
                }),
            'recent' => $user->notifications()
                ->latest()
                ->take(5)
                ->get()
                ->map->getSummary(),
        ];

        return response()->json($stats);
    }

    /**
     * Bulk delete notifications
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'notification_ids' => 'required|array',
            'notification_ids.*' => 'exists:notifications,id',
        ]);

        $deletedCount = Auth::user()->notifications()
            ->whereIn('id', $validated['notification_ids'])
            ->delete();

        return back()
            ->with('success', "Successfully deleted {$deletedCount} notifications!");
    }

    /**
     * Bulk mark as read
     */
    public function bulkMarkAsRead(Request $request)
    {
        $validated = $request->validate([
            'notification_ids' => 'required|array',
            'notification_ids.*' => 'exists:notifications,id',
        ]);

        $updatedCount = Auth::user()->notifications()
            ->whereIn('id', $validated['notification_ids'])
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return back()
            ->with('success', "Successfully marked {$updatedCount} notifications as read!");
    }
}
