<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Display the authenticated user's notifications.
     */
    public function index(Request $request): View
    {
        $notifications = Notification::where(
            'user_id',
            $request->user()->id
        )
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('notifications.index', [
            'notifications' => $notifications,
        ]);
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
}
