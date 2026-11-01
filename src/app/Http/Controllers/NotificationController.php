<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->latest()
            ->paginate(15);

        $unreadCount = $user->unreadNotifications()->count();

        return view('notifications.index', compact(
            'notifications',
            'unreadCount'
        ));
    }

    public function show(Request $request, string $notification): RedirectResponse
    {
        $item = $request->user()
            ->notifications()
            ->findOrFail($notification);

        $item->markAsRead();

        $url = $item->data['url'] ?? null;

        if (is_string($url) && ! str_contains($url, '\\')) {
            $parts = parse_url($url);

            if (is_array($parts)) {
                $path = $parts['path'] ?? '/';

                $relative = ! isset($parts['scheme'])
                    && ! isset($parts['host'])
                    && str_starts_with($url, '/')
                    && ! str_starts_with($url, '//');

                $app = parse_url(config('app.url'));

                $sameOrigin = isset($parts['scheme'], $parts['host'])
                    && strtolower($parts['scheme']) === strtolower($app['scheme'] ?? '')
                    && strtolower($parts['host']) === strtolower($app['host'] ?? '')
                    && ($parts['port'] ?? null) === ($app['port'] ?? null)
                    && ! isset($parts['user'])
                    && ! isset($parts['pass']);

                if (
                    ($relative || $sameOrigin)
                    && str_starts_with($path, '/')
                    && ! str_starts_with($path, '//')
                ) {
                    $target = $path;

                    if (isset($parts['query'])) {
                        $target .= '?' . $parts['query'];
                    }

                    return redirect($target);
                }
            }
        }

        return redirect()->route('notifications.index');
    }

    public function markRead(Request $request, string $notification): RedirectResponse
    {
        $item = $request->user()
            ->notifications()
            ->findOrFail($notification);

        $item->markAsRead();

        return back()->with('success',
            app()->getLocale() === 'ar'
                ? 'تم تحديد الإشعار كمقروء.'
                : 'Notification marked as read.'
        );
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()
            ->unreadNotifications()
            ->update(['read_at' => now()]);

        return back()->with('success',
            app()->getLocale() === 'ar'
                ? 'تم تحديد جميع الإشعارات كمقروءة.'
                : 'All notifications marked as read.'
        );
    }

    public function destroy(Request $request, string $notification): RedirectResponse
    {
        $request->user()
            ->notifications()
            ->findOrFail($notification)
            ->delete();

        return back()->with('success',
            app()->getLocale() === 'ar'
                ? 'تم حذف الإشعار.'
                : 'Notification deleted.'
        );
    }
}
