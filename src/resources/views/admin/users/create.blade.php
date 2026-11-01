<x-admin-layout>

    @php
        $isAr = app()->getLocale() === 'ar';
    @endphp

    <div class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:px-8" dir="{{ $isAr ? 'rtl' : 'ltr' }}">

        {{-- Header --}}
        <div>

            <a href="{{ route('admin.users.index') }}"
                class="inline-flex items-center gap-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-cyan-600 dark:hover:text-cyan-300">

                ← {{ $isAr ? 'العودة للمستخدمين' : 'Back to Users' }}

            </a>

            <p class="mt-5 text-sm font-medium text-cyan-700 dark:text-cyan-400">
                HAG Brokers
            </p>

            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                {{ $isAr ? 'إضافة مستخدم' : 'Create User' }}
            </h1>

            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                {{ $isAr
                    ? 'أضف حساباً لفريق العمل أو لأحد العملاء، ثم حدد الوصول المناسب لمهامه.'
                    : 'Create a team or customer account, then assign access appropriate to their work.' }}
            </p>

        </div>



        {{-- HAG user onboarding guide --}}
        <aside aria-labelledby="user-guide-title"
            class="overflow-hidden rounded-2xl border border-cyan-200 bg-cyan-50 dark:border-cyan-500/20 dark:bg-cyan-500/5">
            <div class="border-b border-cyan-200/70 px-5 py-4 dark:border-cyan-500/20">
                <p class="text-xs font-bold uppercase tracking-wider text-cyan-700 dark:text-cyan-400">
                    HAG Brokers · {{ $isAr ? 'دليل فريق العمل' : 'Team Guide' }}
                </p>
                <h2 id="user-guide-title" class="mt-1 text-lg font-bold text-slate-900 dark:text-white">
                    {{ $isAr ? 'قبل إنشاء الحساب' : 'Before creating the account' }}
                </h2>
            </div>
            <ol class="grid gap-5 p-5 md:grid-cols-3">
                <li>
                    <p class="font-bold text-slate-900 dark:text-white">
                        <span class="text-cyan-700 dark:text-cyan-400">01</span>
                        {{ $isAr ? 'حدد صاحب الحساب' : 'Identify the account owner' }}
                    </p>
                    <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                        {{ $isAr
                            ? 'استخدم اسم الشخص وبريده الصحيح. اربط الحساب بالعميل إذا كان يمثل شركة عميلة، واترك الربط فارغاً للحسابات الداخلية التي لا تتبع عميلاً.'
                            : 'Use the person’s name and correct email. Link a customer account to its customer record; leave internal accounts unassigned when appropriate.' }}
                    </p>
                </li>
                <li>
                    <p class="font-bold text-slate-900 dark:text-white">
                        <span class="text-cyan-700 dark:text-cyan-400">02</span>
                        {{ $isAr ? 'اختر الدور بعناية' : 'Choose the role carefully' }}
                    </p>
                    <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                        {{ $isAr
                            ? 'امنح المستخدم الوصول اللازم لمهامه فقط. أدوار admin وsuper-admin تمنح وصولاً إدارياً واسعاً؛ خصصها للمسؤولين المعتمدين.'
                            : 'Assign access needed for the person’s work. The admin and super-admin roles provide broad administrative access; reserve them for approved administrators.' }}
                    </p>
                </li>
                <li>
                    <p class="font-bold text-slate-900 dark:text-white">
                        <span class="text-cyan-700 dark:text-cyan-400">03</span>
                        {{ $isAr ? 'راجع ثم أنشئ' : 'Review, then create' }}
                    </p>
                    <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                        {{ $isAr
                            ? 'تأكد من البريد والعميل والأدوار. أضف صلاحيات مباشرة فقط عند الحاجة إلى وصول إضافي لا يوفره الدور المختار.'
                            : 'Check the email, customer and roles. Add direct permissions only when extra access beyond the selected roles is needed.' }}
                    </p>
                </li>
            </ol>
        </aside>

        {{-- Validation Errors --}}
        @if ($errors->any())

            <div class="rounded-2xl border border-red-200 dark:border-red-500/30 bg-red-50 dark:bg-red-500/10 p-4">

                <p class="font-semibold text-red-700 dark:text-red-300">
                    {{ $isAr ? 'يرجى تصحيح الأخطاء التالية:' : 'Please correct the following errors:' }}
                </p>

                <ul class="mt-2 list-disc space-y-1 ps-5 text-sm text-red-600 dark:text-red-300">

                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach

                </ul>

            </div>

        @endif


        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-6">

            @csrf


            {{-- Basic Information --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">

                <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                    {{ $isAr ? 'معلومات الحساب' : 'Account Information' }}
                </h2>

                <div class="mt-5 grid gap-5 sm:grid-cols-2">

                    {{-- Name --}}
                    <div>

                        <label for="new-user-name" class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            {{ $isAr ? 'الاسم' : 'Name' }}
                        </label>

                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="mt-2 block w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-2.5 text-sm outline-none focus:border-cyan-500 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-cyan-500/20" id="new-user-name" aria-describedby="help-name" autocomplete="off">
<p id="help-name" class="mt-2 text-xs leading-5 text-slate-600 dark:text-slate-400">{{ $isAr ? 'الاسم الكامل للشخص الذي سيستخدم الحساب.' : 'The full name of the person who will use this account.' }}</p>

                    </div>


                    {{-- Email --}}
                    <div>

                        <label for="new-user-email" class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            {{ $isAr ? 'البريد الإلكتروني' : 'Email' }}
                        </label>

                        <input type="email" name="email" value="{{ old('email') }}" required
                            class="mt-2 block w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-2.5 text-sm outline-none focus:border-cyan-500 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-cyan-500/20" id="new-user-email" aria-describedby="help-email" autocomplete="off" dir="ltr">
<p id="help-email" class="mt-2 text-xs leading-5 text-slate-600 dark:text-slate-400">{{ $isAr ? 'بريد فريد لتسجيل الدخول. تأكد من كتابته بصورة صحيحة.' : 'A unique sign-in email. Check that it is entered correctly.' }}</p>

                    </div>


                    {{-- Password --}}
                    <div>

                        <label for="new-user-password" class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            {{ $isAr ? 'كلمة المرور' : 'Password' }}
                        </label>

                        <input type="password" name="password" required
                            class="mt-2 block w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-2.5 text-sm outline-none focus:border-cyan-500 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-cyan-500/20" id="new-user-password" aria-describedby="help-password" minlength="8" autocomplete="new-password">
<p id="help-password" class="mt-2 text-xs leading-5 text-slate-600 dark:text-slate-400">{{ $isAr ? '8 أحرف على الأقل. شارك بيانات الدخول مع صاحب الحساب عبر قناة خاصة.' : 'At least 8 characters. Share credentials privately with the account owner.' }}</p>

                    </div>


                    {{-- Password Confirmation --}}
                    <div>

                        <label for="new-user-password_confirmation" class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            {{ $isAr ? 'تأكيد كلمة المرور' : 'Confirm Password' }}
                        </label>

                        <input type="password" name="password_confirmation" required
                            class="mt-2 block w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-2.5 text-sm outline-none focus:border-cyan-500 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-cyan-500/20" id="new-user-password_confirmation" aria-describedby="help-password_confirmation" minlength="8" autocomplete="new-password">
<p id="help-password_confirmation" class="mt-2 text-xs leading-5 text-slate-600 dark:text-slate-400">{{ $isAr ? 'أعد كتابة كلمة المرور نفسها.' : 'Enter the same password again.' }}</p>

                    </div>


                    {{-- Customer --}}
                    <div class="sm:col-span-2">

                        <label for="new-user-customer_id" class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            {{ $isAr ? 'العميل المرتبط' : 'Customer Assignment' }}
                        </label>

                        <select name="customer_id"
                            class="mt-2 block w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-2.5 text-sm outline-none focus:border-cyan-500 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-cyan-500/20" id="new-user-customer_id" aria-describedby="help-customer_id">

                            <option value="">
                                {{ $isAr ? 'بدون عميل' : 'No Customer' }}
                            </option>

                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>

                                    {{ $customer->company_name ?? ($customer->name ?? 'Customer #' . $customer->id) }}

                                </option>
                            @endforeach

                        </select>
<p id="help-customer_id" class="mt-2 text-xs leading-5 text-slate-600 dark:text-slate-400">{{ $isAr ? 'اختر سجل العميل الموجود. هذا الربط لا ينشئ عميلاً جديداً ولا يمنح دوراً تلقائياً.' : 'Select an existing customer. This link neither creates a customer nor assigns a role automatically.' }}</p>

                    </div>

                </div>

            </div>


            {{-- Roles --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">

                <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                    {{ $isAr ? 'الأدوار الوظيفية' : 'Account Roles' }}
                </h2>

                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                    {{ $isAr ? 'اختر الدور المناسب للعمل المطلوب. الأدوار تحدد مجموعة الصلاحيات الأساسية.' : 'Choose a role appropriate to the work. Roles provide the baseline permissions.' }}
                </p>

                <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">

                    @forelse ($roles as $role)
                        <label
                            class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 dark:border-slate-800 p-4 transition hover:border-cyan-400 dark:hover:border-cyan-500 hover:bg-cyan-50 dark:hover:bg-cyan-500/10">

                            <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                @checked(in_array($role->id, old('roles', [])))
                                class="mt-1 h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">

                            <span>

                                <span class="block text-sm font-semibold text-slate-800 dark:text-slate-100">
                                    {{ $role->display_name ?? $role->name }}
                                </span>

                                @if ($role->description)
                                    <span class="mt-1 block text-xs text-slate-600 dark:text-slate-400">
                                        {{ $role->description }}
                                    </span>
                                @endif

                            </span>

                        </label>

                    @empty

                        <p class="text-sm text-slate-600 dark:text-slate-400">
                            {{ $isAr ? 'لا توجد أدوار متاحة. راجع مسؤول المنصة قبل إنشاء الحساب.' : 'No roles are available. Contact the platform administrator before creating this account.' }}
                        </p>
                    @endforelse

                </div>

            </div>



        <details class="group rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900"
            @if (count((array) old('permissions', [])) || $errors->has('permissions') || $errors->has('permissions.*')) open @endif>
            <summary class="cursor-pointer rounded-2xl px-6 py-5 font-bold text-slate-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-cyan-500 dark:text-white">
                {{ $isAr ? 'صلاحيات إضافية · اختياري' : 'Additional Permissions · Optional' }}
                <span class="mt-1 block text-sm font-normal text-slate-600 dark:text-slate-400">
                    {{ $isAr ? 'افتح هذا القسم لإضافة صلاحيات مباشرة عند الحاجة.' : 'Expand to assign direct permissions when needed.' }}
                </span>
            </summary>
        {{-- Permissions --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">

                <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                    {{ $isAr ? 'الصلاحيات المباشرة' : 'Direct Permissions' }}
                </h2>

                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                    {{ $isAr
                        ? 'هذه الصلاحيات تضاف مباشرة إلى المستخدم بالإضافة إلى صلاحيات الـ Roles.'
                        : 'These permissions are assigned directly in addition to role permissions.' }}
                </p>

                <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">

                    @forelse ($permissions as $permission)
                        <label
                            class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 dark:border-slate-800 p-4 transition hover:border-cyan-300 hover:bg-cyan-50 dark:hover:bg-cyan-500/10">

                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                @checked(in_array($permission->id, old('permissions', [])))
                                class="mt-1 h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">

                            <span class="text-sm font-medium text-slate-800 dark:text-slate-100">
                                {{ $permission->display_name ?? $permission->name }}
                            </span>

                        </label>

                    @empty

                        <p class="text-sm text-slate-600 dark:text-slate-400">
                            {{ $isAr ? 'لا توجد صلاحيات مباشرة متاحة.' : 'No permissions available.' }}
                        </p>
                    @endforelse

                </div>

            </div>


            </details>

        {{-- Actions --}}
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                <a href="{{ route('admin.users.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-5 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">

                    {{ $isAr ? 'إلغاء' : 'Cancel' }}

                </a>

                <button type="submit"
                    class="inline-flex items-center justify-center rounded-xl bg-cyan-400 px-6 py-2.5 text-sm font-semibold text-slate-950 hover:bg-cyan-300">

                    {{ $isAr ? 'إنشاء المستخدم' : 'Create User' }}

                </button>

            </div>

        </form>

    </div>

</x-admin-layout>
