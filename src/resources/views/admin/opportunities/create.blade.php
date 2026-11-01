{{-- resources/views/admin/opportunities/create.blade.php --}}

<x-admin-layout>

    @php
        $isAr = app()->getLocale() === 'ar';

        $t = [
            'title' => $isAr ? 'إضافة فرصة جديدة' : 'Create Opportunity',
            'subtitle' => $isAr
                ? 'أضف فرصة تجارية أو استثمارية جديدة إلى نظام HAG Brokers.'
                : 'Add a new business or investment opportunity to HAG Brokers.',

            'back' => $isAr ? 'العودة للفرص' : 'Back to Opportunities',
            'save' => $isAr ? 'حفظ الفرصة' : 'Save Opportunity',
            'select' => $isAr ? 'اختر' : 'Select',

            // CRM
            'customer' => $isAr ? 'العميل' : 'Customer',
            'contact' => $isAr ? 'جهة الاتصال' : 'Contact',
            'assigned_to' => $isAr ? 'المسؤول' : 'Assigned To',

            // Opportunity
            'title_field' => $isAr ? 'اسم الفرصة' : 'Opportunity Title',
            'type' => $isAr ? 'نوع الفرصة' : 'Opportunity Type',
            'category' => $isAr ? 'التصنيف' : 'Category',
            'description' => $isAr ? 'وصف الفرصة' : 'Opportunity Description',

            // Freight
            'cargo_type' => $isAr ? 'نوع الشحنة' : 'Cargo Type',
            'container_type' => $isAr ? 'نوع الحاوية' : 'Container Type',
            'origin' => $isAr ? 'ميناء المغادرة' : 'Origin Port',
            'destination' => $isAr ? 'ميناء الوصول' : 'Destination Port',

            // Location
            'country' => $isAr ? 'الدولة' : 'Country',
            'city' => $isAr ? 'المدينة' : 'City',

            // Finance
            'estimated_value' => $isAr ? 'القيمة المتوقعة' : 'Estimated Value',
            'required_capital' => $isAr ? 'رأس المال المطلوب' : 'Required Capital',
            'min_investment' => $isAr ? 'الحد الأدنى للاستثمار' : 'Minimum Investment',
            'currency' => $isAr ? 'العملة' : 'Currency',

            // Commission
            'commission_type' => $isAr ? 'نوع العمولة' : 'Commission Type',
            'commission_rate' => $isAr ? 'نسبة / قيمة العمولة' : 'Commission Rate / Amount',

            // CRM
            'stage' => $isAr ? 'المرحلة' : 'Stage',
            'probability' => $isAr ? 'احتمالية الإغلاق' : 'Probability',
            'expected_close' => $isAr ? 'تاريخ الإغلاق المتوقع' : 'Expected Close Date',

            // Marketplace
            'visibility' => $isAr ? 'ظهور الفرصة' : 'Visibility',
            'verification_status' => $isAr ? 'حالة التحقق' : 'Verification Status',
            'published_at' => $isAr ? 'تاريخ النشر' : 'Published At',
            'expires_at' => $isAr ? 'تاريخ انتهاء الفرصة' : 'Expiration Date',

            // Other
            'notes' => $isAr ? 'ملاحظات' : 'Notes',
        ];

        $stages = [
            'new' => $isAr ? 'جديدة' : 'New',
            'qualified' => $isAr ? 'مؤهلة' : 'Qualified',
            'quotation' => $isAr ? 'عرض سعر' : 'Quotation',
            'negotiation' => $isAr ? 'تفاوض' : 'Negotiation',
            'won' => $isAr ? 'تم الفوز' : 'Won',
            'lost' => $isAr ? 'خاسرة' : 'Lost',
        ];

        $opportunityTypes = [
            'business' => $isAr ? 'أعمال' : 'Business',
            'investment' => $isAr ? 'استثمار' : 'Investment',
            'trade' => $isAr ? 'تجارة' : 'Trade',
            'procurement' => $isAr ? 'مشتريات' : 'Procurement',
            'distribution' => $isAr ? 'توزيع' : 'Distribution',
            'logistics' => $isAr ? 'لوجستيات' : 'Logistics',
            'partnership' => $isAr ? 'شراكة' : 'Partnership',
            'other' => $isAr ? 'أخرى' : 'Other',
        ];

        $categories = [
            'import' => $isAr ? 'استيراد' : 'Import',
            'export' => $isAr ? 'تصدير' : 'Export',
            'sea_freight' => $isAr ? 'شحن بحري' : 'Sea Freight',
            'distribution' => $isAr ? 'توزيع' : 'Distribution',
            'manufacturing' => $isAr ? 'تصنيع' : 'Manufacturing',
            'real_estate' => $isAr ? 'عقارات' : 'Real Estate',
            'technology' => $isAr ? 'تقنية' : 'Technology',
            'services' => $isAr ? 'خدمات' : 'Services',
            'other' => $isAr ? 'أخرى' : 'Other',
        ];

        $commissionTypes = [
            'percentage' => $isAr ? 'نسبة مئوية' : 'Percentage',
            'fixed' => $isAr ? 'مبلغ ثابت' : 'Fixed Amount',
        ];

        $visibilityOptions = [
            'private' => $isAr ? 'خاص' : 'Private',
            'public' => $isAr ? 'عام - يظهر في Marketplace' : 'Public - Marketplace',
            'invite_only' => $isAr ? 'بدعوة فقط' : 'Invite Only',
        ];

        $verificationStatuses = [
            'pending' => $isAr ? 'قيد الانتظار' : 'Pending',
            'under_review' => $isAr ? 'قيد المراجعة' : 'Under Review',
            'verified' => $isAr ? 'تم التحقق' : 'Verified',
            'rejected' => $isAr ? 'مرفوضة' : 'Rejected',
        ];
    @endphp


    <div class="space-y-6">

        {{-- =====================================================
             Header
        ====================================================== --}}

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>

                <h1 class="text-2xl font-bold text-white">
                    {{ $t['title'] }}
                </h1>

                <p class="mt-1 text-sm text-slate-400">
                    {{ $t['subtitle'] }}
                </p>

            </div>

            <a href="{{ route('admin.opportunities.index') }}"
                class="inline-flex items-center justify-center rounded-xl border border-white/10 bg-slate-800 px-5 py-3 text-sm font-semibold text-slate-300 transition hover:bg-slate-700">
                {{ $t['back'] }}
            </a>

        </div>


        {{-- =====================================================
             Validation Errors
        ====================================================== --}}

        @if ($errors->any())

            <div class="rounded-xl border border-red-400/20 bg-red-400/10 p-4">

                <ul class="space-y-1 text-sm text-red-300">

                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach

                </ul>

            </div>

        @endif


        <form method="POST" action="{{ route('admin.opportunities.store') }}" class="space-y-6">

            @csrf


            {{-- =================================================
                 CRM INFORMATION
            ================================================== --}}

            <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-6 shadow-xl">

                <div class="mb-6">

                    <h2 class="text-lg font-bold text-white">
                        {{ $isAr ? 'معلومات CRM' : 'CRM Information' }}
                    </h2>

                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ $isAr
                            ? 'اربط الفرصة بالعميل وجهة الاتصال والمسؤول عنها.'
                            : 'Connect the opportunity with the customer, contact and assigned user.' }}
                    </p>

                </div>


                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">


                    {{-- Customer --}}

                    <div>

                        <label class="mb-2 block text-sm font-medium text-slate-300">
                            {{ $t['customer'] }} *
                        </label>

                        <select name="customer_id" required
                            class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400">

                            <option value="">
                                {{ $t['select'] }}
                            </option>

                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>
                                    {{ $customer->company_name }}
                                </option>
                            @endforeach

                        </select>

                    </div>


                    {{-- Contact --}}

                    <div>

                        <label class="mb-2 block text-sm font-medium text-slate-300">
                            {{ $t['contact'] }}
                        </label>

                        <select name="contact_id"
                            class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400">

                            <option value="">
                                {{ $t['select'] }}
                            </option>

                            @foreach ($contacts as $contact)
                                <option value="{{ $contact->id }}" @selected(old('contact_id') == $contact->id)>
                                    {{ $contact->name }}

                                    @if ($contact->customer)
                                        — {{ $contact->customer->company_name }}
                                    @endif

                                </option>
                            @endforeach

                        </select>

                    </div>


                    {{-- Assigned To --}}

                    <div class="md:col-span-2">

                        <label class="mb-2 block text-sm font-medium text-slate-300">
                            {{ $t['assigned_to'] }}
                        </label>

                        <select name="assigned_to"
                            class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400">

                            <option value="">
                                {{ $t['select'] }}
                            </option>

                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected(old('assigned_to') == $user->id)>
                                    {{ $user->name }}
                                </option>
                            @endforeach

                        </select>

                    </div>

                </div>

            </div>


            {{-- =================================================
                 OPPORTUNITY INFORMATION
            ================================================== --}}

            <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-6 shadow-xl">

                <div class="mb-6">

                    <h2 class="text-lg font-bold text-white">
                        {{ $isAr ? 'معلومات الفرصة' : 'Opportunity Information' }}
                    </h2>

                </div>


                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">


                    {{-- Title --}}

                    <div class="md:col-span-2">

                        <label class="mb-2 block text-sm font-medium text-slate-300">
                            {{ $t['title_field'] }} *
                        </label>

                        <input type="text" name="title" value="{{ old('title') }}" required
                            class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none focus:border-cyan-400"
                            placeholder="{{ $isAr ? 'مثال: فرصة استيراد منتجات من الإمارات' : 'Example: Product Import Opportunity from UAE' }}">

                    </div>


                    {{-- Opportunity Type --}}

                    <div>

                        <label class="mb-2 block text-sm font-medium text-slate-300">
                            {{ $t['type'] }} *
                        </label>

                        <select name="opportunity_type" required
                            class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400">

                            @foreach ($opportunityTypes as $key => $label)
                                <option value="{{ $key }}" @selected(old('opportunity_type', 'business') === $key)>
                                    {{ $label }}
                                </option>
                            @endforeach

                        </select>

                    </div>


                    {{-- Category --}}

                    <div>

                        <label class="mb-2 block text-sm font-medium text-slate-300">
                            {{ $t['category'] }}
                        </label>

                        <select name="category"
                            class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400">

                            <option value="">
                                {{ $t['select'] }}
                            </option>

                            @foreach ($categories as $key => $label)
                                <option value="{{ $key }}" @selected(old('category') === $key)>
                                    {{ $label }}
                                </option>
                            @endforeach

                        </select>

                    </div>


                    {{-- Description --}}

                    <div class="md:col-span-2">

                        <label class="mb-2 block text-sm font-medium text-slate-300">
                            {{ $t['description'] }}
                        </label>

                        <textarea name="description" rows="5"
                            class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none focus:border-cyan-400"
                            placeholder="{{ $isAr ? 'اكتب وصفًا واضحًا للفرصة...' : 'Describe the opportunity...' }}">{{ old('description') }}</textarea>

                    </div>

                </div>

            </div>


            {{-- =================================================
                 FREIGHT INFORMATION
            ================================================== --}}

            <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-6 shadow-xl">

                <div class="mb-6">

                    <h2 class="text-lg font-bold text-white">
                        {{ $isAr ? 'معلومات الشحن' : 'Freight Information' }}
                    </h2>

                </div>


                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">


                    {{-- Cargo --}}

                    <div>

                        <label class="mb-2 block text-sm font-medium text-slate-300">
                            {{ $t['cargo_type'] }}
                        </label>

                        <input type="text" name="cargo_type" value="{{ old('cargo_type') }}"
                            class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400">

                    </div>


                    {{-- Container --}}

                    <div>

                        <label class="mb-2 block text-sm font-medium text-slate-300">
                            {{ $t['container_type'] }}
                        </label>

                        <input type="text" name="container_type" value="{{ old('container_type') }}"
                            class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400"
                            placeholder="40HC / 20GP / LCL">

                    </div>


                    {{-- Origin --}}

                    <div>

                        <label class="mb-2 block text-sm font-medium text-slate-300">
                            {{ $t['origin'] }}
                        </label>

                        <select name="origin_port_id"
                            class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400">

                            <option value="">
                                {{ $t['select'] }}
                            </option>

                            @foreach ($ports as $port)
                                <option value="{{ $port->id }}" @selected(old('origin_port_id') == $port->id)>
                                    {{ $port->name }}

                                    @if ($port->port_code)
                                        ({{ $port->port_code }})
                                    @endif
                                </option>
                            @endforeach

                        </select>

                    </div>


                    {{-- Destination --}}

                    <div>

                        <label class="mb-2 block text-sm font-medium text-slate-300">
                            {{ $t['destination'] }}
                        </label>

                        <select name="destination_port_id"
                            class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400">

                            <option value="">
                                {{ $t['select'] }}
                            </option>

                            @foreach ($ports as $port)
                                <option value="{{ $port->id }}" @selected(old('destination_port_id') == $port->id)>
                                    {{ $port->name }}

                                    @if ($port->port_code)
                                        ({{ $port->port_code }})
                                    @endif
                                </option>
                            @endforeach

                        </select>

                    </div>

                </div>

            </div>


            {{-- =================================================
                 LOCATION
            ================================================== --}}

            <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-6 shadow-xl">

                <div class="mb-6">

                    <h2 class="text-lg font-bold text-white">
                        {{ $isAr ? 'الموقع' : 'Location' }}
                    </h2>

                </div>


                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                    {{-- Country --}}

                    <div>

                        <label class="mb-2 block text-sm font-medium text-slate-300">
                            {{ $t['country'] }}
                        </label>

                        <input type="text" name="country" value="{{ old('country') }}"
                            class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none focus:border-cyan-400"
                            placeholder="{{ $isAr ? 'الإمارات العربية المتحدة' : 'United Arab Emirates' }}">

                    </div>


                    {{-- City --}}

                    <div>

                        <label class="mb-2 block text-sm font-medium text-slate-300">
                            {{ $t['city'] }}
                        </label>

                        <input type="text" name="city" value="{{ old('city') }}"
                            class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none focus:border-cyan-400"
                            placeholder="{{ $isAr ? 'دبي' : 'Dubai' }}">

                    </div>

                </div>

            </div>


            {{-- =================================================
                 FINANCIAL INFORMATION
            ================================================== --}}

            <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-6 shadow-xl">

                <div class="mb-6">

                    <h2 class="text-lg font-bold text-white">
                        {{ $isAr ? 'المعلومات المالية' : 'Financial Information' }}
                    </h2>

                </div>


                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">


                    {{-- Estimated Value --}}

                    <div>

                        <label class="mb-2 block text-sm font-medium text-slate-300">
                            {{ $t['estimated_value'] }}
                        </label>

                        <input type="number" step="0.01" min="0" name="estimated_value"
                            value="{{ old('estimated_value') }}"
                            class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400">

                    </div>


                    {{-- Required Capital --}}

                    <div>

                        <label class="mb-2 block text-sm font-medium text-slate-300">
                            {{ $t['required_capital'] }}
                        </label>

                        <input type="number" step="0.01" min="0" name="required_capital"
                            value="{{ old('required_capital') }}"
                            class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400">

                    </div>


                    {{-- Minimum Investment --}}

                    <div>

                        <label class="mb-2 block text-sm font-medium text-slate-300">
                            {{ $t['min_investment'] }}
                        </label>

                        <input type="number" step="0.01" min="0" name="min_investment"
                            value="{{ old('min_investment') }}"
                            class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400">

                    </div>


                    {{-- Currency --}}

                    <div>

                        <label class="mb-2 block text-sm font-medium text-slate-300">
                            {{ $t['currency'] }}
                        </label>

                        <select name="currency"
                            class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400">

                            @foreach (['USD', 'AED', 'EUR', 'GBP', 'SAR'] as $currency)
                                <option value="{{ $currency }}" @selected(old('currency', 'USD') === $currency)>
                                    {{ $currency }}
                                </option>
                            @endforeach

                        </select>

                    </div>

                </div>

            </div>


            {{-- =================================================
                 COMMISSION
            ================================================== --}}

            <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-6 shadow-xl">

                <div class="mb-6">

                    <h2 class="text-lg font-bold text-white">
                        {{ $isAr ? 'العمولة' : 'Commission' }}
                    </h2>

                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ $isAr
                            ? 'حدد طريقة احتساب عمولة HAG لهذه الفرصة.'
                            : 'Define how HAG commission will be calculated for this opportunity.' }}
                    </p>

                </div>


                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">


                    {{-- Commission Type --}}

                    <div>

                        <label class="mb-2 block text-sm font-medium text-slate-300">
                            {{ $t['commission_type'] }}
                        </label>

                        <select name="commission_type"
                            class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400">

                            @foreach ($commissionTypes as $key => $label)
                                <option value="{{ $key }}" @selected(old('commission_type', 'percentage') === $key)>
                                    {{ $label }}
                                </option>
                            @endforeach

                        </select>

                    </div>


                    {{-- Commission Rate --}}

                    <div>

                        <label class="mb-2 block text-sm font-medium text-slate-300">
                            {{ $t['commission_rate'] }}
                        </label>

                        <input type="number" step="0.01" min="0" name="commission_rate"
                            value="{{ old('commission_rate') }}"
                            class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400"
                            placeholder="{{ $isAr ? 'مثال: 5' : 'Example: 5' }}">

                    </div>

                </div>

            </div>


            {{-- =================================================
                 CRM PIPELINE
            ================================================== --}}

            <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-6 shadow-xl">

                <div class="mb-6">

                    <h2 class="text-lg font-bold text-white">
                        {{ $isAr ? 'مسار المبيعات' : 'Sales Pipeline' }}
                    </h2>

                </div>


                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">


                    {{-- Stage --}}

                    <div>

                        <label class="mb-2 block text-sm font-medium text-slate-300">
                            {{ $t['stage'] }} *
                        </label>

                        <select name="stage" required
                            class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400">

                            @foreach ($stages as $key => $label)
                                <option value="{{ $key }}" @selected(old('stage', 'new') === $key)>
                                    {{ $label }}
                                </option>
                            @endforeach

                        </select>

                    </div>


                    {{-- Probability --}}

                    <div>

                        <label class="mb-2 block text-sm font-medium text-slate-300">
                            {{ $t['probability'] }} (%)
                        </label>

                        <input type="number" min="0" max="100" name="probability"
                            value="{{ old('probability', 0) }}"
                            class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400">

                    </div>


                    {{-- Expected Close --}}

                    <div>

                        <label class="mb-2 block text-sm font-medium text-slate-300">
                            {{ $t['expected_close'] }}
                        </label>

                        <input type="date" name="expected_close_date" value="{{ old('expected_close_date') }}"
                            class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400">

                    </div>

                </div>

            </div>


            {{-- =================================================
                 MARKETPLACE PUBLISHING
            ================================================== --}}

            <div class="rounded-2xl border border-cyan-400/20 bg-slate-900/70 p-6 shadow-xl">

                <div class="mb-6">

                    <div class="flex items-center gap-3">

                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-400/10 text-cyan-300">
                            🌐
                        </div>

                        <div>

                            <h2 class="text-lg font-bold text-white">
                                {{ $isAr ? 'Marketplace' : 'Marketplace' }}
                            </h2>

                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                {{ $isAr
                                    ? 'حدد كيف ستظهر هذه الفرصة في سوق الفرص.'
                                    : 'Control how this opportunity appears in the public marketplace.' }}
                            </p>

                        </div>

                    </div>

                </div>


                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">


                    {{-- Visibility --}}

                    <div>

                        <label class="mb-2 block text-sm font-medium text-slate-300">
                            {{ $t['visibility'] }} *
                        </label>

                        <select name="visibility" required
                            class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400">

                            @foreach ($visibilityOptions as $key => $label)
                                <option value="{{ $key }}" @selected(old('visibility', 'private') === $key)>
                                    {{ $label }}
                                </option>
                            @endforeach

                        </select>

                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                            {{ $isAr ? 'الفرصة الخاصة لن تظهر للعامة.' : 'Private opportunities are not visible publicly.' }}
                        </p>

                    </div>


                    {{-- Verification --}}

                    <div>

                        <label class="mb-2 block text-sm font-medium text-slate-300">
                            {{ $t['verification_status'] }} *
                        </label>

                        <select name="verification_status" required
                            class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400">

                            @foreach ($verificationStatuses as $key => $label)
                                <option value="{{ $key }}" @selected(old('verification_status', 'pending') === $key)>
                                    {{ $label }}
                                </option>
                            @endforeach

                        </select>

                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                            {{ $isAr
                                ? 'التحقق يعني أن HAG قامت بمراجعة بيانات الفرصة، وليس ضمان نجاح الصفقة.'
                                : 'Verification means HAG reviewed the opportunity information; it does not guarantee transaction success.' }}
                        </p>

                    </div>


                    {{-- Published At --}}

                    <div>

                        <label class="mb-2 block text-sm font-medium text-slate-300">
                            {{ $t['published_at'] }}
                        </label>

                        <input type="datetime-local" name="published_at" value="{{ old('published_at') }}"
                            class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400">

                    </div>


                    {{-- Expires At --}}

                    <div>

                        <label class="mb-2 block text-sm font-medium text-slate-300">
                            {{ $t['expires_at'] }}
                        </label>

                        <input type="datetime-local" name="expires_at" value="{{ old('expires_at') }}"
                            class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400">

                    </div>

                </div>

            </div>


            {{-- =================================================
                 NOTES
            ================================================== --}}

            <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-6 shadow-xl">

                <div class="mb-6">

                    <h2 class="text-lg font-bold text-white">
                        {{ $t['notes'] }}
                    </h2>

                </div>

                <textarea name="notes" rows="5"
                    class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none focus:border-cyan-400"
                    placeholder="{{ $isAr ? 'ملاحظات داخلية...' : 'Internal notes...' }}">{{ old('notes') }}</textarea>

            </div>


            {{-- =================================================
                 Actions
            ================================================== --}}

            <div class="flex justify-end gap-3">

                <a href="{{ route('admin.opportunities.index') }}"
                    class="rounded-xl border border-white/10 bg-slate-800 px-6 py-3 text-sm font-semibold text-slate-300 transition hover:bg-slate-700">
                    {{ $isAr ? 'إلغاء' : 'Cancel' }}
                </a>

                <button type="submit"
                    class="rounded-xl bg-cyan-400 px-6 py-3 text-sm font-bold text-slate-950 transition hover:bg-cyan-300">
                    {{ $t['save'] }}
                </button>

            </div>

        </form>

    </div>

</x-admin-layout>
