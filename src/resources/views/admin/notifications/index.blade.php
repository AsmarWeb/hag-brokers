<x-admin-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                Notifications
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Monitor system and customer notifications.
            </p>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            <div
                class="mb-6 rounded-2xl bg-white p-4 shadow ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-800">
                <form method="GET" action="{{ route('admin.notifications.index') }}"
                    class="flex flex-col gap-3 sm:flex-row">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search title, message, type or user..."
                        class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white">

                    <button class="rounded-xl bg-cyan-600 px-6 py-2.5 font-semibold text-white">
                        Search
                    </button>
                </form>
            </div>

            <div class="space-y-4">
                @forelse($notifications as $notification)
                    <div
                        class="rounded-2xl bg-white p-5 shadow ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-800">

                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-bold text-slate-900 dark:text-white">
                                        {{ $notification->title }}
                                    </h3>

                                    @if (!$notification->is_read)
                                        <span
                                            class="rounded-full bg-cyan-100 px-2.5 py-1 text-xs font-semibold text-cyan-700">
                                            Unread
                                        </span>
                                    @endif
                                </div>

                                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                                    {{ $notification->message }}
                                </p>
                            </div>

                            <span
                                class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                {{ $notification->type }}
                            </span>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-4 text-xs text-slate-500">
                            <span>
                                User:
                                {{ $notification->user?->name ?? '—' }}
                            </span>

                            <span>
                                {{ $notification->created_at?->format('Y-m-d H:i') }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl bg-white p-12 text-center text-slate-500 shadow">
                        No notifications found.
                    </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
        </div>
    </div>
</x-admin-layout>
