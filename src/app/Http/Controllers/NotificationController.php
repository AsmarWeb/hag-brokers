<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Display user's notifications.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Display a single notification.
     */
    public function show(
        Request $request,
        string $notification
    ): View {
        $item = $request->user()
            ->notifications()
            ->where('id', $notification)
            ->firstOrFail();

        if ($item->unread()) {
            $item->markAsRead();
        }

        return view('notifications.show', [
            'notification' => $item,
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markRead(
        Request $request,
        string $notification
    ): RedirectResponse {
        $item = $request->user()
            ->notifications()
            ->where('id', $notification)
            ->firstOrFail();

        $item->markAsRead();

        return back()->with(
            'success',
            'Notification marked as read.'
        );
    }

    /**
     * Mark all user's notifications as read.
     */
    public function markAllRead(
        Request $request
    ): RedirectResponse {
        $request->user()
            ->unreadNotifications
            ->markAsRead();

        return back()->with(
            'success',
            'All notifications marked as read.'
        );
    }

    /**
     * Delete a notification.
     */
    public function destroy(
        Request $request,
        string $notification
    ): RedirectResponse {
        $request->user()
            ->notifications()
            ->where('id', $notification)
            ->firstOrFail()
            ->delete();

        return back()->with(
            'success',
            'Notification deleted successfully.'
        );
    }
}