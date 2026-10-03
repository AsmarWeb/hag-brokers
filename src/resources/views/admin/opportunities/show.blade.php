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
            'cargo_type' => $isAr ? 'نوع الشحنة' : 'Cargo Type',
            'container_type' => $isAr ? 'نوع الحاوية' : 'Container Type',
            'origin' => $isAr ? 'ميناء المغادرة' : 'Origin Port',
            'destination' => $isAr ? 'ميناء الوصول' : 'Destination Port',
            'estimated_value' => $isAr ? 'القيمة المتوقعة' : 'Estimated Value',
            'currency' => $isAr ? 'العملة' : 'Currency',
            'stage' => $isAr ? 'المرحلة' : 'Stage',
            'probability' => $isAr ? 'الاحتمالية' : 'Probability',
            'expected_close' => $isAr ? 'الإغلاق المتوقع' : 'Expected Close',
            'assigned_to' => $isAr ? 'المسؤول' : 'Assigned To',
            'notes' => $isAr ? 'ملاحظات' : 'Notes',
            'created' => $isAr ? 'تاريخ الإنشاء' : 'Created',
            'updated' => $isAr ? 'آخر تحديث' : 'Updated',
            'confirm_delete' => $isAr
                ? 'هل أنت متأكد من حذف هذه الفرصة؟'
                : 'Are you sure you want to delete this opportunity?',
        ];

        $stages = [
            'new' => $isAr ? 'جديدة' : 'New',
            'qualified' => $isAr ? 'مؤهلة' : 'Qualified',
            'quotation' => $isAr ? 'عرض سعر' : 'Quotation',
            'negotiation' => $isAr ? 'تفاوض' : 'Negotiation',
            'won' => $isAr ? 'تم الفوز' : 'Won',
            'lost' => $isAr ? 'خاسرة' : 'Lost',
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
    @endphp

    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

            <div>
                <div class="flex flex-wrap items-center gap-3">

                    <h1 class="text-2xl font-bold text-white">
                        {{ $opportunity->title }}
                    </h1>

                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $stageClass }}">
                        {{ $stages[$opportunity->stage] ?? ucfirst($opportunity->stage) }}
                    </span>

                </div>

                <p class="mt-2 text-sm text-slate-400">
                    {{ $t['title'] }}
                </p>
            </div>

            <div class="flex flex-wrap gap-2">

                <a href="{{ route('admin.opportunities.index') }}"
                    class="rounded-xl border border-white/10 bg-slate-800 px-4 py-2.5 text-sm font-semibold text-slate-300 hover:bg-slate-700">
                    {{ $t['back'] }}
                </a>

                <a href="{{ route('admin.opportunities.edit', $opportunity) }}"
                    class="rounded-xl bg-amber-400 px-4 py-2.5 text-sm font-bold text-slate-950 hover:bg-amber-300">
                    {{ $t['edit'] }}
                </a>

                <form method="POST" action="{{ route('admin.opportunities.destroy', $opportunity) }}"
                    onsubmit="return confirm('{{ $t['confirm_delete'] }}')">
                    @csrf
                    @method('DELETE')

                    <button type="submit"
                        class="rounded-xl bg-red-500 px-4 py-2.5 text-sm font-bold text-white hover:bg-red-400">
                        {{ $t['delete'] }}
                    </button>
                </form>

            </div>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        {{-- Main --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            {{-- Main Details --}}
            <div class="space-y-6 lg:col-span-2">

                <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-6 shadow-xl">

                    <h2 class="mb-6 text-lg font-bold text-white">
                        {{ $isAr ? 'معلومات الفرصة' : 'Opportunity Information' }}
                    </h2>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">

                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500">
                                {{ $t['customer'] }}
                            </div>

                            <div class="mt-2 text-sm font-semibold text-white">
                                @if ($opportunity->customer)
                                    <a href="{{ route('admin.customers.show', $opportunity->customer) }}"
                                        class="hover:text-cyan-300">
                                        {{ $opportunity->customer->company_name }}
                                    </a>
                                @else
                                    —
                                @endif
                            </div>
                        </div>

                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500">
                                {{ $t['contact'] }}
                            </div>

                            <div class="mt-2 text-sm text-slate-300">
                                {{ $opportunity->contact?->name ?? '—' }}
                            </div>

                            @if ($opportunity->contact?->email)
                                <div class="mt-1 text-xs text-slate-500">
                                    {{ $opportunity->contact->email }}
                                </div>
                            @endif
                        </div>

                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500">
                                {{ $t['cargo_type'] }}
                            </div>

                            <div class="mt-2 text-sm text-slate-300">
                                {{ $opportunity->cargo_type ?: '—' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500">
                                {{ $t['container_type'] }}
                            </div>

                            <div class="mt-2 text-sm text-slate-300">
                                {{ $opportunity->container_type ?: '—' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500">
                                {{ $t['origin'] }}
                            </div>

                            <div class="mt-2 text-sm text-slate-300">
                                {{ $opportunity->originPort?->name ?? '—' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500">
                                {{ $t['destination'] }}
                            </div>

                            <div class="mt-2 text-sm text-slate-300">
                                {{ $opportunity->destinationPort?->name ?? '—' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500">
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

                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500">
                                {{ $t['probability'] }}
                            </div>

                            <div class="mt-2 text-lg font-bold text-white">
                                {{ $opportunity->probability !== null ? $opportunity->probability . '%' : '—' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500">
                                {{ $t['expected_close'] }}
                            </div>

                            <div class="mt-2 text-sm text-slate-300">
                                @if ($opportunity->expected_close_date)
                                    {{ \Illuminate\Support\Carbon::parse($opportunity->expected_close_date)->format('Y-m-d') }}
                                @else
                                    —
                                @endif
                            </div>
                        </div>

                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500">
                                {{ $t['assigned_to'] }}
                            </div>

                            <div class="mt-2 text-sm text-slate-300">
                                {{ $opportunity->assignedTo?->name ?? '—' }}
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
                        <div class="text-sm text-slate-500">
                            {{ $isAr ? 'لا توجد ملاحظات.' : 'No notes available.' }}
                        </div>
                    @endif

                </div>

            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">

                {{-- Stage --}}
                <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-6 shadow-xl">

                    <div class="text-xs uppercase tracking-wide text-slate-500">
                        {{ $t['stage'] }}
                    </div>

                    <div class="mt-3">
                        <span
                            class="inline-flex rounded-full px-4 py-2 text-sm font-semibold ring-1 {{ $stageClass }}">
                            {{ $stages[$opportunity->stage] ?? ucfirst($opportunity->stage) }}
                        </span>
                    </div>

                </div>

                {{-- Dates --}}
                <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-6 shadow-xl">

                    <h2 class="mb-5 text-lg font-bold text-white">
                        {{ $isAr ? 'معلومات النظام' : 'System Information' }}
                    </h2>

                    <div class="space-y-5">

                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500">
                                {{ $t['created'] }}
                            </div>

                            <div class="mt-2 text-sm text-slate-300">
                                {{ $opportunity->created_at?->format('Y-m-d H:i') ?? '—' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500">
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

                        <a href="{{ route('admin.customers.show', $opportunity->customer) }}"
                            class="mt-4 inline-flex text-sm font-semibold text-cyan-300 hover:text-cyan-200">
                            {{ $isAr ? 'عرض العميل →' : 'View Customer →' }}
                        </a>

                    </div>
                @endif

            </div>
        </div>
    </div>
</x-admin-layout>
