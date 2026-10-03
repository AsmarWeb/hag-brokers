{{-- resources/views/admin/opportunities/create.blade.php --}}

<x-admin-layout>
    @php
        $isAr = app()->getLocale() === 'ar';

        $t = [
            'title' => $isAr ? 'إضافة فرصة جديدة' : 'Create Opportunity',
            'subtitle' => $isAr ? 'أضف فرصة مبيعات جديدة إلى نظام CRM.' : 'Add a new sales opportunity to the CRM.',
            'back' => $isAr ? 'العودة للفرص' : 'Back to Opportunities',
            'save' => $isAr ? 'حفظ الفرصة' : 'Save Opportunity',
            'customer' => $isAr ? 'العميل' : 'Customer',
            'contact' => $isAr ? 'جهة الاتصال' : 'Contact',
            'title_field' => $isAr ? 'اسم الفرصة' : 'Opportunity Title',
            'cargo_type' => $isAr ? 'نوع الشحنة' : 'Cargo Type',
            'container_type' => $isAr ? 'نوع الحاوية' : 'Container Type',
            'origin' => $isAr ? 'ميناء المغادرة' : 'Origin Port',
            'destination' => $isAr ? 'ميناء الوصول' : 'Destination Port',
            'estimated_value' => $isAr ? 'القيمة المتوقعة' : 'Estimated Value',
            'currency' => $isAr ? 'العملة' : 'Currency',
            'stage' => $isAr ? 'المرحلة' : 'Stage',
            'probability' => $isAr ? 'احتمالية الإغلاق' : 'Probability',
            'expected_close' => $isAr ? 'تاريخ الإغلاق المتوقع' : 'Expected Close Date',
            'notes' => $isAr ? 'ملاحظات' : 'Notes',
            'select' => $isAr ? 'اختر' : 'Select',
        ];

        $stages = [
            'new' => $isAr ? 'جديدة' : 'New',
            'qualified' => $isAr ? 'مؤهلة' : 'Qualified',
            'quotation' => $isAr ? 'عرض سعر' : 'Quotation',
            'negotiation' => $isAr ? 'تفاوض' : 'Negotiation',
            'won' => $isAr ? 'تم الفوز' : 'Won',
            'lost' => $isAr ? 'خاسرة' : 'Lost',
        ];
    @endphp

    <div class="space-y-6">

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

            <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-6 shadow-xl">

                <h2 class="mb-6 text-lg font-bold text-white">
                    {{ $isAr ? 'معلومات الفرصة' : 'Opportunity Information' }}
                </h2>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                    {{-- Customer --}}
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-300">
                            {{ $t['customer'] }} *
                        </label>

                        <select name="customer_id" required
                            class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400">
                            <option value="">{{ $t['select'] }}</option>

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
                            <option value="">{{ $t['select'] }}</option>

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

                    {{-- Title --}}
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-slate-300">
                            {{ $t['title_field'] }} *
                        </label>

                        <input type="text" name="title" value="{{ old('title') }}" required
                            class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none focus:border-cyan-400"
                            placeholder="{{ $isAr ? 'مثال: شحنة من دبي إلى جدة' : 'Example: Dubai to Jeddah shipment' }}">
                    </div>

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
                            <option value="">{{ $t['select'] }}</option>

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
                            <option value="">{{ $t['select'] }}</option>

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

                    {{-- Value --}}
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-300">
                            {{ $t['estimated_value'] }}
                        </label>

                        <input type="number" step="0.01" min="0" name="estimated_value"
                            value="{{ old('estimated_value') }}"
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
                            value="{{ old('probability') }}"
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

                    {{-- Notes --}}
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-slate-300">
                            {{ $t['notes'] }}
                        </label>

                        <textarea name="notes" rows="5"
                            class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none focus:border-cyan-400">{{ old('notes') }}</textarea>
                    </div>

                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.opportunities.index') }}"
                    class="rounded-xl border border-white/10 bg-slate-800 px-6 py-3 text-sm font-semibold text-slate-300 hover:bg-slate-700">
                    {{ $isAr ? 'إلغاء' : 'Cancel' }}
                </a>

                <button type="submit"
                    class="rounded-xl bg-cyan-400 px-6 py-3 text-sm font-bold text-slate-950 hover:bg-cyan-300">
                    {{ $t['save'] }}
                </button>
            </div>

        </form>
    </div>
</x-admin-layout>
