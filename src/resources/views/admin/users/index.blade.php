<x-admin-layout>

    @php
        $isAr = app()->getLocale() === 'ar';
    @endphp

    <div class="space-y-6" dir="{{ $isAr ? 'rtl' : 'ltr' }}">

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <p class="text-sm font-medium text-cyan-600">
                    HAG Brokers
                </p>

                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                    {{ $isAr ? 'المستخدمون' : 'Users' }}
                </h1>

                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ $isAr
                        ? 'إدارة حسابات المستخدمين والأدوار والصلاحيات والعملاء المرتبطين.'
                        : 'Manage user accounts, roles, permissions and customer assignments.' }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">

                <span
                    class="inline-flex items-center rounded-full bg-cyan-50 px-3 py-1.5 text-sm font-medium text-cyan-700">
                    {{ $users->total() }}
                    {{ $isAr ? 'مستخدم' : 'Users' }}
                </span>

                <a href="{{ route('admin.users.create') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">

                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                    </svg>

                    {{ $isAr ? 'إضافة مستخدم' : 'Add User' }}

                </a>

            </div>

        </div>


        {{-- Flash Messages --}}
        @if (session('success'))
            <div
                class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                {{ session('error') }}
            </div>
        @endif


        {{-- Search --}}
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow-sm">

            <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col gap-3 sm:flex-row">

                <div class="relative flex-1">

                    <div class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3">
                        <svg class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m21 21-4.35-4.35m1.35-5.4a6.75 6.75 0 1 1-13.5 0 6.75 6.75 0 0 1 13.5 0Z" />
                        </svg>
                    </div>

                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="{{ $isAr ? 'البحث بالاسم أو البريد الإلكتروني...' : 'Search by name or email...' }}"
                        class="block w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 py-2.5 ps-10 pe-4 text-sm text-slate-900 dark:text-white placeholder-slate-400 outline-none transition focus:border-cyan-500 focus:bg-white focus:ring-2 focus:ring-cyan-100">

                </div>

                <button type="submit"
                    class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">

                    {{ $isAr ? 'بحث' : 'Search' }}

                </button>

                @if (request()->filled('search'))
                    <a href="{{ route('admin.users.index') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-5 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200 transition hover:bg-slate-50">

                        {{ $isAr ? 'مسح' : 'Clear' }}

                    </a>
                @endif

            </form>

        </div>


        {{-- Users Table --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm">

            {{-- Desktop --}}
            <div class="hidden overflow-x-auto md:block">

                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">

                    <thead class="bg-slate-50 dark:bg-slate-950">

                        <tr>

                            <th
                                class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                {{ $isAr ? 'المستخدم' : 'User' }}
                            </th>

                            <th
                                class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                {{ $isAr ? 'البريد' : 'Email' }}
                            </th>

                            <th
                                class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                {{ $isAr ? 'الأدوار' : 'Roles' }}
                            </th>

                            <th
                                class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                {{ $isAr ? 'الصلاحيات' : 'Permissions' }}
                            </th>

                            <th
                                class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                {{ $isAr ? 'العميل' : 'Customer' }}
                            </th>

                            <th
                                class="px-6 py-4 text-end text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                {{ $isAr ? 'الإجراءات' : 'Actions' }}
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">

                        @forelse ($users as $user)

                            <tr class="transition hover:bg-slate-50">

                                {{-- User --}}
                                <td class="whitespace-nowrap px-6 py-4">

                                    <div class="flex items-center gap-3">

                                        <div
                                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-cyan-50 text-sm font-bold text-cyan-700">

                                            {{ strtoupper(substr($user->name ?: 'U', 0, 1)) }}

                                        </div>

                                        <div>

                                            <div class="font-semibold text-slate-900 dark:text-white">
                                                {{ $user->name ?: 'Unnamed User' }}
                                            </div>

                                            <div class="text-xs text-slate-500 dark:text-slate-400">
                                                #{{ $user->id }}
                                            </div>

                                        </div>

                                    </div>

                                </td>


                                {{-- Email --}}
                                <td class="whitespace-nowrap px-6 py-4">

                                    @if ($user->email)
                                        <a href="mailto:{{ $user->email }}"
                                            class="text-sm text-slate-700 dark:text-slate-200 transition hover:text-cyan-600">
                                            {{ $user->email }}
                                        </a>
                                    @else
                                        <span class="text-sm text-slate-400">—</span>
                                    @endif

                                </td>


                                {{-- Roles --}}
                                <td class="px-6 py-4">

                                    <div class="flex max-w-xs flex-wrap gap-1.5">

                                        @forelse ($user->roles as $role)
                                            <span
                                                class="inline-flex items-center rounded-full bg-purple-50 px-2.5 py-1 text-xs font-semibold text-purple-700">

                                                {{ $role->display_name ?? $role->name }}

                                            </span>

                                        @empty

                                            <span
                                                class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">

                                                {{ $isAr ? 'بدون Role' : 'No Role' }}

                                            </span>
                                        @endforelse

                                    </div>

                                </td>


                                {{-- Permissions --}}
                                <td class="px-6 py-4">

                                    <span
                                        class="inline-flex items-center rounded-full bg-cyan-50 px-2.5 py-1 text-xs font-semibold text-cyan-700">

                                        {{ $user->permissions->count() }}

                                        {{ $isAr ? 'مباشرة' : 'Direct' }}

                                    </span>

                                </td>


                                {{-- Customer --}}
                                <td class="whitespace-nowrap px-6 py-4">

                                    @if ($user->customer)
                                        <div class="text-sm font-semibold text-slate-800 dark:text-slate-100">
                                            {{ $user->customer->company_name ?? ($user->customer->name ?? 'Customer') }}
                                        </div>

                                        <div class="text-xs text-slate-400">
                                            #{{ $user->customer_id }}
                                        </div>
                                    @else
                                        <span
                                            class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">

                                            {{ $isAr ? 'غير مرتبط' : 'Not Assigned' }}

                                        </span>
                                    @endif

                                </td>


                                {{-- Actions --}}
                                <td class="whitespace-nowrap px-6 py-4">

                                    <div class="flex items-center justify-end gap-2">

                                        <a href="{{ route('admin.users.show', $user) }}"
                                            class="rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-3 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50">
                                            {{ $isAr ? 'عرض' : 'View' }}
                                        </a>

                                        <a href="{{ route('admin.users.edit', $user) }}"
                                            class="rounded-lg bg-cyan-50 px-3 py-1.5 text-xs font-semibold text-cyan-700 hover:bg-cyan-100">
                                            {{ $isAr ? 'تعديل' : 'Edit' }}
                                        </a>

                                        @if (auth()->id() !== $user->id)
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                                onsubmit="return confirm('{{ $isAr ? 'هل أنت متأكد من حذف هذا المستخدم؟' : 'Are you sure you want to delete this user?' }}')">

                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                    class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100">

                                                    {{ $isAr ? 'حذف' : 'Delete' }}

                                                </button>

                                            </form>
                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="6" class="px-6 py-16 text-center">

                                    <div
                                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100">

                                        <svg class="h-7 w-7 text-slate-400" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="1.7">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                            <circle cx="9" cy="7" r="4" />
                                        </svg>

                                    </div>

                                    <h3 class="mt-4 text-sm font-semibold text-slate-900 dark:text-white">
                                        {{ $isAr ? 'لا يوجد مستخدمون' : 'No users found' }}
                                    </h3>

                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                        {{ $isAr ? 'لم يتم العثور على أي حساب مستخدم.' : 'No user accounts are available.' }}
                                    </p>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- Mobile --}}
            <div class="divide-y divide-slate-100 dark:divide-slate-800 md:hidden">

                @forelse ($users as $user)

                    <div class="p-4">

                        <div class="flex items-start justify-between gap-3">

                            <div class="flex min-w-0 items-center gap-3">

                                <div
                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-cyan-50 text-sm font-bold text-cyan-700">

                                    {{ strtoupper(substr($user->name ?: 'U', 0, 1)) }}

                                </div>

                                <div class="min-w-0">

                                    <p class="truncate font-semibold text-slate-900 dark:text-white">
                                        {{ $user->name ?: 'Unnamed User' }}
                                    </p>

                                    <p class="truncate text-sm text-slate-500 dark:text-slate-400">
                                        {{ $user->email }}
                                    </p>

                                </div>

                            </div>

                        </div>


                        {{-- Roles --}}
                        <div class="mt-4">

                            <p class="mb-2 text-xs font-medium text-slate-400">
                                {{ $isAr ? 'Roles' : 'Roles' }}
                            </p>

                            <div class="flex flex-wrap gap-1.5">

                                @forelse ($user->roles as $role)
                                    <span
                                        class="rounded-full bg-purple-50 px-2.5 py-1 text-xs font-semibold text-purple-700">

                                        {{ $role->display_name ?? $role->name }}

                                    </span>

                                @empty

                                    <span class="text-sm text-amber-600">
                                        {{ $isAr ? 'بدون Role' : 'No Role' }}
                                    </span>
                                @endforelse

                            </div>

                        </div>


                        {{-- Info --}}
                        <div class="mt-4 grid grid-cols-2 gap-3">

                            <div class="rounded-xl bg-slate-50 dark:bg-slate-950 p-3">

                                <p class="text-xs font-medium text-slate-400">
                                    {{ $isAr ? 'User ID' : 'User ID' }}
                                </p>

                                <p class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-100">
                                    #{{ $user->id }}
                                </p>

                            </div>

                            <div class="rounded-xl bg-slate-50 dark:bg-slate-950 p-3">

                                <p class="text-xs font-medium text-slate-400">
                                    {{ $isAr ? 'Customer' : 'Customer' }}
                                </p>

                                <p class="mt-1 truncate text-sm font-semibold text-slate-800 dark:text-slate-100">

                                    @if ($user->customer)
                                        {{ $user->customer->company_name ?? ($user->customer->name ?? '#' . $user->customer_id) }}
                                    @else
                                        <span class="text-amber-600">
                                            {{ $isAr ? 'غير مرتبط' : 'Not Assigned' }}
                                        </span>
                                    @endif

                                </p>

                            </div>

                            <div class="rounded-xl bg-slate-50 dark:bg-slate-950 p-3">

                                <p class="text-xs font-medium text-slate-400">
                                    {{ $isAr ? 'Permissions' : 'Permissions' }}
                                </p>

                                <p class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-100">
                                    {{ $user->permissions->count() }}
                                </p>

                            </div>

                            <div class="rounded-xl bg-slate-50 dark:bg-slate-950 p-3">

                                <p class="text-xs font-medium text-slate-400">
                                    {{ $isAr ? 'Joined' : 'Joined' }}
                                </p>

                                <p class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-100">
                                    {{ $user->created_at?->format('d M Y') ?? '—' }}
                                </p>

                            </div>

                        </div>


                        {{-- Actions --}}
                        <div class="mt-4 grid grid-cols-2 gap-2">

                            <a href="{{ route('admin.users.show', $user) }}"
                                class="inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50">

                                {{ $isAr ? 'عرض' : 'View' }}

                            </a>

                            <a href="{{ route('admin.users.edit', $user) }}"
                                class="inline-flex items-center justify-center rounded-xl bg-cyan-50 px-4 py-2.5 text-sm font-semibold text-cyan-700 hover:bg-cyan-100">

                                {{ $isAr ? 'تعديل' : 'Edit' }}

                            </a>

                            @if (auth()->id() !== $user->id)
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                    class="col-span-2"
                                    onsubmit="return confirm('{{ $isAr ? 'هل أنت متأكد من حذف هذا المستخدم؟' : 'Are you sure you want to delete this user?' }}')">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                        class="inline-flex w-full items-center justify-center rounded-xl bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-700 hover:bg-red-100">

                                        {{ $isAr ? 'حذف المستخدم' : 'Delete User' }}

                                    </button>

                                </form>
                            @endif

                        </div>

                    </div>

                @empty

                    <div class="px-6 py-16 text-center">

                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">
                            {{ $isAr ? 'لا يوجد مستخدمون' : 'No users found' }}
                        </h3>

                    </div>

                @endforelse

            </div>

        </div>


        {{-- Pagination --}}
        @if ($users->hasPages())
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-4 py-3 shadow-sm sm:px-6">
                {{ $users->links() }}
            </div>
        @endif

    </div>

</x-admin-layout>
