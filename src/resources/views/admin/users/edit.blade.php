<x-admin-layout>

    @php
        $isAr = app()->getLocale() === 'ar';

        $userRoleIds = $user->roles->pluck('id')->toArray();
        $userPermissionIds = $user->permissions->pluck('id')->toArray();
    @endphp

    <div class="mx-auto max-w-5xl space-y-6" dir="{{ $isAr ? 'rtl' : 'ltr' }}">

        {{-- Header --}}
        <div>

            <a href="{{ route('admin.users.show', $user) }}"
                class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 dark:text-slate-400 hover:text-cyan-600">

                ← {{ $isAr ? 'العودة إلى المستخدم' : 'Back to User' }}

            </a>

            <div class="mt-5 flex items-center gap-4">

                <div
                    class="flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-50 text-lg font-bold text-cyan-700">

                    {{ strtoupper(substr($user->name ?: 'U', 0, 1)) }}

                </div>

                <div>

                    <p class="text-sm font-medium text-cyan-600">
                        HAG Brokers
                    </p>

                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                        {{ $isAr ? 'تعديل المستخدم' : 'Edit User' }}
                    </h1>

                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ $user->name }} · {{ $user->email }}
                    </p>

                </div>

            </div>

        </div>


        {{-- Errors --}}
        @if ($errors->any())

            <div class="rounded-2xl border border-red-200 bg-red-50 p-4">

                <ul class="list-disc space-y-1 ps-5 text-sm text-red-600">

                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach

                </ul>

            </div>

        @endif


        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">

            @csrf
            @method('PUT')


            {{-- Account --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">

                <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                    {{ $isAr ? 'معلومات الحساب' : 'Account Information' }}
                </h2>

                <div class="mt-5 grid gap-5 sm:grid-cols-2">

                    <div>

                        <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            {{ $isAr ? 'الاسم' : 'Name' }}
                        </label>

                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                            class="mt-2 block w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-2.5 text-sm outline-none focus:border-cyan-500 focus:bg-white focus:ring-2 focus:ring-cyan-100">

                    </div>


                    <div>

                        <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            {{ $isAr ? 'البريد الإلكتروني' : 'Email' }}
                        </label>

                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                            class="mt-2 block w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-2.5 text-sm outline-none focus:border-cyan-500 focus:bg-white focus:ring-2 focus:ring-cyan-100">

                    </div>


                    <div>

                        <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            {{ $isAr ? 'كلمة مرور جديدة' : 'New Password' }}
                        </label>

                        <input type="password" name="password"
                            class="mt-2 block w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-2.5 text-sm outline-none focus:border-cyan-500 focus:bg-white focus:ring-2 focus:ring-cyan-100">

                        <p class="mt-1 text-xs text-slate-400">
                            {{ $isAr ? 'اتركها فارغة للإبقاء على كلمة المرور الحالية.' : 'Leave empty to keep the current password.' }}
                        </p>

                    </div>


                    <div>

                        <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            {{ $isAr ? 'تأكيد كلمة المرور' : 'Confirm Password' }}
                        </label>

                        <input type="password" name="password_confirmation"
                            class="mt-2 block w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-2.5 text-sm outline-none focus:border-cyan-500 focus:bg-white focus:ring-2 focus:ring-cyan-100">

                    </div>


                    <div class="sm:col-span-2">

                        <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            {{ $isAr ? 'العميل المرتبط' : 'Customer Assignment' }}
                        </label>

                        <select name="customer_id"
                            class="mt-2 block w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-2.5 text-sm outline-none focus:border-cyan-500 focus:bg-white focus:ring-2 focus:ring-cyan-100">

                            <option value="">
                                {{ $isAr ? 'بدون عميل' : 'No Customer' }}
                            </option>

                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}" @selected(old('customer_id', $user->customer_id) == $customer->id)>

                                    {{ $customer->company_name ?? ($customer->name ?? 'Customer #' . $customer->id) }}

                                </option>
                            @endforeach

                        </select>

                    </div>

                </div>

            </div>


            {{-- Roles --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">

                <div class="flex items-center justify-between gap-3">

                    <div>

                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                            {{ $isAr ? 'Roles' : 'Roles' }}
                        </h2>

                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            {{ $isAr ? 'حدد الأدوار التي يمتلكها المستخدم.' : 'Select the roles assigned to this user.' }}
                        </p>

                    </div>

                    <span class="rounded-full bg-purple-50 px-3 py-1 text-xs font-semibold text-purple-700">
                        Laratrust
                    </span>

                </div>


                <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">

                    @foreach ($roles as $role)
                        <label
                            class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 dark:border-slate-800 p-4 transition hover:border-purple-300 hover:bg-purple-50">

                            <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                @checked(in_array($role->id, old('roles', $userRoleIds)))
                                class="mt-1 h-4 w-4 rounded border-slate-300 text-purple-600 focus:ring-purple-500">

                            <span>

                                <span class="block text-sm font-semibold text-slate-800 dark:text-slate-100">
                                    {{ $role->display_name ?? $role->name }}
                                </span>

                                @if ($role->description)
                                    <span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">
                                        {{ $role->description }}
                                    </span>
                                @endif

                            </span>

                        </label>
                    @endforeach

                </div>

            </div>


            {{-- Permissions --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">

                <div>

                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                        {{ $isAr ? 'الصلاحيات المباشرة' : 'Direct Permissions' }}
                    </h2>

                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ $isAr
                            ? 'حدد الصلاحيات التي تريد منحها للمستخدم مباشرة.'
                            : 'Select permissions that should be assigned directly to this user.' }}
                    </p>

                </div>


                <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">

                    @foreach ($permissions as $permission)
                        <label
                            class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 dark:border-slate-800 p-4 transition hover:border-cyan-300 hover:bg-cyan-50">

                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                @checked(in_array($permission->id, old('permissions', $userPermissionIds)))
                                class="mt-1 h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">

                            <span class="text-sm font-medium text-slate-800 dark:text-slate-100">
                                {{ $permission->display_name ?? $permission->name }}
                            </span>

                        </label>
                    @endforeach

                </div>

            </div>


            {{-- Actions --}}
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">

                <div>

                    @if (auth()->id() !== $user->id)
                        <button type="button" onclick="document.getElementById('delete-user-form').submit()"
                            class="inline-flex items-center justify-center rounded-xl bg-red-50 px-5 py-2.5 text-sm font-semibold text-red-700 hover:bg-red-100">

                            {{ $isAr ? 'حذف المستخدم' : 'Delete User' }}

                        </button>
                    @endif

                </div>


                <div class="flex flex-col gap-3 sm:flex-row">

                    <a href="{{ route('admin.users.show', $user) }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-5 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50">

                        {{ $isAr ? 'إلغاء' : 'Cancel' }}

                    </a>

                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-6 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">

                        {{ $isAr ? 'حفظ التغييرات' : 'Save Changes' }}

                    </button>

                </div>

            </div>

        </form>


        {{-- Delete Form --}}
        @if (auth()->id() !== $user->id)
            <form id="delete-user-form" method="POST" action="{{ route('admin.users.destroy', $user) }}"
                onsubmit="return confirm('{{ $isAr ? 'هل أنت متأكد من حذف هذا المستخدم؟ لا يمكن التراجع عن هذه العملية.' : 'Are you sure you want to delete this user? This action cannot be undone.' }}')">

                @csrf
                @method('DELETE')

            </form>
        @endif

    </div>

</x-admin-layout>
