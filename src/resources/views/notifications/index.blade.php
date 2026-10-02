<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-cyan-400 text-sm font-black text-slate-950 shadow-lg shadow-cyan-400/20">
                        HAG
                    </div>

                    <div>
                        <h2 class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                            Notifications
                        </h2>

                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Stay updated with your shipments and account activity.
                        </p>
                    </div>
                </div>
            </div>

            <a href="{{ route('dashboard') }}"
                class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:border-cyan-300 hover:text-cyan-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-cyan-500 dark:hover:text-cyan-400">
                ← Dashboard
            </a>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-50 dark:bg-slate-950">
        <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">

            {{-- Success message --}}
            @if (session('success'))
                <div
                    class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-300">
                    <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>

                    <span>{{ session('success') }}</span>
                </div>
            @endif

            {{-- Hero --}}
            <section
                class="relative overflow-hidden rounded-3xl border border-slate-800 bg-slate-950 p-6 shadow-xl sm:p-8">
                <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-cyan-500/10 blur-3xl"></div>
                <div class="absolute -bottom-24 -left-20 h-64 w-64 rounded-full bg-cyan-400/10 blur-3xl"></div>

                <div class="relative flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                    <div>
                        <div
                            class="mb-3 inline-flex items-center gap-2 rounded-full border border-cyan-400/20 bg-cyan-400/10 px-3 py-1 text-xs font-bold uppercase tracking-wider text-cyan-300">
                            <span class="h-2 w-2 rounded-full bg-cyan-400"></span>
                            HAG Brokers
                        </div>

                        <h1 class="text-3xl font-black tracking-tight text-white sm:text-4xl">
                            Notifications
                        </h1>

                        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-400 sm:text-base">
                            Important updates about your sea freight operations,
                            shipments, documents and account activity.
                        </p>
                    </div>

                    <div
                        class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl border border-cyan-400/20 bg-cyan-400/10">
                        <svg class="h-9 w-9 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                </div>
            </section>

            {{-- Notification list --}}
            <section
                class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="border-b border-slate-200 px-5 py-5 dark:border-slate-800 sm:px-6">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">
                                Your Notifications
                            </h2>

                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                Recent notifications from your account.
                            </p>
                        </div>

                        @if ($notifications->total() > 0)
                            <div
                                class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                {{ $notifications->total() }}
                                {{ $notifications->total() === 1 ? 'notification' : 'notifications' }}
                            </div>
                        @endif
                    </div>
                </div>

                @forelse ($notifications as $notification)
                    @php
                        $isRead = (bool) $notification->is_read;

                        $title = $notification->title ?? ($notification->subject ?? 'Notification');

                        $message =
                            $notification->message ??
                            ($notification->body ?? ($notification->content ?? 'You have a new notification.'));

                        $date = $notification->created_at;
                    @endphp

                    <div
                        class="group border-b border-slate-100 px-5 py-5 transition last:border-b-0 dark:border-slate-800 sm:px-6
                        {{ $isRead ? 'bg-white dark:bg-slate-900' : 'bg-cyan-50/40 dark:bg-cyan-950/10' }}">
                        <div class="flex gap-4">

                            {{-- Notification icon --}}
                            <div
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl
                                {{ $isRead
                                    ? 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'
                                    : 'bg-cyan-100 text-cyan-600 dark:bg-cyan-400/10 dark:text-cyan-400' }}">
                                @if ($isRead)
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                @else
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                @endif
                            </div>

                            {{-- Content --}}
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3
                                                class="text-sm font-extrabold
                                                {{ $isRead ? 'text-slate-800 dark:text-slate-200' : 'text-slate-950 dark:text-white' }}">
                                                {{ $title }}
                                            </h3>

                                            @unless ($isRead)
                                                <span
                                                    class="inline-flex items-center gap-1 rounded-full bg-cyan-100 px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-cyan-700 dark:bg-cyan-400/10 dark:text-cyan-300">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-cyan-500"></span>
                                                    New
                                                </span>
                                            @endunless
                                        </div>

                                        <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">
                                            {{ $message }}
                                        </p>

                                        @if ($date)
                                            <div class="mt-3 flex items-center gap-2 text-xs text-slate-400">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.5"
                                                        d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>

                                                <span>
                                                    {{ $date->format('M d, Y · H:i') }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Mark as read --}}
                                    @unless ($isRead)
                                        <form method="POST" action="{{ route('notifications.read', $notification) }}"
                                            class="shrink-0">
                                            @csrf

                                            <button type="submit"
                                                class="inline-flex items-center justify-center rounded-xl border border-cyan-200 bg-white px-3 py-2 text-xs font-bold text-cyan-700 transition hover:border-cyan-400 hover:bg-cyan-50 dark:border-cyan-900/50 dark:bg-slate-900 dark:text-cyan-400 dark:hover:border-cyan-600 dark:hover:bg-cyan-950/30">
                                                Mark as read
                                            </button>
                                        </form>
                                    @endunless
                                </div>
                            </div>
                        </div>
                    </div>
                @empty

                    {{-- Empty state --}}
                    <div class="px-6 py-16 text-center">
                        <div
                            class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800">
                            <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </div>

                        <h3 class="mt-5 text-lg font-extrabold text-slate-900 dark:text-white">
                            No notifications yet
                        </h3>

                        <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400">
                            You don't have any notifications at the moment.
                            Updates about your shipments and account activity will appear here.
                        </p>

                        <a href="{{ route('dashboard') }}"
                            class="mt-6 inline-flex items-center justify-center rounded-xl bg-cyan-500 px-5 py-3 text-sm font-extrabold text-slate-950 shadow-lg shadow-cyan-500/20 transition hover:bg-cyan-400">
                            Back to Dashboard
                        </a>
                    </div>
                @endforelse

                {{-- Pagination --}}
                @if ($notifications->hasPages())
                    <div class="border-t border-slate-200 px-5 py-5 dark:border-slate-800 sm:px-6">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </section>

            {{-- Footer information --}}
            <div class="flex flex-col items-center justify-between gap-3 text-xs text-slate-400 sm:flex-row">
                <p>
                    HAG Brokers — Sea Freight Solutions
                </p>

                <div class="flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-cyan-500"></span>
                    <span>Secure Platform</span>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
<x-app-layout>

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                Notifications
            </h2>

            @if ($notifications->where('is_read', false)->count())
                <form method="POST" action="{{ route('notifications.markAllRead') }}">
                    @csrf
                    @method('PATCH')

                    <button
                        type="submit"
                        class="text-sm font-semibold text-cyan-600 hover:text-cyan-500"
                    >
                        Mark all as read
                    </button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-5 rounded-lg bg-green-50 p-4 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-400">
                    {{ session('success') }}
                </div>
            @endif

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm dark:bg-slate-900">

                @forelse ($notifications as $notification)

                    <div class="border-b border-slate-200 p-5 last:border-b-0 dark:border-slate-800
                        {{ ! $notification->is_read ? 'bg-cyan-50/50 dark:bg-cyan-950/20' : '' }}">

                        <div class="flex items-start justify-between gap-4">

                            <div class="flex-1">

                                <a
                                    href="{{ route('notifications.show', $notification) }}"
                                    class="font-bold text-slate-900 hover:text-cyan-600 dark:text-white"
                                >
                                    {{ $notification->title }}
                                </a>

                                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                                    {{ $notification->message }}
                                </p>

                                <p class="mt-2 text-xs text-slate-400">
                                    {{ $notification->created_at->diffForHumans() }}
                                </p>

                            </div>

                            @if (! $notification->is_read)
                                <form
                                    method="POST"
                                    action="{{ route('notifications.markRead', $notification) }}"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="rounded-lg px-3 py-2 text-xs font-semibold text-cyan-600 hover:bg-cyan-50 dark:hover:bg-cyan-950/30"
                                    >
                                        Mark as read
                                    </button>
                                </form>
                            @endif

                        </div>

                    </div>

                @empty

                    <div class="p-12 text-center">

                        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800">
                            🔔
                        </div>

                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                            No notifications
                        </h3>

                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                            You don't have any notifications yet.
                        </p>

                    </div>

                @endforelse

            </div>

            @if ($notifications->hasPages())
                <div class="mt-6">
                    {{ $notifications->links() }}
                </div>
            @endif

        </div>
    </div>

</x-app-layout>