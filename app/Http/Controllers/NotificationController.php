<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get recent notifications (for AJAX polling).
     */
    public function index(Request $request)
    {
        $notifications = Notification::orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($n) {
                return [
                    'id'         => $n->id,
                    'type'       => $n->type,
                    'title'      => $n->title,
                    'message'    => $n->message,
                    'icon'       => $n->icon,
                    'color'      => $n->color,
                    'link'       => $n->link,
                    'is_read'    => $n->is_read,
                    'time_ago'   => $n->created_at->diffForHumans(),
                    'created_at' => $n->created_at->format('M d, Y h:i A'),
                ];
            });

        $unreadCount = Notification::where('is_read', false)->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => $unreadCount,
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        Notification::where('is_read', false)->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Show the full notifications page.
     */
    public function page(Request $request)
    {
        $query = Notification::orderBy('created_at', 'desc');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filter === 'unread') {
            $query->where('is_read', false);
        } elseif ($request->filter === 'read') {
            $query->where('is_read', true);
        }

        $notifications = $query->paginate(20);
        $unreadCount = Notification::where('is_read', false)->count();
        $types = Notification::select('type')->distinct()->pluck('type');

        return view('notifications.index', compact('notifications', 'unreadCount', 'types'));
    }

    /**
     * Delete old notifications (cleanup, older than 30 days).
     */
    public function cleanup()
    {
        $deleted = Notification::where('created_at', '<', now()->subDays(30))->delete();

        return response()->json(['success' => true, 'deleted' => $deleted]);
    }
}
