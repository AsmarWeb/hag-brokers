<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Display user's notifications.
     */
    public function index(Request $request): View
    {
        $notifications = Notification::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Display a single notification.
     */
    public function show(
        Request $request,
        Notification $notification
    ): View {
        abort_unless(
            $notification->user_id === $request->user()->id,
            403
        );

        if (! $notification->is_read) {
            $notification->update([
                'is_read' => true,
            ]);
        }

        return view('notifications.show', compact('notification'));
    }

    /**
     * Mark a notification as read.
     */
    public function markRead(
        Request $request,
        Notification $notification
    ): RedirectResponse {
        abort_unless(
            $notification->user_id === $request->user()->id,
            403
        );

        $notification->update([
            'is_read' => true,
        ]);

        return back()->with(
            'success',
            'Notification marked as read.'
        );
    }

    /**
     * Mark all user's notifications as read.
     */
    public function markAllRead(Request $request): RedirectResponse
    {
        Notification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
            ]);

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
        Notification $notification
    ): RedirectResponse {
        abort_unless(
            $notification->user_id === $request->user()->id,
            403
        );

        $notification->delete();

        return back()->with(
            'success',
            'Notification deleted successfully.'
        );
    }
}
