<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class AdminNotificationController extends Controller
{
    /**
     * Display admin notifications.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $search = $request->string('search')->trim()->toString();

        $notifications = $user->notifications()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('type', 'like', "%{$search}%")
                        ->orWhere('data', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $unreadCount = $user->unreadNotifications()->count();

        return view('admin.notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

    /**
     * Show the send notification form.
     */
    public function create(): View
    {
        $users = User::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'email',
            ]);

        return view('admin.notifications.create', [
            'users' => $users,
        ]);
    }

    /**
     * Send a notification to a selected user.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'message' => [
                'required',
                'string',
                'max:5000',
            ],

            'url' => [
                'nullable',
                'string',
                'max:2048',
            ],
        ]);

        $user = User::findOrFail($validated['user_id']);

        $user->notify(
            new \App\Notifications\AdminPlatformNotification(
                type: 'admin.message',
                title: $validated['title'],
                message: $validated['message'],
                url: $validated['url'] ?? null,
                extra: [
                    'sender_id' => $request->user()->id,
                    'sender_name' => $request->user()->name,
                ],
            )
        );

        return redirect()
            ->route('admin.notifications.create')
            ->with(
                'success',
                'Notification sent successfully to ' .
                    ($user->name ?: $user->email) .
                    '.'
            );
    }

    /**
     * Mark one notification as read.
     */
    public function markRead(string $notification): RedirectResponse
    {
        $user = request()->user();

        $item = $user->notifications()
            ->where('id', $notification)
            ->firstOrFail();

        $item->markAsRead();

        return back()->with(
            'success',
            'Notification marked as read.'
        );
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(): RedirectResponse
    {
        request()
            ->user()
            ->unreadNotifications
            ->markAsRead();

        return back()->with(
            'success',
            'All notifications marked as read.'
        );
    }
}
