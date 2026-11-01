{{-- resources/views/admin/opportunities/show.blade.php --}}

<x-admin-layout>
    @php
        $isAr = app()->getLocale() === 'ar';

        $t = [
            'title' => $isAr ? 'تفاصيل الفرصة' : 'Opportunity Details',
            'back' => $isAr ? 'العودة للفرص' : 'Back to Opportunities',
            'edit' => $isAr ? 'تعديل' : 'Edit',
            'delete' => $isAr ? 'حذف' : 'Delete',

            'customer' => $isAr ? 'العميل' : 'Customer',
            'contact' => $isAr ? 'جهة الاتصال' : 'Contact',
            'assigned_to' => $isAr ? 'المسؤول' : 'Assigned To',

            'opportunity_type' => $isAr ? 'نوع الفرصة' : 'Opportunity Type',
            'category' => $isAr ? 'التصنيف' : 'Category',
            'description' => $isAr ? 'الوصف' : 'Description',

            'cargo_type' => $isAr ? 'نوع الشحنة' : 'Cargo Type',
            'container_type' => $isAr ? 'نوع الحاوية' : 'Container Type',
            'origin' => $isAr ? 'ميناء المغادرة' : 'Origin Port',
            'destination' => $isAr ? 'ميناء الوصول' : 'Destination Port',

            'country' => $isAr ? 'الدولة' : 'Country',
            'city' => $isAr ? 'المدينة' : 'City',

            'estimated_value' => $isAr ? 'القيمة المتوقعة' : 'Estimated Value',
            'required_capital' => $isAr ? 'رأس المال المطلوب' : 'Required Capital',
            'min_investment' => $isAr ? 'الحد الأدنى للاستثمار' : 'Minimum Investment',
            'currency' => $isAr ? 'العملة' : 'Currency',

            'commission_type' => $isAr ? 'نوع العمولة' : 'Commission Type',
            'commission_rate' => $isAr ? 'قيمة / نسبة العمولة' : 'Commission Rate / Amount',

            'stage' => $isAr ? 'المرحلة' : 'Stage',
            'probability' => $isAr ? 'الاحتمالية' : 'Probability',
            'expected_close' => $isAr ? 'الإغلاق المتوقع' : 'Expected Close',

            'visibility' => $isAr ? 'الظهور' : 'Visibility',
            'verification' => $isAr ? 'حالة التحقق' : 'Verification',
            'published' => $isAr ? 'تاريخ النشر' : 'Published',
            'expires' => $isAr ? 'تاريخ الانتهاء' : 'Expires',

            'interests' => $isAr ? 'طلبات الاهتمام' : 'Interest Requests',
            'interest_count' => $isAr ? 'عدد المهتمين' : 'Interested Users',
            'role' => $isAr ? 'الدور' : 'Role',
            'capital' => $isAr ? 'رأس المال المتاح' : 'Available Capital',
            'message' => $isAr ? 'الرسالة' : 'Message',
            'status' => $isAr ? 'الحالة' : 'Status',
            'reviewed_by' => $isAr ? 'تمت المراجعة بواسطة' : 'Reviewed By',
            'reviewed_at' => $isAr ? 'تاريخ المراجعة' : 'Reviewed At',

            'notes' => $isAr ? 'ملاحظات' : 'Notes',
            'created' => $isAr ? 'تاريخ الإنشاء' : 'Created',
            'updated' => $isAr ? 'آخر تحديث' : 'Updated',

            'system_info' => $isAr ? 'معلومات النظام' : 'System Information',

            'no_interests' => $isAr
                ? 'لا توجد طلبات اهتمام بهذه الفرصة حتى الآن.'
                : 'No interest requests for this opportunity yet.',

            'no_description' => $isAr ? 'لا يوجد وصف.' : 'No description available.',

            'no_notes' => $isAr ? 'لا توجد ملاحظات.' : 'No notes available.',

            'confirm_delete' => $isAr
                ? 'هل أنت متأكد من حذف هذه الفرصة؟'
                : 'Are you sure you want to delete this opportunity?',
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

        $stages = [
            'new' => $isAr ? 'جديدة' : 'New',
            'qualified' => $isAr ? 'مؤهلة' : 'Qualified',
            'quotation' => $isAr ? 'عرض سعر' : 'Quotation',
            'negotiation' => $isAr ? 'تفاوض' : 'Negotiation',
            'won' => $isAr ? 'تم الفوز' : 'Won',
            'lost' => $isAr ? 'خاسرة' : 'Lost',
        ];

        $visibilities = [
            'private' => $isAr ? 'خاصة' : 'Private',
            'public' => $isAr ? 'عامة' : 'Public',
            'invite_only' => $isAr ? 'بدعوة فقط' : 'Invite Only',
        ];

        $verificationStatuses = [
            'pending' => $isAr ? 'قيد الانتظار' : 'Pending',
            'under_review' => $isAr ? 'قيد المراجعة' : 'Under Review',
            'verified' => $isAr ? 'موثقة' : 'Verified',
            'rejected' => $isAr ? 'مرفوضة' : 'Rejected',
        ];

        $interestStatuses = [
            'pending' => $isAr ? 'قيد الانتظار' : 'Pending',
            'approved' => $isAr ? 'مقبول' : 'Approved',
            'rejected' => $isAr ? 'مرفوض' : 'Rejected',
            'withdrawn' => $isAr ? 'منسحب' : 'Withdrawn',
        ];

        $interestStatusClasses = [
            'pending' => 'bg-amber-500/10 text-amber-300 ring-amber-400/20',
            'approved' => 'bg-emerald-500/10 text-emerald-300 ring-emerald-400/20',
            'rejected' => 'bg-red-500/10 text-red-300 ring-red-400/20',
            'withdrawn' => 'bg-slate-500/10 text-slate-300 ring-slate-400/20',
        ];

        $stageClass = match ($opportunity->stage) {
            'new' => 'bg-slate-500/10 text-slate-300 ring-slate-400/20',
            'qualified' => 'bg-blue-500/10 text-blue-300 ring-blue-400/20',
            'quotation' => 'bg-cyan-500/10 text-cyan-300 ring-cyan-400/20',
            'negotiation' => 'bg-amber-500/10 text-amber-300 ring-amber-400/20',
            'won' => 'bg-emerald-500/10 text-emerald-300 ring-emerald-400/20',
            'lost' => 'bg-red-500/10 text-red-300 ring-red-400/20',
            default => 'bg-slate-500/10 text-slate-300 ring-slate-400/20',
        };

        $verificationClass = match ($opportunity->verification_status) {
            'verified' => 'bg-emerald-500/10 text-emerald-300 ring-emerald-400/20',
            'under_review' => 'bg-amber-500/10 text-amber-300 ring-amber-400/20',
            'rejected' => 'bg-red-500/10 text-red-300 ring-red-400/20',
            default => 'bg-slate-500/10 text-slate-300 ring-slate-400/20',
        };

        $visibilityClass = match ($opportunity->visibility) {
            'public' => 'bg-cyan-500/10 text-cyan-300 ring-cyan-400/20',
            'invite_only' => 'bg-purple-500/10 text-purple-300 ring-purple-400/20',
            default => 'bg-slate-500/10 text-slate-300 ring-slate-400/20',
        };
    @endphp

    <div class="space-y-6">

        {{-- =========================================================
            HEADER
        ========================================================== --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

            <div>
                <div class="flex flex-wrap items-center gap-3">

                    <h1 class="text-2xl font-bold text-white">
                        {{ $opportunity->title }}
                    </h1>

                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $stageClass }}">
                        {{ $stages[$opportunity->stage] ?? ucfirst($opportunity->stage) }}
                    </span>

                    @if ($opportunity->verification_status === 'verified')
                        <span
                            class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $verificationClass }}">
                            {{ $verificationStatuses[$opportunity->verification_status] ?? $opportunity->verification_status }}
                        </span>
                    @endif

                </div>

                <p class="mt-2 text-sm text-slate-400">
                    {{ $t['title'] }}
                </p>
            </div>

            <div class="flex flex-wrap gap-2">

                <a href="{{ route('admin.opportunities.index') }}"
                    class="rounded-xl border border-white/10 bg-slate-800 px-4 py-2.5 text-sm font-semibold text-slate-300 transition hover:bg-slate-700">
                    {{ $t['back'] }}
                </a>

                <a href="{{ route('admin.opportunities.edit', $opportunity) }}"
                    class="rounded-xl bg-amber-400 px-4 py-2.5 text-sm font-bold text-slate-950 transition hover:bg-amber-300">
                    {{ $t['edit'] }}
                </a>

                <form method="POST" action="{{ route('admin.opportunities.destroy', $opportunity) }}"
                    onsubmit="return confirm('{{ $t['confirm_delete'] }}')">

                    @csrf
                    @method('DELETE')

                    <button type="submit"
                        class="rounded-xl bg-red-500 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-red-400">
                        {{ $t['delete'] }}
                    </button>

                </form>

            </div>
        </div>


        {{-- Success --}}
        @if (session('success'))
            <div class="rounded-xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-300">
                {{ session('success') }}
            </div>
        @endif


        {{-- =========================================================
            MAIN LAYOUT
        ========================================================== --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            {{-- =====================================================
                MAIN CONTENT
            ====================================================== --}}
            <div class="space-y-6 lg:col-span-2">

                {{-- Opportunity Information --}}
                <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-6 shadow-xl">

                    <h2 class="mb-6 text-lg font-bold text-white">
                        {{ $isAr ? 'معلومات الفرصة' : 'Opportunity Information' }}
                    </h2>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">

                        {{-- Customer --}}
                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                {{ $t['customer'] }}
                            </div>

                            <div class="mt-2 text-sm font-semibold text-white">

                                @if ($opportunity->customer)

                                    @if (Route::has('admin.customers.show'))
                                        <a href="{{ route('admin.customers.show', $opportunity->customer) }}"
                                            class="transition hover:text-cyan-300">
                                            {{ $opportunity->customer->company_name }}
                                        </a>
                                    @else
                                        {{ $opportunity->customer->company_name }}
                                    @endif
                                @else
                                    —
                                @endif

                            </div>
                        </div>


                        {{-- Contact --}}
                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                {{ $t['contact'] }}
                            </div>

                            <div class="mt-2 text-sm text-slate-300">
                                {{ $opportunity->contact?->name ?? '—' }}
                            </div>

                            @if ($opportunity->contact?->email)
                                <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    {{ $opportunity->contact->email }}
                                </div>
                            @endif
                        </div>


                        {{-- Assigned --}}
                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                {{ $t['assigned_to'] }}
                            </div>

                            <div class="mt-2 text-sm text-slate-300">
                                {{ $opportunity->assignedTo?->name ?? '—' }}
                            </div>
                        </div>


                        {{-- Opportunity Type --}}
                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                {{ $t['opportunity_type'] }}
                            </div>

                            <div class="mt-2 text-sm text-cyan-300">
                                {{ $opportunityTypes[$opportunity->opportunity_type] ?? ucfirst($opportunity->opportunity_type ?? '—') }}
                            </div>
                        </div>


                        {{-- Category --}}
                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                {{ $t['category'] }}
                            </div>

                            <div class="mt-2 text-sm text-slate-300">
                                {{ $categories[$opportunity->category] ?? ($opportunity->category ?: '—') }}
                            </div>
                        </div>


                        {{-- Country --}}
                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                {{ $t['country'] }}
                            </div>

                            <div class="mt-2 text-sm text-slate-300">
                                {{ $opportunity->country ?: '—' }}
                            </div>
                        </div>


                        {{-- City --}}
                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                {{ $t['city'] }}
                            </div>

                            <div class="mt-2 text-sm text-slate-300">
                                {{ $opportunity->city ?: '—' }}
                            </div>
                        </div>


                        {{-- Cargo --}}
                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                {{ $t['cargo_type'] }}
                            </div>

                            <div class="mt-2 text-sm text-slate-300">
                                {{ $opportunity->cargo_type ?: '—' }}
                            </div>
                        </div>


                        {{-- Container --}}
                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                {{ $t['container_type'] }}
                            </div>

                            <div class="mt-2 text-sm text-slate-300">
                                {{ $opportunity->container_type ?: '—' }}
                            </div>
                        </div>


                        {{-- Origin --}}
                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                {{ $t['origin'] }}
                            </div>

                            <div class="mt-2 text-sm text-slate-300">
                                {{ $opportunity->originPort?->name ?? '—' }}
                            </div>
                        </div>


                        {{-- Destination --}}
                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                {{ $t['destination'] }}
                            </div>

                            <div class="mt-2 text-sm text-slate-300">
                                {{ $opportunity->destinationPort?->name ?? '—' }}
                            </div>
                        </div>

                    </div>
                </div>


                {{-- Description --}}
                <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-6 shadow-xl">

                    <h2 class="mb-4 text-lg font-bold text-white">
                        {{ $t['description'] }}
                    </h2>

                    @if ($opportunity->description)
                        <div class="whitespace-pre-line text-sm leading-7 text-slate-300">
                            {{ $opportunity->description }}
                        </div>
                    @else
                        <div class="text-sm text-slate-500 dark:text-slate-400">
                            {{ $t['no_description'] }}
                        </div>
                    @endif

                </div>


                {{-- Financial Information --}}
                <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-6 shadow-xl">

                    <h2 class="mb-6 text-lg font-bold text-white">
                        {{ $isAr ? 'المعلومات المالية' : 'Financial Information' }}
                    </h2>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">

                        {{-- Estimated Value --}}
                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                {{ $t['estimated_value'] }}
                            </div>

                            <div class="mt-2 text-lg font-bold text-cyan-300">

                                @if ($opportunity->estimated_value !== null)
                                    {{ number_format((float) $opportunity->estimated_value, 2) }}
                                    {{ $opportunity->currency ?? 'USD' }}
                                @else
                                    —
                                @endif

                            </div>
                        </div>


                        {{-- Required Capital --}}
                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                {{ $t['required_capital'] }}
                            </div>

                            <div class="mt-2 text-lg font-bold text-white">

                                @if ($opportunity->required_capital !== null)
                                    {{ number_format((float) $opportunity->required_capital, 2) }}
                                    {{ $opportunity->currency ?? 'USD' }}
                                @else
                                    —
                                @endif

                            </div>
                        </div>


                        {{-- Minimum Investment --}}
                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                {{ $t['min_investment'] }}
                            </div>

                            <div class="mt-2 text-lg font-bold text-white">

                                @if ($opportunity->min_investment !== null)
                                    {{ number_format((float) $opportunity->min_investment, 2) }}
                                    {{ $opportunity->currency ?? 'USD' }}
                                @else
                                    —
                                @endif

                            </div>
                        </div>


                        {{-- Currency --}}
                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                {{ $t['currency'] }}
                            </div>

                            <div class="mt-2 text-sm font-semibold text-slate-300">
                                {{ $opportunity->currency ?? 'USD' }}
                            </div>
                        </div>


                        {{-- Commission Type --}}
                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                {{ $t['commission_type'] }}
                            </div>

                            <div class="mt-2 text-sm text-slate-300">
                                {{ $opportunity->commission_type === 'percentage'
                                    ? ($isAr
                                        ? 'نسبة مئوية'
                                        : 'Percentage')
                                    : ($isAr
                                        ? 'مبلغ ثابت'
                                        : 'Fixed Amount') }}
                            </div>
                        </div>


                        {{-- Commission Rate --}}
                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                {{ $t['commission_rate'] }}
                            </div>

                            <div class="mt-2 text-lg font-bold text-amber-300">

                                @if ($opportunity->commission_rate !== null)

                                    {{ number_format((float) $opportunity->commission_rate, 2) }}

                                    @if ($opportunity->commission_type === 'percentage')
                                        %
                                    @else
                                        {{ $opportunity->currency ?? 'USD' }}
                                    @endif
                                @else
                                    —
                                @endif

                            </div>
                        </div>

                    </div>
                </div>


                {{-- Notes --}}
                <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-6 shadow-xl">

                    <h2 class="mb-4 text-lg font-bold text-white">
                        {{ $t['notes'] }}
                    </h2>

                    @if ($opportunity->notes)
                        <div class="whitespace-pre-line text-sm leading-7 text-slate-300">
                            {{ $opportunity->notes }}
                        </div>
                    @else
                        <div class="text-sm text-slate-500 dark:text-slate-400">
                            {{ $t['no_notes'] }}
                        </div>
                    @endif

                </div>


                {{-- =====================================================
                    INTEREST REQUESTS
                ====================================================== --}}
                <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-6 shadow-xl">

                    <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">

                        <div>
                            <h2 class="text-lg font-bold text-white">
                                {{ $t['interests'] }}
                            </h2>

                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                {{ $t['interest_count'] }}:
                                <span class="font-semibold text-cyan-300">
                                    {{ $opportunity->interests->count() }}
                                </span>
                            </p>
                        </div>

                    </div>


                    @if ($opportunity->interests->count())

                        <div class="space-y-4">

                            @foreach ($opportunity->interests as $interest)
                                @php
                                    $interestStatusClass =
                                        $interestStatusClasses[$interest->status] ??
                                        'bg-slate-500/10 text-slate-300 ring-slate-400/20';
                                @endphp

                                <div class="rounded-xl border border-white/10 bg-slate-950/60 p-5">

                                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">

                                        <div class="min-w-0">

                                            <div class="flex flex-wrap items-center gap-3">

                                                <div class="text-sm font-bold text-white">
                                                    {{ $interest->user?->name ?? '—' }}
                                                </div>

                                                <span
                                                    class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $interestStatusClass }}">
                                                    {{ $interestStatuses[$interest->status] ?? ucfirst($interest->status) }}
                                                </span>

                                            </div>

                                            @if ($interest->user?->email)
                                                <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                                    {{ $interest->user->email }}
                                                </div>
                                            @endif

                                        </div>

                                        <div class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ $interest->created_at?->format('Y-m-d H:i') ?? '—' }}
                                        </div>

                                    </div>


                                    <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">

                                        {{-- Role --}}
                                        <div>
                                            <div class="text-xs uppercase tracking-wide text-slate-600">
                                                {{ $t['role'] }}
                                            </div>

                                            <div class="mt-1 text-sm text-slate-300">
                                                {{ $interest->role ?: '—' }}
                                            </div>
                                        </div>


                                        {{-- Available Capital --}}
                                        <div>
                                            <div class="text-xs uppercase tracking-wide text-slate-600">
                                                {{ $t['capital'] }}
                                            </div>

                                            <div class="mt-1 text-sm font-semibold text-cyan-300">

                                                @if ($interest->available_capital !== null)
                                                    {{ number_format((float) $interest->available_capital, 2) }}
                                                    {{ $interest->currency ?? 'USD' }}
                                                @else
                                                    —
                                                @endif

                                            </div>
                                        </div>

                                    </div>


                                    {{-- Message --}}
                                    @if ($interest->message)
                                        <div class="mt-5 rounded-lg border border-white/5 bg-slate-900/60 p-4">

                                            <div class="text-xs uppercase tracking-wide text-slate-600">
                                                {{ $t['message'] }}
                                            </div>

                                            <div class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-300">
                                                {{ $interest->message }}
                                            </div>

                                        </div>
                                    @endif


                                    {{-- Review Information --}}
                                    @if ($interest->reviewed_by || $interest->reviewed_at)
                                        <div class="mt-5 border-t border-white/5 pt-4">

                                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                                                @if ($interest->reviewer)
                                                    <div>
                                                        <div class="text-xs uppercase tracking-wide text-slate-600">
                                                            {{ $t['reviewed_by'] }}
                                                        </div>

                                                        <div class="mt-1 text-sm text-slate-300">
                                                            {{ $interest->reviewer->name }}
                                                        </div>
                                                    </div>
                                                @endif

                                                @if ($interest->reviewed_at)
                                                    <div>
                                                        <div class="text-xs uppercase tracking-wide text-slate-600">
                                                            {{ $t['reviewed_at'] }}
                                                        </div>

                                                        <div class="mt-1 text-sm text-slate-300">
                                                            {{ $interest->reviewed_at->format('Y-m-d H:i') }}
                                                        </div>
                                                    </div>
                                                @endif

                                            </div>

                                        </div>
                                    @endif

                                </div>
                            @endforeach

                        </div>
                    @else
                        <div class="rounded-xl border border-dashed border-white/10 bg-slate-950/40 p-8 text-center">

                            <div class="text-sm text-slate-500 dark:text-slate-400">
                                {{ $t['no_interests'] }}
                            </div>

                        </div>

                    @endif

                </div>

            </div>


            {{-- =====================================================
                SIDEBAR
            ====================================================== --}}
            <div class="space-y-6">

                {{-- Stage --}}
                <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-6 shadow-xl">

                    <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        {{ $t['stage'] }}
                    </div>

                    <div class="mt-3">

                        <span
                            class="inline-flex rounded-full px-4 py-2 text-sm font-semibold ring-1 {{ $stageClass }}">
                            {{ $stages[$opportunity->stage] ?? ucfirst($opportunity->stage) }}
                        </span>

                    </div>

                    {{-- Probability --}}
                    <div class="mt-6">

                        <div class="mb-2 flex items-center justify-between text-xs">

                            <span class="text-slate-500 dark:text-slate-400">
                                {{ $t['probability'] }}
                            </span>

                            <span class="font-semibold text-white">
                                {{ $opportunity->probability ?? 0 }}%
                            </span>

                        </div>

                        <div class="h-2 overflow-hidden rounded-full bg-slate-800">

                            <div class="h-full rounded-full bg-cyan-400 transition-all"
                                style="width: {{ min(100, max(0, (int) $opportunity->probability)) }}%">
                            </div>

                        </div>

                    </div>

                </div>


                {{-- Marketplace Status --}}
                <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-6 shadow-xl">

                    <h2 class="mb-5 text-lg font-bold text-white">
                        {{ $isAr ? 'حالة Marketplace' : 'Marketplace Status' }}
                    </h2>

                    <div class="space-y-5">

                        {{-- Visibility --}}
                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                {{ $t['visibility'] }}
                            </div>

                            <div class="mt-2">

                                <span
                                    class="inline-flex rounded-full px-3 py-1.5 text-xs font-semibold ring-1 {{ $visibilityClass }}">
                                    {{ $visibilities[$opportunity->visibility] ?? ucfirst($opportunity->visibility) }}
                                </span>

                            </div>
                        </div>


                        {{-- Verification --}}
                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                {{ $t['verification'] }}
                            </div>

                            <div class="mt-2">

                                <span
                                    class="inline-flex rounded-full px-3 py-1.5 text-xs font-semibold ring-1 {{ $verificationClass }}">
                                    {{ $verificationStatuses[$opportunity->verification_status] ?? ucfirst($opportunity->verification_status) }}
                                </span>

                            </div>
                        </div>


                        {{-- Published --}}
                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                {{ $t['published'] }}
                            </div>

                            <div class="mt-2 text-sm text-slate-300">

                                @if ($opportunity->published_at)
                                    {{ $opportunity->published_at->format('Y-m-d H:i') }}
                                @else
                                    —
                                @endif

                            </div>
                        </div>


                        {{-- Expires --}}
                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                {{ $t['expires'] }}
                            </div>

                            <div class="mt-2 text-sm text-slate-300">

                                @if ($opportunity->expires_at)
                                    {{ $opportunity->expires_at->format('Y-m-d H:i') }}
                                @else
                                    {{ $isAr ? 'بدون تاريخ انتهاء' : 'No expiration' }}
                                @endif

                            </div>
                        </div>

                    </div>

                </div>


                {{-- System Information --}}
                <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-6 shadow-xl">

                    <h2 class="mb-5 text-lg font-bold text-white">
                        {{ $t['system_info'] }}
                    </h2>

                    <div class="space-y-5">

                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                {{ $t['created'] }}
                            </div>

                            <div class="mt-2 text-sm text-slate-300">
                                {{ $opportunity->created_at?->format('Y-m-d H:i') ?? '—' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                {{ $t['updated'] }}
                            </div>

                            <div class="mt-2 text-sm text-slate-300">
                                {{ $opportunity->updated_at?->format('Y-m-d H:i') ?? '—' }}
                            </div>
                        </div>

                    </div>

                </div>


                {{-- Customer --}}
                @if ($opportunity->customer)

                    <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-6 shadow-xl">

                        <h2 class="mb-4 text-lg font-bold text-white">
                            {{ $t['customer'] }}
                        </h2>

                        <div class="text-sm font-semibold text-cyan-300">
                            {{ $opportunity->customer->company_name }}
                        </div>

                        @if ($opportunity->customer->email)
                            <div class="mt-2 text-sm text-slate-400">
                                {{ $opportunity->customer->email }}
                            </div>
                        @endif

                        @if ($opportunity->customer->phone)
                            <div class="mt-1 text-sm text-slate-400">
                                {{ $opportunity->customer->phone }}
                            </div>
                        @endif

                        @if (Route::has('admin.customers.show'))
                            <a href="{{ route('admin.customers.show', $opportunity->customer) }}"
                                class="mt-4 inline-flex text-sm font-semibold text-cyan-300 transition hover:text-cyan-200">
                                {{ $isAr ? 'عرض العميل →' : 'View Customer →' }}
                            </a>
                        @endif

                    </div>

                @endif

            </div>

        </div>
    </div>
</x-admin-layout>
