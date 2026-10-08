<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $notifications = Notification::query()
            ->where('user_id', $user->id)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('type', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $unreadCount = Notification::query()
            ->where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

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

        NotificationService::user(
            user: $user,
            type: 'admin.message',
            title: $validated['title'],
            message: $validated['message'],
            model: null,
            data: [
                'url' => $validated['url'] ?? null,
                'sender_id' => $request->user()->id,
                'sender_name' => $request->user()->name,
            ],
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

        $item = Notification::query()
            ->where('id', $notification)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $item->update([
            'is_read' => true,
        ]);

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
        Notification::query()
            ->where('user_id', request()->user()->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
            ]);

        return back()->with(
            'success',
            'All notifications marked as read.'
        );
    }
}
