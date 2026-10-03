<x-admin-layout>

    @php
        $isAr = app()->getLocale() === 'ar';
    @endphp

    <div class="mx-auto max-w-5xl space-y-6" dir="{{ $isAr ? 'rtl' : 'ltr' }}">

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>

                <a href="{{ route('admin.users.index') }}"
                    class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-blue-600">

                    ← {{ $isAr ? 'العودة للمستخدمين' : 'Back to Users' }}

                </a>

            </div>

            <div class="flex gap-2">

                <a href="{{ route('admin.users.edit', $user) }}"
                    class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">

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
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div class="p-6">

                <div class="flex flex-col gap-5 sm:flex-row sm:items-center">

                    <div
                        class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-2xl font-bold text-blue-700">

                        {{ strtoupper(substr($user->name ?: 'U', 0, 1)) }}

                    </div>

                    <div class="min-w-0">

                        <p class="text-sm font-medium text-blue-600">
                            HAG Brokers
                        </p>

                        <h1 class="mt-1 text-2xl font-bold text-slate-900">
                            {{ $user->name ?: 'Unnamed User' }}
                        </h1>

                        <p class="mt-1 text-sm text-slate-500">
                            {{ $user->email }}
                        </p>

                        <p class="mt-2 text-xs text-slate-400">
                            User #{{ $user->id }}
                        </p>

                    </div>

                </div>

            </div>


            {{-- Basic Info --}}
            <div class="grid border-t border-slate-100 sm:grid-cols-3">

                <div class="border-b border-slate-100 p-5 sm:border-b-0 sm:border-r">

                    <p class="text-xs font-medium text-slate-400">
                        {{ $isAr ? 'تاريخ التسجيل' : 'Joined' }}
                    </p>

                    <p class="mt-1 font-semibold text-slate-800">
                        {{ $user->created_at?->format('d M Y') ?? '—' }}
                    </p>

                </div>

                <div class="border-b border-slate-100 p-5 sm:border-b-0 sm:border-r">

                    <p class="text-xs font-medium text-slate-400">
                        {{ $isAr ? 'آخر تحديث' : 'Last Updated' }}
                    </p>

                    <p class="mt-1 font-semibold text-slate-800">
                        {{ $user->updated_at?->format('d M Y') ?? '—' }}
                    </p>

                </div>

                <div class="p-5">

                    <p class="text-xs font-medium text-slate-400">
                        {{ $isAr ? 'Customer' : 'Customer' }}
                    </p>

                    <p class="mt-1 font-semibold text-slate-800">

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


        {{-- Roles --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <div class="flex items-center justify-between">

                <div>

                    <h2 class="text-lg font-bold text-slate-900">
                        {{ $isAr ? 'الأدوار' : 'Roles' }}
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
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
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <div class="flex items-center justify-between">

                <div>

                    <h2 class="text-lg font-bold text-slate-900">
                        {{ $isAr ? 'الصلاحيات المباشرة' : 'Direct Permissions' }}
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
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

                    <div class="rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-500">

                        {{ $isAr ? 'لا توجد صلاحيات مباشرة.' : 'No direct permissions assigned.' }}

                    </div>
                @endforelse

            </div>

        </div>


        {{-- Contact --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <h2 class="text-lg font-bold text-slate-900">
                {{ $isAr ? 'معلومات الاتصال' : 'Contact Information' }}
            </h2>

            <div class="mt-5 grid gap-4 sm:grid-cols-2">

                <div class="rounded-xl bg-slate-50 p-4">

                    <p class="text-xs font-medium text-slate-400">
                        {{ $isAr ? 'البريد الإلكتروني' : 'Email' }}
                    </p>

                    @if ($user->email)
                        <a href="mailto:{{ $user->email }}"
                            class="mt-1 block text-sm font-semibold text-blue-600 hover:text-blue-700">

                            {{ $user->email }}

                        </a>
                    @else
                        <p class="mt-1 text-sm text-slate-500">
                            —
                        </p>
                    @endif

                </div>


                @if (isset($user->phone))
                    <div class="rounded-xl bg-slate-50 p-4">

                        <p class="text-xs font-medium text-slate-400">
                            {{ $isAr ? 'الهاتف' : 'Phone' }}
                        </p>

                        <p class="mt-1 text-sm font-semibold text-slate-800">
                            {{ $user->phone ?: '—' }}
                        </p>

                    </div>
                @endif

            </div>

        </div>


        {{-- Bottom Actions --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">

            <a href="{{ route('admin.users.index') }}"
                class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">

                {{ $isAr ? 'العودة للمستخدمين' : 'Back to Users' }}

            </a>

            <a href="{{ route('admin.users.edit', $user) }}"
                class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">

                {{ $isAr ? 'تعديل المستخدم' : 'Edit User' }}

            </a>

        </div>

    </div>

</x-admin-layout>
