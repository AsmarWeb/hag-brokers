<x-app-layout>
    @php($isAr = app()->getLocale() === 'ar')
    <x-slot name="header">
        <div class="space-y-1">
            <h2 class="text-xl font-extrabold text-slate-900 dark:text-white">{{ $isAr ? 'مراجعة طلب عرض السعر' : 'Review freight quotation' }}</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $isAr ? 'راجع تفاصيل الشحنة قبل تأكيد الإرسال.' : 'Review your shipment details before final submission.' }}</p>
        </div>
    </x-slot>
    <div class="mx-auto max-w-3xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @if($errors->any())<div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-900 dark:bg-red-950 dark:text-red-200"><ul class="list-disc space-y-1 ps-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <dl class="grid gap-4 sm:grid-cols-2">
                <div><dt class="text-xs text-slate-500">{{ $isAr ? 'ميناء الانطلاق' : 'Origin port' }}</dt><dd class="mt-1 font-bold">{{ $origin?->name ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ $isAr ? 'ميناء الوصول' : 'Destination port' }}</dt><dd class="mt-1 font-bold">{{ $destination?->name ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ $isAr ? 'نوع البضائع' : 'Cargo type' }}</dt><dd class="mt-1 font-bold">{{ $data['cargo_type'] }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ $isAr ? 'نوع الحاوية' : 'Container type' }}</dt><dd class="mt-1 font-bold">{{ $data['container_type'] }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ $isAr ? 'الكمية' : 'Quantity' }}</dt><dd class="mt-1 font-bold">{{ $data['quantity'] }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ $isAr ? 'الوزن' : 'Weight' }}</dt><dd class="mt-1 font-bold">{{ $data['weight'] ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ $isAr ? 'تاريخ الشحن المفضل' : 'Preferred shipping date' }}</dt><dd class="mt-1 font-bold">{{ $data['preferred_date'] ?? '—' }}</dd></div>
                @if($data['freight_sailing_id'] ?? null)
                    <div><dt class="text-xs text-slate-500">{{ $isAr ? 'رقم الرحلة' : 'Voyage number' }}</dt><dd class="mt-1 font-bold">{{ $sailing?->voyage_number ?? ($isAr ? 'لم تعد الرحلة متاحة؛ سيعاد التحقق عند التأكيد' : 'Sailing availability will be rechecked on confirmation') }}</dd></div>
                @endif
            </dl>
        </section>
        <div class="flex flex-wrap gap-3">
            <form method="POST" action="{{ route('quotations.resume.submit', ['draft' => request()->route('draft')]) }}">@csrf
                <button class="rounded-xl bg-cyan-500 px-6 py-3 font-bold text-slate-950">{{ $isAr ? 'تأكيد وإرسال الطلب' : 'Confirm and submit request' }}</button>
            </form>
            <a href="{{ route('quotations.create') }}" class="rounded-xl border border-slate-300 px-6 py-3 font-bold text-slate-700 dark:border-slate-700 dark:text-slate-200">{{ $isAr ? 'العودة إلى نموذج الطلب' : 'Back to quote form' }}</a>
        </div>
    </div>
</x-app-layout>
