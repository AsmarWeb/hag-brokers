<x-admin-layout>

    @php
        $isAr = app()->getLocale() === 'ar';
    @endphp

    <div class="mx-auto max-w-5xl space-y-6" dir="{{ $isAr ? 'rtl' : 'ltr' }}">

        {{-- Header --}}
        <div>

            <a href="{{ route('admin.users.index') }}"
                class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-blue-600">

                ← {{ $isAr ? 'العودة للمستخدمين' : 'Back to Users' }}

            </a>

            <p class="mt-5 text-sm font-medium text-blue-600">
                HAG Brokers
            </p>

            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                {{ $isAr ? 'إضافة مستخدم' : 'Create User' }}
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                {{ $isAr
                    ? 'إنشاء حساب جديد وتحديد الأدوار والصلاحيات.'
                    : 'Create a new account and assign roles and permissions.' }}
            </p>

        </div>


        {{-- Validation Errors --}}
        @if ($errors->any())

            <div class="rounded-2xl border border-red-200 bg-red-50 p-4">

                <p class="font-semibold text-red-700">
                    {{ $isAr ? 'يرجى تصحيح الأخطاء التالية:' : 'Please correct the following errors:' }}
                </p>

                <ul class="mt-2 list-disc space-y-1 ps-5 text-sm text-red-600">

                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach

                </ul>

            </div>

        @endif


        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-6">

            @csrf


            {{-- Basic Information --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

                <h2 class="text-lg font-bold text-slate-900">
                    {{ $isAr ? 'معلومات الحساب' : 'Account Information' }}
                </h2>

                <div class="mt-5 grid gap-5 sm:grid-cols-2">

                    {{-- Name --}}
                    <div>

                        <label class="text-sm font-semibold text-slate-700">
                            {{ $isAr ? 'الاسم' : 'Name' }}
                        </label>

                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100">

                    </div>


                    {{-- Email --}}
                    <div>

                        <label class="text-sm font-semibold text-slate-700">
                            {{ $isAr ? 'البريد الإلكتروني' : 'Email' }}
                        </label>

                        <input type="email" name="email" value="{{ old('email') }}" required
                            class="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100">

                    </div>


                    {{-- Password --}}
                    <div>

                        <label class="text-sm font-semibold text-slate-700">
                            {{ $isAr ? 'كلمة المرور' : 'Password' }}
                        </label>

                        <input type="password" name="password" required
                            class="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100">

                    </div>


                    {{-- Password Confirmation --}}
                    <div>

                        <label class="text-sm font-semibold text-slate-700">
                            {{ $isAr ? 'تأكيد كلمة المرور' : 'Confirm Password' }}
                        </label>

                        <input type="password" name="password_confirmation" required
                            class="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100">

                    </div>


                    {{-- Customer --}}
                    <div class="sm:col-span-2">

                        <label class="text-sm font-semibold text-slate-700">
                            {{ $isAr ? 'العميل المرتبط' : 'Customer Assignment' }}
                        </label>

                        <select name="customer_id"
                            class="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100">

                            <option value="">
                                {{ $isAr ? 'بدون عميل' : 'No Customer' }}
                            </option>

                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>

                                    {{ $customer->company_name ?? ($customer->name ?? 'Customer #' . $customer->id) }}

                                </option>
                            @endforeach

                        </select>

                    </div>

                </div>

            </div>


            {{-- Roles --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

                <h2 class="text-lg font-bold text-slate-900">
                    {{ $isAr ? 'Roles' : 'Roles' }}
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    {{ $isAr ? 'اختر Role أو أكثر لهذا المستخدم.' : 'Assign one or more roles to this user.' }}
                </p>

                <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">

                    @forelse ($roles as $role)
                        <label
                            class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 transition hover:border-purple-300 hover:bg-purple-50">

                            <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                @checked(in_array($role->id, old('roles', [])))
                                class="mt-1 h-4 w-4 rounded border-slate-300 text-purple-600 focus:ring-purple-500">

                            <span>

                                <span class="block text-sm font-semibold text-slate-800">
                                    {{ $role->display_name ?? $role->name }}
                                </span>

                                @if ($role->description)
                                    <span class="mt-1 block text-xs text-slate-500">
                                        {{ $role->description }}
                                    </span>
                                @endif

                            </span>

                        </label>

                    @empty

                        <p class="text-sm text-slate-500">
                            {{ $isAr ? 'لا توجد Roles.' : 'No roles available.' }}
                        </p>
                    @endforelse

                </div>

            </div>


            {{-- Permissions --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

                <h2 class="text-lg font-bold text-slate-900">
                    {{ $isAr ? 'الصلاحيات المباشرة' : 'Direct Permissions' }}
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    {{ $isAr
                        ? 'هذه الصلاحيات تضاف مباشرة إلى المستخدم بالإضافة إلى صلاحيات الـ Roles.'
                        : 'These permissions are assigned directly in addition to role permissions.' }}
                </p>

                <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">

                    @forelse ($permissions as $permission)
                        <label
                            class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 transition hover:border-cyan-300 hover:bg-cyan-50">

                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                @checked(in_array($permission->id, old('permissions', [])))
                                class="mt-1 h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">

                            <span class="text-sm font-medium text-slate-800">
                                {{ $permission->display_name ?? $permission->name }}
                            </span>

                        </label>

                    @empty

                        <p class="text-sm text-slate-500">
                            {{ $isAr ? 'لا توجد Permissions.' : 'No permissions available.' }}
                        </p>
                    @endforelse

                </div>

            </div>


            {{-- Actions --}}
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                <a href="{{ route('admin.users.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">

                    {{ $isAr ? 'إلغاء' : 'Cancel' }}

                </a>

                <button type="submit"
                    class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-6 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">

                    {{ $isAr ? 'إنشاء المستخدم' : 'Create User' }}

                </button>

            </div>

        </form>

    </div>

</x-admin-layout>
