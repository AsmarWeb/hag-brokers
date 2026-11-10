<x-admin-layout>
    @php($isAr = app()->getLocale() === 'ar')
    <div class="space-y-6" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-cyan-600">HAG Brokers / Admin</p>
                <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900 dark:text-white">{{ $isAr ? 'إدارة المستخدمين' : 'User Management' }}</h1>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ $isAr ? 'إدارة الحسابات، الصلاحيات، وحالة الوصول من مكان واحد.' : 'Manage accounts, permissions and access status from one place.' }}</p>
            </div>
            <a href="{{ route('admin.users.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-slate-900/10 transition hover:-translate-y-0.5 hover:bg-cyan-700 dark:bg-cyan-600 dark:hover:bg-cyan-500">
                <span class="text-lg leading-none">+</span>{{ $isAr ? 'إضافة مستخدم' : 'Add user' }}
            </a>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-300">{{ session('error') }}</div>
        @endif

        <div class="grid gap-3 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $isAr ? 'إجمالي الحسابات' : 'Total accounts' }}</p>
                <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ number_format($totalUsers) }}</p>
                <p class="mt-1 text-xs text-slate-400">{{ $isAr ? 'جميع المستخدمين المسجلين' : 'All registered users' }}</p>
            </div>
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50/70 p-5 shadow-sm dark:border-emerald-900 dark:bg-emerald-950/20">
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">{{ $isAr ? 'حسابات نشطة' : 'Active accounts' }}</p>
                <p class="mt-2 text-3xl font-black text-emerald-800 dark:text-emerald-300">{{ number_format($activeUsers) }}</p>
                <p class="mt-1 text-xs text-emerald-700/70 dark:text-emerald-500">{{ $isAr ? 'يمكنها تسجيل الدخول' : 'Allowed to sign in' }}</p>
            </div>
            <div class="rounded-2xl border border-rose-200 bg-rose-50/70 p-5 shadow-sm dark:border-rose-900 dark:bg-rose-950/20">
                <p class="text-xs font-bold uppercase tracking-wider text-rose-700 dark:text-rose-400">{{ $isAr ? 'حسابات محظورة' : 'Blocked accounts' }}</p>
                <p class="mt-2 text-3xl font-black text-rose-800 dark:text-rose-300">{{ number_format($blockedUsers) }}</p>
                <p class="mt-1 text-xs text-rose-700/70 dark:text-rose-500">{{ $isAr ? 'تم إيقاف الوصول إليها' : 'Access has been suspended' }}</p>
            </div>
        </div>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="border-b border-slate-100 p-4 dark:border-slate-800 sm:p-5">
                <form method="GET" action="{{ route('admin.users.index') }}" class="grid gap-3 md:grid-cols-[minmax(0,1fr)_220px_auto_auto]">
                    <label class="relative block">
                        <span class="sr-only">{{ $isAr ? 'البحث' : 'Search' }}</span>
                        <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-slate-400">⌕</span>
                        <input name="search" value="{{ request('search') }}" type="search" placeholder="{{ $isAr ? 'ابحث بالاسم أو البريد الإلكتروني...' : 'Search name or email...' }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 ps-10 pe-3 text-sm text-slate-900 outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    </label>
                    <select name="status" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-sm text-slate-700 outline-none focus:border-cyan-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                        <option value="">{{ $isAr ? 'كل الحالات' : 'All statuses' }}</option>
                        <option value="active" @selected(request('status') === 'active')>{{ $isAr ? 'نشط' : 'Active' }}</option>
                        <option value="blocked" @selected(request('status') === 'blocked')>{{ $isAr ? 'محظور' : 'Blocked' }}</option>
                    </select>
                    <button class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-cyan-700 dark:bg-cyan-600" type="submit">{{ $isAr ? 'تطبيق البحث' : 'Apply filters' }}</button>
                    @if(request()->filled('search') || request()->filled('status'))
                        <a href="{{ route('admin.users.index') }}" class="rounded-xl border border-slate-200 px-5 py-3 text-center text-sm font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">{{ $isAr ? 'إعادة ضبط' : 'Reset' }}</a>
                    @endif
                </form>
            </div>

            <div class="flex items-center justify-between px-5 py-4">
                <div>
                    <h2 class="font-bold text-slate-900 dark:text-white">{{ $isAr ? 'قائمة المستخدمين' : 'User directory' }}</h2>
                    <p class="mt-1 text-xs text-slate-500">{{ $isAr ? 'النتائج' : 'Results' }}: {{ $users->total() }}</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $users->currentPage() }} / {{ max(1, $users->lastPage()) }}</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-800">
                    <thead class="bg-slate-50 text-start dark:bg-slate-950">
                        <tr>
                            <th class="px-5 py-3 text-start text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ $isAr ? 'المستخدم' : 'User' }}</th>
                            <th class="px-5 py-3 text-start text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ $isAr ? 'الأدوار' : 'Roles' }}</th>
                            <th class="px-5 py-3 text-start text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ $isAr ? 'العميل' : 'Customer' }}</th>
                            <th class="px-5 py-3 text-start text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ $isAr ? 'آخر دخول' : 'Last sign-in' }}</th>
                            <th class="px-5 py-3 text-start text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ $isAr ? 'الحالة' : 'Status' }}</th>
                            <th class="px-5 py-3 text-end text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ $isAr ? 'الإجراء' : 'Action' }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($users as $user)
                            <tr class="transition hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                                <td class="px-5 py-4">
                                    <div class="flex min-w-[230px] items-center gap-3">
                                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-500/15 to-blue-500/15 text-sm font-black text-cyan-700 dark:text-cyan-300">{{ mb_strtoupper(mb_substr($user->name ?: 'U', 0, 1)) }}</div>
                                        <div class="min-w-0">
                                            <a href="{{ route('admin.users.show', $user) }}" class="block truncate text-sm font-bold text-slate-900 hover:text-cyan-700 dark:text-white dark:hover:text-cyan-300">{{ $user->name ?: ($isAr ? 'مستخدم بدون اسم' : 'Unnamed user') }}</a>
                                            <p class="mt-1 truncate text-xs text-slate-500">{{ $user->email }}</p>
                                            <p class="mt-1 text-[11px] text-slate-400">#{{ $user->id }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex max-w-[220px] flex-wrap gap-1.5">
                                        @forelse($user->roles as $role)
                                            <span class="rounded-full bg-violet-50 px-2.5 py-1 text-[11px] font-bold text-violet-700 dark:bg-violet-950/40 dark:text-violet-300">{{ $role->display_name ?? $role->name }}</span>
                                        @empty
                                            <span class="text-xs text-slate-400">—</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">{{ $user->customer?->company_name ?? $user->customer?->name ?? '—' }}</td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <p class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ $user->last_login_at?->format('d M Y') ?? '—' }}</p>
                                    <p class="mt-1 font-mono text-[11px] text-slate-400">{{ $user->last_login_ip ?: ($isAr ? 'لا يوجد سجل بعد' : 'No record yet') }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    @if($user->blocked_at)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-2.5 py-1 text-xs font-bold text-rose-700 dark:bg-rose-950/40 dark:text-rose-300"><span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>{{ $isAr ? 'محظور' : 'Blocked' }}</span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>{{ $isAr ? 'نشط' : 'Active' }}</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-end">
                                    <a href="{{ route('admin.users.show', $user) }}" class="inline-flex rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-cyan-400 hover:text-cyan-700 dark:border-slate-700 dark:text-slate-200">{{ $isAr ? 'التفاصيل' : 'Details' }} <span class="ms-1">↗</span></a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-16 text-center">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-xl dark:bg-slate-800">⌕</div>
                                <p class="mt-3 font-bold text-slate-800 dark:text-white">{{ $isAr ? 'لا توجد نتائج' : 'No users found' }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $isAr ? 'جرّب تغيير كلمات البحث أو الفلاتر.' : 'Try adjusting your search or filters.' }}</p>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($users->hasPages())
                <div class="border-t border-slate-100 px-5 py-4 dark:border-slate-800">{{ $users->links() }}</div>
            @endif
        </section>
    </div>
</x-admin-layout>
