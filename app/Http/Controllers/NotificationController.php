<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Return unread count + latest 8 notifications for the dropdown.
     * GET /notifications/dropdown
     */
    public function dropdown()
    {
        $user = Auth::user();

        $notifications = Notification::forUser($user->id)
            ->latest()
            ->take(8)
            ->get();

        $unreadCount = Notification::forUser($user->id)->unread()->count();

        return response()->json([
            'unread_count'  => $unreadCount,
            'notifications' => $notifications->map(fn($n) => [
                'id'        => $n->id,
                'type'      => $n->type,
                'title'     => $n->title,
                'message'   => $n->message,
                'url'       => $n->url,
                'icon'      => $n->icon,
                'color'     => $n->color,
                'is_unread' => $n->isUnread(),
                'time'      => $n->created_at->diffForHumans(),
            ]),
        ]);
    }

    /**
     * Mark a single notification as read.
     * GET /notifications/{id}/read
     *
     * - From the notification page (<a href>): redirects to dashboard after marking read
     * - From the dropdown (fetch with Accept: application/json): returns JSON only, no redirect
     */
    public function markRead($id)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $notification->markAsRead();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        // Notification page click — just go back to notifications list
        return redirect()->route('notifications.index');
    }

    /**
     * Mark all notifications as read for the current user.
     * POST /notifications/read-all
     */
    public function markAllRead()
    {
        Notification::forUser(Auth::id())
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Full notifications page.
     * GET /notifications
     */
    public function index()
    {
        $notifications = Notification::forUser(Auth::id())
            ->latest()
            ->paginate(20);

        return view('notifications', compact('notifications'));
    }
}