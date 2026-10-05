<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Display the authenticated user's notifications.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $notifications = $user
            ->notifications()
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $unreadCount = $user
            ->unreadNotifications()
            ->count();

        return view('notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

    /**
     * Display one notification and mark it as read.
     */
    public function show(
        Request $request,
        string $notification
    ): View {
        $user = $request->user();

        $item = $user
            ->notifications()
            ->findOrFail($notification);

        if ($item->unread()) {
            $item->markAsRead();
        }

        return view('notifications.show', [
            'notification' => $item,
        ]);
    }

    /**
     * Mark one notification as read.
     */
    public function markRead(
        Request $request,
        string $notification
    ): RedirectResponse {
        $user = $request->user();

        $item = $user
            ->notifications()
            ->findOrFail($notification);

        $item->markAsRead();

        return back()->with(
            'success',
            'Notification marked as read.'
        );
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(
        Request $request
    ): RedirectResponse {
        $user = $request->user();

        $user
            ->unreadNotifications
            ->markAsRead();

        return back()->with(
            'success',
            'All notifications marked as read.'
        );
    }

    /**
     * Delete one notification.
     */
    public function destroy(
        Request $request,
        string $notification
    ): RedirectResponse {
        $user = $request->user();

        $item = $user
            ->notifications()
            ->findOrFail($notification);

        $item->delete();

        return back()->with(
            'success',
            'Notification deleted.'
        );
    }
}
