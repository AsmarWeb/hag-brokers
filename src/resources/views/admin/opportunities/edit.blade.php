{{-- resources/views/admin/opportunities/edit.blade.php --}}

<x-admin-layout>
    @php
        $isAr = app()->getLocale() === 'ar';

        $t = [
            'title' => $isAr ? 'تعديل الفرصة' : 'Edit Opportunity',
            'subtitle' => $isAr
                ? 'تحديث معلومات الفرصة وبيانات السوق والصفقة.'
                : 'Update opportunity, marketplace and deal information.',

            'back' => $isAr ? 'العودة للفرص' : 'Back to Opportunities',
            'save' => $isAr ? 'حفظ التغييرات' : 'Save Changes',
            'select' => $isAr ? 'اختر' : 'Select',

            'customer' => $isAr ? 'العميل' : 'Customer',
            'contact' => $isAr ? 'جهة الاتصال' : 'Contact',
            'assigned_to' => $isAr ? 'المسؤول عن الفرصة' : 'Assigned To',

            'title_field' => $isAr ? 'اسم الفرصة' : 'Opportunity Title',
            'opportunity_type' => $isAr ? 'نوع الفرصة' : 'Opportunity Type',
            'category' => $isAr ? 'التصنيف' : 'Category',
            'description' => $isAr ? 'وصف الفرصة' : 'Opportunity Description',

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
            'commission_rate' => $isAr ? 'نسبة / قيمة العمولة' : 'Commission Rate / Amount',

            'stage' => $isAr ? 'المرحلة' : 'Stage',
            'probability' => $isAr ? 'احتمالية الإغلاق' : 'Probability',
            'expected_close' => $isAr ? 'تاريخ الإغلاق المتوقع' : 'Expected Close Date',

            'visibility' => $isAr ? 'ظهور الفرصة' : 'Visibility',
            'verification_status' => $isAr ? 'حالة التحقق' : 'Verification Status',
            'published_at' => $isAr ? 'تاريخ النشر' : 'Published At',
            'expires_at' => $isAr ? 'تاريخ انتهاء الفرصة' : 'Expires At',

            'notes' => $isAr ? 'ملاحظات' : 'Notes',

            'crm_section' => $isAr ? 'معلومات CRM' : 'CRM Information',
            'opportunity_section' => $isAr ? 'معلومات الفرصة' : 'Opportunity Information',
            'freight_section' => $isAr ? 'معلومات الشحن' : 'Freight Information',
            'location_section' => $isAr ? 'الموقع' : 'Location',
            'financial_section' => $isAr ? 'المعلومات المالية' : 'Financial Information',
            'commission_section' => $isAr ? 'العمولة' : 'Commission',
            'pipeline_section' => $isAr ? 'مسار المبيعات' : 'Sales Pipeline',
            'marketplace_section' => $isAr ? 'Marketplace / السوق' : 'Marketplace',
            'notes_section' => $isAr ? 'ملاحظات إضافية' : 'Additional Notes',

            'marketplace_notice' => $isAr
                ? 'ملاحظة: الفرصة لن تظهر للعامة إلا إذا كانت حالتها "موثقة" وظهورها "عام". التحقق يعني أن HAG قامت بمراجعة المعلومات المقدمة، ولا يمثل ضماناً لنجاح الصفقة أو العائد الاستثماري.'
                : 'Note: The opportunity will only appear publicly when it is "Verified" and visibility is "Public". Verification means HAG has reviewed the submitted information and does not guarantee transaction success or investment returns.',

            'cancel' => $isAr ? 'إلغاء' : 'Cancel',
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

        $currencies = ['USD', 'AED', 'EUR', 'GBP', 'SAR'];

        $inputClass =
            'w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none transition focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400/30';

        $sectionClass = 'rounded-2xl border border-white/10 bg-slate-900/70 p-6 shadow-xl';

        $labelClass = 'mb-2 block text-sm font-medium text-slate-300';
    @endphp

    <div class="space-y-6">

        {{-- Header --}}
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

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="rounded-xl border border-red-400/20 bg-red-400/10 p-4">
                <ul class="space-y-1 text-sm text-red-300">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.opportunities.update', $opportunity) }}" class="space-y-6">

            @csrf
            @method('PUT')

            {{-- =========================================================
                CRM INFORMATION
            ========================================================== --}}
            <div class="{{ $sectionClass }}">

                <h2 class="mb-6 text-lg font-bold text-white">
                    {{ $t['crm_section'] }}
                </h2>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                    {{-- Customer --}}
                    <div>
                        <label class="{{ $labelClass }}">
                            {{ $t['customer'] }} *
                        </label>

                        <select name="customer_id" required class="{{ $inputClass }}">

                            <option value="">
                                {{ $t['select'] }}
                            </option>

                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}" @selected(old('customer_id', $opportunity->customer_id) == $customer->id)>
                                    {{ $customer->company_name }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    {{-- Contact --}}
                    <div>
                        <label class="{{ $labelClass }}">
                            {{ $t['contact'] }}
                        </label>

                        <select name="contact_id" class="{{ $inputClass }}">

                            <option value="">
                                {{ $t['select'] }}
                            </option>

                            @foreach ($contacts as $contact)
                                <option value="{{ $contact->id }}" @selected(old('contact_id', $opportunity->contact_id) == $contact->id)>
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
                        <label class="{{ $labelClass }}">
                            {{ $t['assigned_to'] }}
                        </label>

                        <select name="assigned_to" class="{{ $inputClass }}">

                            <option value="">
                                {{ $t['select'] }}
                            </option>

                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected(old('assigned_to', $opportunity->assigned_to) == $user->id)>
                                    {{ $user->name }}

                                    @if ($user->email)
                                        — {{ $user->email }}
                                    @endif
                                </option>
                            @endforeach

                        </select>
                    </div>

                </div>
            </div>


            {{-- =========================================================
                OPPORTUNITY INFORMATION
            ========================================================== --}}
            <div class="{{ $sectionClass }}">

                <h2 class="mb-6 text-lg font-bold text-white">
                    {{ $t['opportunity_section'] }}
                </h2>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                    {{-- Title --}}
                    <div class="md:col-span-2">
                        <label class="{{ $labelClass }}">
                            {{ $t['title_field'] }} *
                        </label>

                        <input type="text" name="title" value="{{ old('title', $opportunity->title) }}" required
                            class="{{ $inputClass }}">
                    </div>

                    {{-- Opportunity Type --}}
                    <div>
                        <label class="{{ $labelClass }}">
                            {{ $t['opportunity_type'] }} *
                        </label>

                        <select name="opportunity_type" required class="{{ $inputClass }}">

                            @foreach ($opportunityTypes as $key => $label)
                                <option value="{{ $key }}" @selected(old('opportunity_type', $opportunity->opportunity_type ?? 'business') === $key)>
                                    {{ $label }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    {{-- Category --}}
                    <div>
                        <label class="{{ $labelClass }}">
                            {{ $t['category'] }}
                        </label>

                        <select name="category" class="{{ $inputClass }}">

                            <option value="">
                                {{ $t['select'] }}
                            </option>

                            @foreach ($categories as $key => $label)
                                <option value="{{ $key }}" @selected(old('category', $opportunity->category) === $key)>
                                    {{ $label }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    {{-- Description --}}
                    <div class="md:col-span-2">
                        <label class="{{ $labelClass }}">
                            {{ $t['description'] }}
                        </label>

                        <textarea name="description" rows="6" class="{{ $inputClass }}"
                            placeholder="{{ $isAr ? 'اكتب وصفاً واضحاً ومختصراً للفرصة...' : 'Write a clear and concise description of the opportunity...' }}">{{ old('description', $opportunity->description) }}</textarea>
                    </div>

                </div>
            </div>


            {{-- =========================================================
                FREIGHT INFORMATION
            ========================================================== --}}
            <div class="{{ $sectionClass }}">

                <h2 class="mb-6 text-lg font-bold text-white">
                    {{ $t['freight_section'] }}
                </h2>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                    {{-- Cargo Type --}}
                    <div>
                        <label class="{{ $labelClass }}">
                            {{ $t['cargo_type'] }}
                        </label>

                        <input type="text" name="cargo_type"
                            value="{{ old('cargo_type', $opportunity->cargo_type) }}" class="{{ $inputClass }}"
                            placeholder="{{ $isAr ? 'مثال: مواد غذائية، أثاث، إلكترونيات' : 'e.g. Food, Furniture, Electronics' }}">
                    </div>

                    {{-- Container Type --}}
                    <div>
                        <label class="{{ $labelClass }}">
                            {{ $t['container_type'] }}
                        </label>

                        <input type="text" name="container_type"
                            value="{{ old('container_type', $opportunity->container_type) }}"
                            class="{{ $inputClass }}"
                            placeholder="{{ $isAr ? 'مثال: 20FT / 40FT / LCL' : 'e.g. 20FT / 40FT / LCL' }}">
                    </div>

                    {{-- Origin --}}
                    <div>
                        <label class="{{ $labelClass }}">
                            {{ $t['origin'] }}
                        </label>

                        <select name="origin_port_id" class="{{ $inputClass }}">

                            <option value="">
                                {{ $t['select'] }}
                            </option>

                            @foreach ($ports as $port)
                                <option value="{{ $port->id }}" @selected(old('origin_port_id', $opportunity->origin_port_id) == $port->id)>
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
                        <label class="{{ $labelClass }}">
                            {{ $t['destination'] }}
                        </label>

                        <select name="destination_port_id" class="{{ $inputClass }}">

                            <option value="">
                                {{ $t['select'] }}
                            </option>

                            @foreach ($ports as $port)
                                <option value="{{ $port->id }}" @selected(old('destination_port_id', $opportunity->destination_port_id) == $port->id)>
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


            {{-- =========================================================
                LOCATION
            ========================================================== --}}
            <div class="{{ $sectionClass }}">

                <h2 class="mb-6 text-lg font-bold text-white">
                    {{ $t['location_section'] }}
                </h2>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                    {{-- Country --}}
                    <div>
                        <label class="{{ $labelClass }}">
                            {{ $t['country'] }}
                        </label>

                        <input type="text" name="country" value="{{ old('country', $opportunity->country) }}"
                            class="{{ $inputClass }}">
                    </div>

                    {{-- City --}}
                    <div>
                        <label class="{{ $labelClass }}">
                            {{ $t['city'] }}
                        </label>

                        <input type="text" name="city" value="{{ old('city', $opportunity->city) }}"
                            class="{{ $inputClass }}">
                    </div>

                </div>
            </div>


            {{-- =========================================================
                FINANCIAL INFORMATION
            ========================================================== --}}
            <div class="{{ $sectionClass }}">

                <h2 class="mb-6 text-lg font-bold text-white">
                    {{ $t['financial_section'] }}
                </h2>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-4">

                    {{-- Estimated Value --}}
                    <div>
                        <label class="{{ $labelClass }}">
                            {{ $t['estimated_value'] }}
                        </label>

                        <input type="number" step="0.01" min="0" name="estimated_value"
                            value="{{ old('estimated_value', $opportunity->estimated_value) }}"
                            class="{{ $inputClass }}">
                    </div>

                    {{-- Required Capital --}}
                    <div>
                        <label class="{{ $labelClass }}">
                            {{ $t['required_capital'] }}
                        </label>

                        <input type="number" step="0.01" min="0" name="required_capital"
                            value="{{ old('required_capital', $opportunity->required_capital) }}"
                            class="{{ $inputClass }}">
                    </div>

                    {{-- Minimum Investment --}}
                    <div>
                        <label class="{{ $labelClass }}">
                            {{ $t['min_investment'] }}
                        </label>

                        <input type="number" step="0.01" min="0" name="min_investment"
                            value="{{ old('min_investment', $opportunity->min_investment) }}"
                            class="{{ $inputClass }}">
                    </div>

                    {{-- Currency --}}
                    <div>
                        <label class="{{ $labelClass }}">
                            {{ $t['currency'] }}
                        </label>

                        <select name="currency" class="{{ $inputClass }}">

                            @foreach ($currencies as $currency)
                                <option value="{{ $currency }}" @selected(old('currency', $opportunity->currency ?? 'USD') === $currency)>
                                    {{ $currency }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                </div>
            </div>


            {{-- =========================================================
                COMMISSION
            ========================================================== --}}
            <div class="{{ $sectionClass }}">

                <h2 class="mb-6 text-lg font-bold text-white">
                    {{ $t['commission_section'] }}
                </h2>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                    {{-- Commission Type --}}
                    <div>
                        <label class="{{ $labelClass }}">
                            {{ $t['commission_type'] }} *
                        </label>

                        <select name="commission_type" required class="{{ $inputClass }}">

                            @foreach ($commissionTypes as $key => $label)
                                <option value="{{ $key }}" @selected(old('commission_type', $opportunity->commission_type ?? 'percentage') === $key)>
                                    {{ $label }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    {{-- Commission Rate --}}
                    <div>
                        <label class="{{ $labelClass }}">
                            {{ $t['commission_rate'] }}
                        </label>

                        <input type="number" step="0.01" min="0" name="commission_rate"
                            value="{{ old('commission_rate', $opportunity->commission_rate) }}"
                            class="{{ $inputClass }}">
                    </div>

                </div>
            </div>


            {{-- =========================================================
                SALES PIPELINE
            ========================================================== --}}
            <div class="{{ $sectionClass }}">

                <h2 class="mb-6 text-lg font-bold text-white">
                    {{ $t['pipeline_section'] }}
                </h2>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">

                    {{-- Stage --}}
                    <div>
                        <label class="{{ $labelClass }}">
                            {{ $t['stage'] }} *
                        </label>

                        <select name="stage" required class="{{ $inputClass }}">

                            @foreach ($stages as $key => $label)
                                <option value="{{ $key }}" @selected(old('stage', $opportunity->stage ?? 'new') === $key)>
                                    {{ $label }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    {{-- Probability --}}
                    <div>
                        <label class="{{ $labelClass }}">
                            {{ $t['probability'] }} (%)
                        </label>

                        <input type="number" min="0" max="100" name="probability"
                            value="{{ old('probability', $opportunity->probability ?? 0) }}"
                            class="{{ $inputClass }}">
                    </div>

                    {{-- Expected Close --}}
                    <div>
                        <label class="{{ $labelClass }}">
                            {{ $t['expected_close'] }}
                        </label>

                        <input type="date" name="expected_close_date"
                            value="{{ old(
                                'expected_close_date',
                                $opportunity->expected_close_date
                                    ? \Illuminate\Support\Carbon::parse($opportunity->expected_close_date)->format('Y-m-d')
                                    : '',
                            ) }}"
                            class="{{ $inputClass }}">
                    </div>

                </div>
            </div>


            {{-- =========================================================
                MARKETPLACE
            ========================================================== --}}
            <div class="{{ $sectionClass }}">

                <div class="mb-6 flex flex-col gap-2">
                    <h2 class="text-lg font-bold text-white">
                        {{ $t['marketplace_section'] }}
                    </h2>

                    <p class="text-sm leading-6 text-slate-400">
                        {{ $t['marketplace_notice'] }}
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                    {{-- Visibility --}}
                    <div>
                        <label class="{{ $labelClass }}">
                            {{ $t['visibility'] }} *
                        </label>

                        <select name="visibility" required class="{{ $inputClass }}">

                            @foreach ($visibilities as $key => $label)
                                <option value="{{ $key }}" @selected(old('visibility', $opportunity->visibility ?? 'private') === $key)>
                                    {{ $label }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    {{-- Verification --}}
                    <div>
                        <label class="{{ $labelClass }}">
                            {{ $t['verification_status'] }} *
                        </label>

                        <select name="verification_status" required class="{{ $inputClass }}">

                            @foreach ($verificationStatuses as $key => $label)
                                <option value="{{ $key }}" @selected(old('verification_status', $opportunity->verification_status ?? 'pending') === $key)>
                                    {{ $label }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    {{-- Published At --}}
                    <div>
                        <label class="{{ $labelClass }}">
                            {{ $t['published_at'] }}
                        </label>

                        <input type="datetime-local" name="published_at"
                            value="{{ old(
                                'published_at',
                                $opportunity->published_at
                                    ? \Illuminate\Support\Carbon::parse($opportunity->published_at)->format('Y-m-d\TH:i')
                                    : '',
                            ) }}"
                            class="{{ $inputClass }}">

                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                            {{ $isAr
                                ? 'إذا كانت الفرصة عامة وموثقة ولم يتم تحديد تاريخ، سيتم تعيين تاريخ النشر تلقائياً.'
                                : 'If the opportunity is public and verified without a date, the publication date will be set automatically.' }}
                        </p>
                    </div>

                    {{-- Expires At --}}
                    <div>
                        <label class="{{ $labelClass }}">
                            {{ $t['expires_at'] }}
                        </label>

                        <input type="datetime-local" name="expires_at"
                            value="{{ old(
                                'expires_at',
                                $opportunity->expires_at ? \Illuminate\Support\Carbon::parse($opportunity->expires_at)->format('Y-m-d\TH:i') : '',
                            ) }}"
                            class="{{ $inputClass }}">
                    </div>

                </div>
            </div>


            {{-- =========================================================
                NOTES
            ========================================================== --}}
            <div class="{{ $sectionClass }}">

                <h2 class="mb-6 text-lg font-bold text-white">
                    {{ $t['notes_section'] }}
                </h2>

                <label class="{{ $labelClass }}">
                    {{ $t['notes'] }}
                </label>

                <textarea name="notes" rows="6" class="{{ $inputClass }}"
                    placeholder="{{ $isAr ? 'أضف أي ملاحظات داخلية حول الفرصة...' : 'Add any internal notes about this opportunity...' }}">{{ old('notes', $opportunity->notes) }}</textarea>

            </div>


            {{-- =========================================================
                ACTIONS
            ========================================================== --}}
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                <a href="{{ route('admin.opportunities.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-white/10 bg-slate-800 px-6 py-3 text-sm font-semibold text-slate-300 transition hover:bg-slate-700">
                    {{ $t['cancel'] }}
                </a>

                <button type="submit"
                    class="inline-flex items-center justify-center rounded-xl bg-cyan-400 px-6 py-3 text-sm font-bold text-slate-950 transition hover:bg-cyan-300">
                    {{ $t['save'] }}
                </button>

            </div>

        </form>
    </div>
</x-admin-layout>
