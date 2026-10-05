<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminNotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $search = $request
            ->string('search')
            ->trim()
            ->toString();

        $notifications = $user
            ->notifications()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('type', 'like', "%{$search}%")
                        ->orWhere('data->title', 'like', "%{$search}%")
                        ->orWhere('data->message', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $unreadCount = $user
            ->unreadNotifications()
            ->count();

        return view('admin.notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

    public function markRead(string $notification): RedirectResponse
    {
        $user = request()->user();

        $item = $user
            ->notifications()
            ->findOrFail($notification);

        $item->markAsRead();

        return back();
    }

    public function markAllRead(): RedirectResponse
    {
        request()->user()
            ->unreadNotifications
            ->markAsRead();

        return back();
    }
}
