<x-admin-layout>
    @php $isAr = app()->getLocale() === 'ar'; @endphp
    <div class="mx-auto max-w-5xl space-y-6" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-bold text-cyan-700 dark:text-cyan-400">HAG Brokers</p>
                <h1 class="mt-2 font-bold text-slate-900 dark:text-white">{{ $port->name }}</h1>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ $isAr ? 'تفاصيل الميناء المستخدم في مسارات الشحن وعروض الأسعار.' : 'Port details used in shipping routes and quotations.' }}</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.ports.index') }}" class="rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold dark:border-slate-700">{{ $isAr ? 'العودة للموانئ' : 'Back to ports' }}</a>
                <a href="{{ route('admin.ports.edit', $port) }}" class="rounded-xl bg-cyan-400 px-4 py-3 text-sm font-bold text-slate-950 hover:bg-cyan-300">{{ $isAr ? 'تعديل الميناء' : 'Edit port' }}</a>
            </div>
        </div>
        <dl class="grid gap-5 rounded-2xl border border-slate-200 bg-white p-6 sm:grid-cols-2 dark:border-slate-800 dark:bg-slate-900">
            @foreach ([
                [$isAr ? 'الدولة' : 'Country', $port->country],
                [$isAr ? 'رمز الدولة' : 'Country code', $port->country_code],
                [$isAr ? 'رمز الميناء' : 'Port code', $port->port_code],
                ['UN/LOCODE', $port->unlocode],
                [$isAr ? 'المنطقة الزمنية' : 'Timezone', $port->timezone],
                [$isAr ? 'خط العرض' : 'Latitude', $port->latitude],
                [$isAr ? 'خط الطول' : 'Longitude', $port->longitude],
                [$isAr ? 'الحالة' : 'Status', $port->is_active ? ($isAr ? 'نشط' : 'Active') : ($isAr ? 'غير نشط' : 'Inactive')],
            ] as [$label, $value])
                <div><dt class="text-xs text-slate-600 dark:text-slate-400">{{ $label }}</dt><dd class="mt-2 break-words text-sm font-semibold text-slate-900 dark:text-white">{{ $value !== null && $value !== '' ? $value : '—' }}</dd></div>
            @endforeach
        </dl>
    </div>
</x-admin-layout>
