<x-admin-layout>

    @php
        $isAr = app()->getLocale() === 'ar';
    @endphp

    <div class="mx-auto max-w-5xl space-y-6" dir="{{ $isAr ? 'rtl' : 'ltr' }}">

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>

                <a href="{{ route('admin.users.index') }}"
                    class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 dark:text-slate-400 hover:text-cyan-600">

                    ← {{ $isAr ? 'العودة للمستخدمين' : 'Back to Users' }}

                </a>

            </div>

            <div class="flex gap-2">

                <a href="{{ route('admin.users.edit', $user) }}"
                    class="inline-flex items-center justify-center rounded-xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-cyan-700">

                    {{ $isAr ? 'تعديل' : 'Edit User' }}

                </a>

                @if (auth()->id() !== $user->id)
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                        onsubmit="return confirm('{{ $isAr ? 'هل أنت متأكد من حذف هذا المستخدم؟' : 'Are you sure you want to delete this user?' }}')">

                        @csrf
                        @method('DELETE')

                        <button type="submit"
                            class="inline-flex items-center justify-center rounded-xl bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-700 hover:bg-red-100">

                            {{ $isAr ? 'حذف' : 'Delete' }}

                        </button>

                    </form>
                @endif

            </div>

        </div>


        {{-- Profile Card --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm">

            <div class="p-6">

                <div class="flex flex-col gap-5 sm:flex-row sm:items-center">

                    <div
                        class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-cyan-50 text-2xl font-bold text-cyan-700">

                        {{ strtoupper(substr($user->name ?: 'U', 0, 1)) }}

                    </div>

                    <div class="min-w-0">

                        <p class="text-sm font-medium text-cyan-600">
                            HAG Brokers
                        </p>

                        <h1 class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">
                            {{ $user->name ?: 'Unnamed User' }}
                        </h1>
                        @if($user->blocked_at)
                            <span class="mt-2 inline-flex rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-700 dark:bg-rose-950/40 dark:text-rose-300">{{ $isAr ? 'محظور' : 'Blocked' }}</span>
                        @endif

                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            {{ $user->email }}
                        </p>

                        <p class="mt-2 text-xs text-slate-400">
                            User #{{ $user->id }}
                        </p>

                    </div>

                </div>

            </div>


            {{-- Basic Info --}}
            <div class="grid border-t border-slate-100 dark:border-slate-800 sm:grid-cols-3">

                <div class="border-b border-slate-100 dark:border-slate-800 p-5 sm:border-b-0 sm:border-r">

                    <p class="text-xs font-medium text-slate-400">
                        {{ $isAr ? 'تاريخ التسجيل' : 'Joined' }}
                    </p>

                    <p class="mt-1 font-semibold text-slate-800 dark:text-slate-100">
                        {{ $user->created_at?->format('d M Y') ?? '—' }}
                    </p>

                </div>

                <div class="border-b border-slate-100 dark:border-slate-800 p-5 sm:border-b-0 sm:border-r">

                    <p class="text-xs font-medium text-slate-400">
                        {{ $isAr ? 'آخر تحديث' : 'Last Updated' }}
                    </p>

                    <p class="mt-1 font-semibold text-slate-800 dark:text-slate-100">
                        {{ $user->updated_at?->format('d M Y') ?? '—' }}
                    </p>

                </div>

                <div class="p-5">

                    <p class="text-xs font-medium text-slate-400">
                        {{ $isAr ? 'Customer' : 'Customer' }}
                    </p>

                    <p class="mt-1 font-semibold text-slate-800 dark:text-slate-100">

                        @if ($user->customer)
                            {{ $user->customer->company_name ?? ($user->customer->name ?? 'Customer #' . $user->customer_id) }}
                        @else
                            <span class="text-amber-600">
                                {{ $isAr ? 'غير مرتبط' : 'Not Assigned' }}
                            </span>
                        @endif

                    </p>

                </div>

            </div>

        </div>


        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-300">{{ session('error') }}</div>
        @endif

        {{-- Access and sign-in security --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-col gap-3 border-b border-slate-100 p-5 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-black text-slate-900 dark:text-white">{{ $isAr ? 'الأمان والوصول' : 'Security & access' }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $isAr ? 'معلومات آخر تسجيل دخول وحالة الوصول إلى الحساب.' : 'Last sign-in details and account access status.' }}</p>
                </div>
                @if($user->blocked_at)
                    <span class="inline-flex w-fit items-center gap-2 rounded-full bg-rose-50 px-3 py-1.5 text-xs font-bold text-rose-700 dark:bg-rose-950/40 dark:text-rose-300"><span class="h-2 w-2 rounded-full bg-rose-500"></span>{{ $isAr ? 'الحساب محظور' : 'Account blocked' }}</span>
                @else
                    <span class="inline-flex w-fit items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300"><span class="h-2 w-2 rounded-full bg-emerald-500"></span>{{ $isAr ? 'الحساب نشط' : 'Account active' }}</span>
                @endif
            </div>
            <div class="grid gap-px bg-slate-100 dark:bg-slate-800 sm:grid-cols-2">
                <div class="bg-white p-5 dark:bg-slate-900">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ $isAr ? 'عنوان IP لآخر دخول ناجح' : 'Last successful login IP' }}</p>
                    <p class="mt-2 break-all font-mono text-base font-bold text-slate-900 dark:text-white">{{ $user->last_login_ip ?: '—' }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ $isAr ? 'يتم تسجيله عند تسجيل الدخول مستقبلاً.' : 'Captured on future successful sign-ins.' }}</p>
                </div>
                <div class="bg-white p-5 dark:bg-slate-900">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ $isAr ? 'تاريخ آخر دخول' : 'Last sign-in time' }}</p>
                    <p class="mt-2 text-base font-bold text-slate-900 dark:text-white">{{ $user->last_login_at?->format('d M Y, H:i') ?? '—' }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ $isAr ? 'حسب توقيت الخادم.' : 'Server timestamp.' }}</p>
                </div>
            </div>
            @if($user->blocked_at)
                <div class="border-t border-rose-100 bg-rose-50/60 p-5 dark:border-rose-900 dark:bg-rose-950/20">
                    <p class="text-xs font-bold uppercase tracking-wider text-rose-700 dark:text-rose-300">{{ $isAr ? 'سبب الحظر' : 'Block reason' }}</p>
                    <p class="mt-1 text-sm text-rose-800 dark:text-rose-200">{{ $user->blocked_reason ?: ($isAr ? 'لم يتم تحديد سبب.' : 'No reason provided.') }}</p>
                    <p class="mt-2 text-xs text-rose-600 dark:text-rose-400">{{ $isAr ? 'تاريخ الحظر:' : 'Blocked at:' }} {{ $user->blocked_at->format('d M Y, H:i') }}</p>
                    @if(auth()->id() !== $user->id)
                        <form method="POST" action="{{ route('admin.users.unblock', $user) }}" class="mt-4" onsubmit="return confirm('{{ $isAr ? 'هل تريد فك الحظر وإعادة تفعيل الحساب؟' : 'Restore access to this account?' }}')">
                            @csrf @method('PATCH')
                            <button class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-700" type="submit">{{ $isAr ? 'فك الحظر وإعادة تفعيل الحساب' : 'Unblock account' }}</button>
                        </form>
                    @endif
                </div>
            @elseif(auth()->id() !== $user->id && !$user->hasRole(['admin', 'super-admin']))
                <form method="POST" action="{{ route('admin.users.block', $user) }}" class="border-t border-slate-100 p-5 dark:border-slate-800" onsubmit="return confirm('{{ $isAr ? 'سيتم منع المستخدم من الدخول وإلغاء جلساته وأجهزته. هل تريد المتابعة؟' : 'This will deny sign-in and revoke active sessions and devices. Continue?' }}')">
                    @csrf @method('PATCH')
                    <label class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">{{ $isAr ? 'سبب الحظر (اختياري)' : 'Reason for blocking (optional)' }}</label>
                    <textarea name="blocked_reason" rows="2" maxlength="500" placeholder="{{ $isAr ? 'اكتب سبب إيقاف الحساب...' : 'Add a short reason for the suspension...' }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 outline-none focus:border-rose-400 focus:ring-4 focus:ring-rose-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
                    <button type="submit" class="mt-3 rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-rose-700">{{ $isAr ? 'حظر المستخدم' : 'Block user' }}</button>
                </form>
            @endif
        </section>

        {{-- Roles --}}
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">

            <div class="flex items-center justify-between">

                <div>

                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                        {{ $isAr ? 'الأدوار' : 'Roles' }}
                    </h2>

                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ $isAr ? 'الأدوار المرتبطة بهذا المستخدم.' : 'Roles assigned to this user.' }}
                    </p>

                </div>

                <span class="rounded-full bg-purple-50 px-3 py-1 text-xs font-semibold text-purple-700">

                    {{ $user->roles->count() }}

                </span>

            </div>


            <div class="mt-5 flex flex-wrap gap-2">

                @forelse ($user->roles as $role)
                    <div class="rounded-xl border border-purple-100 bg-purple-50 px-4 py-3">

                        <p class="text-sm font-bold text-purple-700">
                            {{ $role->display_name ?? $role->name }}
                        </p>

                        @if ($role->description)
                            <p class="mt-1 text-xs text-purple-600">
                                {{ $role->description }}
                            </p>
                        @endif

                    </div>

                @empty

                    <div class="rounded-xl bg-amber-50 px-4 py-3 text-sm font-medium text-amber-700">

                        {{ $isAr ? 'لا يوجد Role مرتبط.' : 'No role assigned.' }}

                    </div>
                @endforelse

            </div>

        </div>


        {{-- Direct Permissions --}}
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">

            <div class="flex items-center justify-between">

                <div>

                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                        {{ $isAr ? 'الصلاحيات المباشرة' : 'Direct Permissions' }}
                    </h2>

                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ $isAr ? 'الصلاحيات التي تم منحها للمستخدم مباشرة.' : 'Permissions directly assigned to this user.' }}
                    </p>

                </div>

                <span class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700">

                    {{ $user->permissions->count() }}

                </span>

            </div>


            <div class="mt-5 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">

                @forelse ($user->permissions as $permission)
                    <div class="rounded-xl border border-cyan-100 bg-cyan-50 px-4 py-3">

                        <p class="text-sm font-semibold text-cyan-800">
                            {{ $permission->display_name ?? $permission->name }}
                        </p>

                        @if ($permission->description)
                            <p class="mt-1 text-xs text-cyan-600">
                                {{ $permission->description }}
                            </p>
                        @endif

                    </div>

                @empty

                    <div class="rounded-xl bg-slate-50 dark:bg-slate-950 px-4 py-3 text-sm text-slate-500 dark:text-slate-400">

                        {{ $isAr ? 'لا توجد صلاحيات مباشرة.' : 'No direct permissions assigned.' }}

                    </div>
                @endforelse

            </div>

        </div>


        {{-- Contact --}}
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">

            <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                {{ $isAr ? 'معلومات الاتصال' : 'Contact Information' }}
            </h2>

            <div class="mt-5 grid gap-4 sm:grid-cols-2">

                <div class="rounded-xl bg-slate-50 dark:bg-slate-950 p-4">

                    <p class="text-xs font-medium text-slate-400">
                        {{ $isAr ? 'البريد الإلكتروني' : 'Email' }}
                    </p>

                    @if ($user->email)
                        <a href="mailto:{{ $user->email }}"
                            class="mt-1 block text-sm font-semibold text-cyan-600 hover:text-cyan-700">

                            {{ $user->email }}

                        </a>
                    @else
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            —
                        </p>
                    @endif

                </div>


                @if (isset($user->phone))
                    <div class="rounded-xl bg-slate-50 dark:bg-slate-950 p-4">

                        <p class="text-xs font-medium text-slate-400">
                            {{ $isAr ? 'الهاتف' : 'Phone' }}
                        </p>

                        <p class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-100">
                            {{ $user->phone ?: '—' }}
                        </p>

                    </div>
                @endif

            </div>

        </div>


        {{-- Bottom Actions --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">

            <a href="{{ route('admin.users.index') }}"
                class="inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-5 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50">

                {{ $isAr ? 'العودة للمستخدمين' : 'Back to Users' }}

            </a>

            <a href="{{ route('admin.users.edit', $user) }}"
                class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">

                {{ $isAr ? 'تعديل المستخدم' : 'Edit User' }}

            </a>

        </div>

    </div>

</x-admin-layout>
