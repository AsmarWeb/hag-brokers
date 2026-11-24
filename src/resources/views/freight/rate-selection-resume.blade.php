<x-app-layout>
    @php
        $isAr = app()->getLocale() === 'ar';
        $quantity = (int) $pending['data']['quantity'];
        $customerSurcharges = collect($rate->surcharge_items ?? [])->sum(fn (array $item) => (float) ($item['customer_amount'] ?? $item['amount'] ?? 0));
        $customerTotal = ((float) $rate->customer_unit_price * $quantity) + $customerSurcharges;
    @endphp
    <x-slot name="header">
        <div class="space-y-1">
            <h2 class="text-xl font-extrabold text-slate-900 dark:text-white">{{ $isAr ? 'مراجعة اختيار سعر الشحن' : 'Review freight rate selection' }}</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $isAr ? 'سيعاد التحقق من صلاحية السعر والتوافر عند التأكيد.' : 'Rate validity and availability will be checked again when you confirm.' }}</p>
        </div>
    </x-slot>
    <div class="mx-auto max-w-3xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @if($errors->any())<div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-900 dark:bg-red-950 dark:text-red-200"><ul class="list-disc space-y-1 ps-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <dl class="grid gap-4 sm:grid-cols-2">
                <div><dt class="text-xs text-slate-500">{{ $isAr ? 'الناقل' : 'Carrier' }}</dt><dd class="mt-1 font-bold">{{ $rate->carrier?->name ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ $isAr ? 'نوع الحاوية' : 'Container type' }}</dt><dd class="mt-1 font-bold">{{ $rate->container_type }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ $isAr ? 'من' : 'From' }}</dt><dd class="mt-1 font-bold">{{ $rate->originPort?->name ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ $isAr ? 'إلى' : 'To' }}</dt><dd class="mt-1 font-bold">{{ $rate->destinationPort?->name ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ $isAr ? 'الكمية' : 'Quantity' }}</dt><dd class="mt-1 font-bold">{{ $pending['data']['quantity'] }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ $isAr ? 'نوع البضائع' : 'Cargo type' }}</dt><dd class="mt-1 font-bold">{{ $pending['data']['cargo_type'] ?? 'General cargo' }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ $isAr ? 'سعر العميل للوحدة' : 'Customer unit price' }}</dt><dd class="mt-1 font-bold">{{ number_format((float) $rate->customer_unit_price, 2) }} {{ $rate->currency }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ $isAr ? 'حالة السعر' : 'Rate status' }}</dt><dd class="mt-1 font-bold">{{ $rate->status }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ $isAr ? 'إجمالي السعر الحالي للعميل' : 'Current customer total' }}</dt><dd class="mt-1 text-xl font-black">{{ number_format($customerTotal, 2) }} {{ $rate->currency }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ $isAr ? 'صالح حتى' : 'Valid until' }}</dt><dd class="mt-1 font-bold">{{ $rate->expires_at?->setTimezone('Asia/Dubai')->format('Y-m-d H:i') ?? ($isAr ? 'يتطلب التأكيد' : 'Subject to confirmation') }}</dd></div>
            </dl>
            <p class="mt-4 rounded-xl bg-amber-50 p-3 text-sm text-amber-900 dark:bg-amber-950 dark:text-amber-100">{{ $isAr ? 'هذا السعر الحالي للمراجعة فقط. سيعاد التحقق من السعر والتوافر عند التأكيد النهائي، وقد يُرفض الطلب إذا انتهت الصلاحية أو تغيّر التوفر.' : 'This is the current indicative customer price. Price validity and availability are rechecked at final confirmation; the request may be declined if the rate expires or availability changes.' }}</p>
        </section>
        <form method="POST" action="{{ route('freight.rates.resume.submit', ['draft' => request()->route('draft')]) }}">@csrf
            <button class="rounded-xl bg-cyan-500 px-6 py-3 font-bold text-slate-950">{{ $isAr ? 'تأكيد اختيار السعر وإرسال الطلب' : 'Confirm rate selection and submit request' }}</button>
        </form>
    </div>
</x-app-layout>
