@php
    $isArDoc = $document->language === 'ar';
    $content = $document->content ?? [];
    $snapshot = $document->financial_snapshot ?? [];
    $commission = $snapshot['commission'] ?? [];
    $agreement = $snapshot['agreement'] ?? [];
    $partyA = $document->parties->firstWhere('role_key', 'party_a');
    $partyB = $document->parties->firstWhere('role_key', 'party_b');
    $typeTitle = match ($document->type) {
        'ncnda' => $isArDoc ? 'اتفاقية عدم الإفصاح وعدم الالتفاف NCNDA' : 'Non-Circumvention and Non-Disclosure Agreement (NCNDA)',
        'fixture_recap' => $isArDoc ? 'ملخص تثبيت الصفقة Fixture Recap' : 'Fixture Recap',
        'commission_addendum' => $isArDoc ? 'ملحق العمولة' : 'Commission Addendum',
        default => $document->title,
    };
    $feeKindLabel = match ($agreement['fee_kind'] ?? '') {
        'percent' => $isArDoc ? 'نسبة مئوية' : 'Percentage',
        'fixed' => $isArDoc ? 'مبلغ ثابت' : 'Fixed fee',
        'spread' => $isArDoc ? 'هامش / فرق' : 'Spread',
        default => '—',
    };
    $feeDetail = match ($agreement['fee_kind'] ?? '') {
        'percent' => number_format(((int) ($agreement['commission_bps'] ?? 0)) / 100, 2) . '%',
        'fixed' => ($agreement['fixed_fee'] ?? '—') . ' ' . ($commission['currency'] ?? ''),
        'spread' => $isArDoc ? 'وفق الشروط المعتمدة' : 'As stated in approved terms',
        default => '—',
    };
@endphp

<section class="printable-document rounded-2xl border border-slate-200 bg-white p-6 text-slate-900 shadow-xl sm:p-10" dir="{{ $isArDoc ? 'rtl' : 'ltr' }}">
    <div class="border-b-2 border-slate-900 pb-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.2em] text-cyan-800">HAG Brokers</p>
                <h2 class="mt-2 text-2xl font-black">{{ $document->title }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $typeTitle }}</p>
            </div>
            <div class="text-end text-xs leading-6 text-slate-600" dir="ltr">
                <p>{{ $document->reference }}</p>
                <p>{{ $document->deal?->reference }}</p>
                <p>{{ optional($document->prepared_at)->format('Y-m-d') ?? $document->created_at?->format('Y-m-d') }}</p>
            </div>
        </div>
    </div>

    <div class="grid gap-4 border-b border-slate-200 py-5 sm:grid-cols-2">
        @foreach ([$partyA, $partyB] as $index => $party)
            <div class="rounded-xl border border-slate-200 p-4">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ $isArDoc ? ($index === 0 ? 'الطرف الأول' : 'الطرف الثاني') : ($index === 0 ? 'Party A' : 'Party B') }}</p>
                <p class="mt-2 font-black">{{ $party?->company_name }}</p>
                <p class="mt-1 text-sm text-slate-700">{{ $party?->contact_name }}</p>
                <p class="text-sm text-slate-600" dir="ltr">{{ $party?->email }}</p>
            </div>
        @endforeach
    </div>

    @if ($document->type === 'ncnda')
        <div class="space-y-5 py-6">
            <div class="grid gap-4 sm:grid-cols-2">
                <div><p class="text-xs font-bold text-slate-500">{{ $isArDoc ? 'العلاقات المحمية' : 'Protected contacts / relationships' }}</p><p class="mt-1 whitespace-pre-line">{{ $content['protected_contacts'] ?? '' }}</p></div>
                <div><p class="text-xs font-bold text-slate-500">{{ $isArDoc ? 'مدة الحماية' : 'Protection term' }}</p><p class="mt-1">{{ $content['term_months'] ?? '' }} {{ $isArDoc ? 'شهراً' : 'months' }}</p></div>
                <div><p class="text-xs font-bold text-slate-500">{{ $isArDoc ? 'النطاق' : 'Territory / scope' }}</p><p class="mt-1">{{ $content['territory'] ?: '—' }}</p></div>
                <div><p class="text-xs font-bold text-slate-500">{{ $isArDoc ? 'تاريخ السريان المقترح' : 'Proposed effective date' }}</p><p class="mt-1">{{ $content['effective_date'] ?: '—' }}</p></div>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm">
                <p class="font-bold">{{ $isArDoc ? 'سجل المراجعة القانونية' : 'Legal review record' }}</p>
                <p class="mt-1">{{ $content['legal_reviewed_by'] ?? '—' }} · {{ $content['legal_reviewed_at'] ?? '—' }}</p>
            </div>
            <div class="border-t border-slate-200 pt-5">
                <h3 class="mb-3 font-black">{{ $isArDoc ? 'نص الاتفاقية' : 'Agreement wording' }}</h3>
                <div class="whitespace-pre-line text-sm leading-8">{{ $content['agreement_text'] ?? '' }}</div>
            </div>
            <p class="rounded-lg bg-amber-50 p-3 text-xs leading-6 text-amber-900">{{ $isArDoc ? 'تتطلب الصياغة مراجعة واعتماداً قانونياً قبل التوقيع.' : 'Legal wording must be reviewed and approved by qualified counsel before signature.' }}</p>
        </div>
    @elseif ($document->type === 'fixture_recap')
        <div class="py-6">
            <h3 class="mb-4 font-black">{{ $isArDoc ? 'ملخص الشحنة والتثبيت' : 'Cargo and fixture summary' }}</h3>
            <div class="grid gap-px overflow-hidden rounded-xl border border-slate-200 bg-slate-200 sm:grid-cols-2">
                @foreach ([
                    [$isArDoc ? 'السلعة' : 'Commodity', $content['commodity'] ?? '—'],
                    [$isArDoc ? 'الكمية' : 'Quantity', trim(($content['quantity'] ?? '').' '.($content['quantity_unit'] ?? ''))],
                    [$isArDoc ? 'ميناء التحميل' : 'Loading port', $content['loading_port'] ?? '—'],
                    [$isArDoc ? 'ميناء التفريغ' : 'Discharge port', $content['discharge_port'] ?? '—'],
                    [$isArDoc ? 'فترة Laycan' : 'Laycan', ($content['laycan_from'] ?? '—').' → '.($content['laycan_to'] ?? '—')],
                    [$isArDoc ? 'السفينة' : 'Vessel', $content['vessel_name'] ?: '—'],
                    [$isArDoc ? 'سعر الشحن' : 'Freight rate', ($content['freight_rate'] ?: '—').' '.($content['freight_currency'] ?? '')],
                    [$isArDoc ? 'الدفع' : 'Payment terms', $content['payment_terms'] ?? '—'],
                    [$isArDoc ? 'غرامة التأخير' : 'Demurrage', ($content['demurrage_rate'] ?: '—').' '.($content['freight_currency'] ?? '')],
                    [$isArDoc ? 'مكافأة التعجيل' : 'Despatch', ($content['despatch_rate'] ?: '—').' '.($content['freight_currency'] ?? '')],
                ] as [$label, $value])
                    <div class="bg-white p-4"><p class="text-xs font-bold text-slate-500">{{ $label }}</p><p class="mt-1 text-sm font-semibold">{{ $value }}</p></div>
                @endforeach
            </div>
            @if (! empty($content['additional_terms']))
                <div class="mt-5"><h3 class="font-black">{{ $isArDoc ? 'شروط إضافية' : 'Additional terms' }}</h3><p class="mt-2 whitespace-pre-line text-sm leading-7">{{ $content['additional_terms'] }}</p></div>
            @endif
        </div>
    @elseif ($document->type === 'commission_addendum')
        <div class="space-y-5 py-6">
            <p class="rounded-lg bg-cyan-50 p-4 text-sm leading-7 text-cyan-950">{{ $isArDoc ? 'يعرض هذا الملحق لقطة من اتفاقية الصفقة المعتمدة وسجل العمولة المرتبط بها. لا ينشئ حساباً جديداً ولا يستبدل الاتفاقية المصدر.' : 'This addendum reproduces the approved deal agreement and its linked commission record. It does not calculate a new fee or replace the source agreement.' }}</p>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl border border-slate-200 p-4"><p class="text-xs font-bold text-slate-500">{{ $isArDoc ? 'نموذج الرسوم' : 'Fee model' }}</p><p class="mt-1 font-black">{{ $feeKindLabel }}</p></div>
                <div class="rounded-xl border border-slate-200 p-4"><p class="text-xs font-bold text-slate-500">{{ $isArDoc ? 'تفاصيل الرسوم' : 'Fee detail' }}</p><p class="mt-1 font-black" dir="ltr">{{ $feeDetail }}</p></div>
                <div class="rounded-xl border border-slate-200 p-4"><p class="text-xs font-bold text-slate-500">{{ $isArDoc ? 'العمولة المعتمدة' : 'Approved commission' }}</p><p class="mt-1 text-xl font-black" dir="ltr">{{ $commission['amount'] ?? '—' }} {{ $commission['currency'] ?? '' }}</p></div>
                <div class="rounded-xl border border-slate-200 p-4"><p class="text-xs font-bold text-slate-500">{{ $isArDoc ? 'قيمة الأساس' : 'Basis amount' }}</p><p class="mt-1 font-bold" dir="ltr">{{ $commission['basis_amount'] ?? '—' }} {{ $commission['currency'] ?? '' }}</p></div>
                <div class="rounded-xl border border-slate-200 p-4"><p class="text-xs font-bold text-slate-500">{{ $isArDoc ? 'الاتفاقية المصدر' : 'Source agreement' }}</p><p class="mt-1 font-bold">#{{ $agreement['id'] ?? '—' }} · v{{ $agreement['version'] ?? '—' }}</p></div>
            </div>
            <div class="border-t border-slate-200 pt-5"><h3 class="font-black">{{ $isArDoc ? 'الشروط المعتمدة' : 'Approved terms' }}</h3><p class="mt-2 whitespace-pre-line text-sm leading-7">{{ $agreement['terms'] ?? '' }}</p></div>
            <p class="break-all text-[10px] text-slate-500">{{ $isArDoc ? 'بصمة الاتفاقية:' : 'Source agreement SHA-256:' }} {{ $agreement['content_sha256'] ?? '' }}</p>
        </div>
    @endif

    <div class="mt-4 border-t-2 border-slate-900 pt-5">
        <h3 class="mb-4 font-black">{{ $isArDoc ? 'اعتماد الأطراف' : 'Party acknowledgement' }}</h3>
        <div class="grid gap-8 sm:grid-cols-2">
            @foreach ([$partyA, $partyB] as $index => $party)
                <div class="min-h-24 border-b border-slate-400 pb-3">
                    <p class="font-bold">{{ $party?->company_name }}</p>
                    <p class="mt-1 text-xs text-slate-600">{{ $isArDoc ? 'الممثل:' : 'Representative:' }} {{ $party?->contact_name }}</p>
                    @if ($party?->status === 'signed')
                        <p class="mt-4 text-sm font-bold">{{ $party->signed_by_name }}</p>
                        <p class="text-xs text-slate-600">{{ $party->signed_by_title }} · {{ $party->signed_at?->format('Y-m-d') }}</p>
                        <p class="mt-2 text-[10px] text-emerald-700">{{ $isArDoc ? 'تم استلام نسخة إثبات التوقيع خارج HAG' : 'Signed-copy evidence received outside HAG' }}</p>
                    @else
                        <p class="mt-8 text-xs text-slate-500">{{ $isArDoc ? 'التوقيع والتاريخ' : 'Signature and date' }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <footer class="mt-7 border-t border-slate-200 pt-3 text-[10px] text-slate-500">
        <p>{{ $isArDoc ? 'رقم المستند:' : 'Document reference:' }} {{ $document->reference }} · {{ $isArDoc ? 'الإصدار:' : 'Template version:' }} {{ $document->template_version }}</p>
        @if ($document->document_sha256)
            <p class="mt-1 break-all">SHA-256: {{ $document->document_sha256 }}</p>
        @endif
    </footer>
</section>
