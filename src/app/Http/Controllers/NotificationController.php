<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = Notification::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return view('notifications.index', [
            'notifications' => $notifications,
        ]);
    }

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

        return view('notifications.show', [
            'notification' => $notification,
        ]);
    }

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

    public function markAllRead(
        Request $request
    ): RedirectResponse {
        Notification::query()
            ->where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
            ]);

        return back()->with(
            'success',
            'All notifications marked as read.'
        );
    }

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
            'Notification deleted.'
        );
    }
}
