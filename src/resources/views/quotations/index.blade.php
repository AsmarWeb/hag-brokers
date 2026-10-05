<x-app-layout>

    @php
        $isAr = app()->getLocale() === 'ar';
        $dir = $isAr ? 'rtl' : 'ltr';

        $t = [
            'quotations' => $isAr ? 'طلبات عروض الأسعار' : 'Quotations',
            'manage_description' => $isAr
                ? 'إدارة طلبات عروض أسعار الشحن البحري الخاصة بك.'
                : 'Manage your sea freight quotation requests.',

            'request_quote' => $isAr ? 'طلب عرض سعر' : 'Request a Quote',

            'freight_quotations' => $isAr ? 'عروض أسعار الشحن' : 'Freight Quotations',
            'hero_description' => $isAr
                ? 'اطلب وأدر عروض أسعار الشحن البحري من خلال منصة واحدة وآمنة.'
                : 'Request and manage sea freight quotations from one secure platform.',

            'your_quotations' => $isAr ? 'عروض أسعارك' : 'Your Quotations',
            'quotation_found' => $isAr ? 'عرض سعر' : 'quotation',
            'quotations_found' => $isAr ? 'عروض أسعار' : 'quotations',
            'found' => $isAr ? 'تم العثور عليها' : 'found',

            'sea_freight' => $isAr ? 'شحن بحري' : 'SEA FREIGHT',

            'reference' => $isAr ? 'المرجع' : 'Reference',
            'cargo' => $isAr ? 'البضائع' : 'Cargo',
            'container' => $isAr ? 'الحاوية' : 'Container',
            'quantity' => $isAr ? 'الكمية' : 'Quantity',
            'preferred_date' => $isAr ? 'التاريخ المفضل' : 'Preferred Date',
            'action' => $isAr ? 'الإجراء' : 'Action',

            'view' => $isAr ? 'عرض' : 'View',
            'quotation' => $isAr ? 'عرض سعر' : 'Quotation',
            'quote' => $isAr ? 'عرض سعر' : 'Quote',
            'view_quotation' => $isAr ? 'عرض طلب السعر' : 'View Quotation',

            'not_specified' => $isAr ? 'غير محدد' : 'Not specified',

            'no_quotations' => $isAr ? 'لا توجد عروض أسعار بعد' : 'No quotations yet',
            'no_quotations_description' => $isAr
                ? 'ابدأ بطلب عرض سعر جديد لإرسال متطلبات الشحن البحري الخاصة بك ومعالجتها.'
                : 'Start a new quotation request to get your sea freight requirements processed.',
        ];
    @endphp


    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div class="flex items-center gap-3">

                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-cyan-400 text-sm font-black text-slate-950 shadow-lg shadow-cyan-400/20">
                    HAG
                </div>

                <div>
                    <h2 class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                        {{ $t['quotations'] }}
                    </h2>

                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ $t['manage_description'] }}
                    </p>
                </div>

            </div>

            <a href="{{ route('quotations.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-cyan-400 px-5 py-3 text-sm font-bold text-slate-950 shadow-lg shadow-cyan-400/20 transition hover:bg-cyan-300">

                @if ($isAr)
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                @else
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                @endif

                {{ $t['request_quote'] }}
            </a>

        </div>
    </x-slot>


    <div class="min-h-screen bg-slate-50 py-8 dark:bg-slate-950">

        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            {{-- Hero --}}
            <div class="relative overflow-hidden rounded-3xl bg-slate-950 p-6 shadow-xl sm:p-8">

                <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-cyan-400/10 blur-3xl"></div>
                <div class="absolute -bottom-32 -left-24 h-72 w-72 rounded-full bg-blue-500/10 blur-3xl"></div>

                <div class="relative">

                    <div class="text-xs font-bold uppercase tracking-[0.2em] text-cyan-400">
                        HAG Brokers
                    </div>

                    <h1 class="mt-3 max-w-2xl text-3xl font-black tracking-tight text-white sm:text-4xl">
                        {{ $t['freight_quotations'] }}
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-400">
                        {{ $t['hero_description'] }}
                    </p>

                </div>

            </div>


            {{-- Success message --}}
            @if (session('success'))
                <div
                    class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300">

                    <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>

                    <div class="text-sm font-semibold">
                        {{ session('success') }}
                    </div>

                </div>
            @endif


            {{-- Quotations table --}}
            <div
                class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">

                <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">

                    <div class="flex items-center justify-between">

                        <div>
                            <h2 class="text-lg font-extrabold text-slate-950 dark:text-white">
                                {{ $t['your_quotations'] }}
                            </h2>

                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">

                                {{ $quotations->total() }}

                                {{ $quotations->total() == 1 ? $t['quotation_found'] : $t['quotations_found'] }}

                                {{ $t['found'] }}

                            </p>
                        </div>

                        <div
                            class="hidden rounded-xl bg-cyan-50 px-3 py-2 text-xs font-bold text-cyan-700 dark:bg-cyan-400/10 dark:text-cyan-400 sm:block">
                            {{ $t['sea_freight'] }}
                        </div>

                    </div>

                </div>


                @if ($quotations->count())

                    {{-- Desktop --}}
                    <div class="hidden overflow-x-auto lg:block">

                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800"
                            dir="{{ $dir }}">

                            <thead class="bg-slate-50 dark:bg-slate-950/50">

                                <tr>

                                    <th
                                        class="px-6 py-4 text-{{ $isAr ? 'right' : 'left' }} text-xs font-bold uppercase tracking-wider text-slate-500">
                                        {{ $t['reference'] }}
                                    </th>

                                    <th
                                        class="px-6 py-4 text-{{ $isAr ? 'right' : 'left' }} text-xs font-bold uppercase tracking-wider text-slate-500">
                                        {{ $t['cargo'] }}
                                    </th>

                                    <th
                                        class="px-6 py-4 text-{{ $isAr ? 'right' : 'left' }} text-xs font-bold uppercase tracking-wider text-slate-500">
                                        {{ $t['container'] }}
                                    </th>

                                    <th
                                        class="px-6 py-4 text-{{ $isAr ? 'right' : 'left' }} text-xs font-bold uppercase tracking-wider text-slate-500">
                                        {{ $t['quantity'] }}
                                    </th>

                                    <th
                                        class="px-6 py-4 text-{{ $isAr ? 'right' : 'left' }} text-xs font-bold uppercase tracking-wider text-slate-500">
                                        {{ $t['preferred_date'] }}
                                    </th>

                                    <th
                                        class="px-6 py-4 text-{{ $isAr ? 'left' : 'right' }} text-xs font-bold uppercase tracking-wider text-slate-500">
                                        {{ $t['action'] }}
                                    </th>

                                </tr>

                            </thead>

                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">

                                @foreach ($quotations as $quotation)
                                    <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-950/50">

                                        <td class="px-6 py-5">

                                            <div class="font-bold text-slate-950 dark:text-white">
                                                {{ $quotation->reference_no ?? 'QT-' . $quotation->id }}
                                            </div>

                                            <div class="mt-1 text-xs text-slate-400">
                                                #{{ $quotation->id }}
                                            </div>

                                        </td>


                                        <td class="px-6 py-5">

                                            <div class="font-semibold text-slate-900 dark:text-white">
                                                {{ $quotation->cargo_type }}
                                            </div>

                                        </td>


                                        <td class="px-6 py-5">

                                            <span
                                                class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                                {{ $quotation->container_type }}
                                            </span>

                                        </td>


                                        <td class="px-6 py-5">

                                            <span class="font-bold text-slate-900 dark:text-white">
                                                {{ $quotation->quantity }}
                                            </span>

                                        </td>


                                        <td class="px-6 py-5 text-sm text-slate-500 dark:text-slate-400">

                                            {{ $quotation->preferred_date
                                                ? \Carbon\Carbon::parse($quotation->preferred_date)->format('d M Y')
                                                : $t['not_specified'] }}

                                        </td>


                                        <td class="px-6 py-5 text-{{ $isAr ? 'left' : 'right' }}">

                                            <a href="{{ route('quotations.show', $quotation) }}"
                                                class="inline-flex items-center gap-2 rounded-xl bg-slate-950 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-cyan-400 hover:text-slate-950 dark:bg-white dark:text-slate-950 dark:hover:bg-cyan-400">

                                                {{ $t['view'] }}

                                                <svg class="h-4 w-4 {{ $isAr ? 'rotate-180' : '' }}" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>

                                            </a>

                                        </td>

                                    </tr>
                                @endforeach

                            </tbody>

                        </table>

                    </div>


                    {{-- Mobile --}}
                    <div class="space-y-4 p-4 lg:hidden">

                        @foreach ($quotations as $quotation)
                            <div class="rounded-2xl border border-slate-200 p-5 dark:border-slate-800">

                                <div class="flex items-start justify-between gap-4">

                                    <div>

                                        <div class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                            {{ $t['quotation'] }}
                                        </div>

                                        <h3 class="mt-1 font-extrabold text-slate-950 dark:text-white">
                                            {{ $quotation->reference_no ?? 'QT-' . $quotation->id }}
                                        </h3>

                                    </div>

                                    <span
                                        class="rounded-lg bg-cyan-50 px-3 py-1.5 text-xs font-bold text-cyan-700 dark:bg-cyan-400/10 dark:text-cyan-400">
                                        {{ $t['quote'] }}
                                    </span>

                                </div>


                                <div class="mt-5 grid grid-cols-2 gap-4">

                                    <div>

                                        <div class="text-xs text-slate-400">
                                            {{ $t['cargo'] }}
                                        </div>

                                        <div class="mt-1 text-sm font-bold text-slate-900 dark:text-white">
                                            {{ $quotation->cargo_type }}
                                        </div>

                                    </div>


                                    <div>

                                        <div class="text-xs text-slate-400">
                                            {{ $t['container'] }}
                                        </div>

                                        <div class="mt-1 text-sm font-bold text-slate-900 dark:text-white">
                                            {{ $quotation->container_type }}
                                        </div>

                                    </div>


                                    <div>

                                        <div class="text-xs text-slate-400">
                                            {{ $t['quantity'] }}
                                        </div>

                                        <div class="mt-1 text-sm font-bold text-slate-900 dark:text-white">
                                            {{ $quotation->quantity }}
                                        </div>

                                    </div>


                                    <div>

                                        <div class="text-xs text-slate-400">
                                            {{ $t['preferred_date'] }}
                                        </div>

                                        <div class="mt-1 text-sm font-bold text-slate-900 dark:text-white">

                                            {{ $quotation->preferred_date ? \Carbon\Carbon::parse($quotation->preferred_date)->format('d M Y') : '—' }}

                                        </div>

                                    </div>

                                </div>


                                <a href="{{ route('quotations.show', $quotation) }}"
                                    class="mt-5 flex w-full items-center justify-center rounded-xl bg-cyan-400 px-4 py-3 text-sm font-bold text-slate-950 transition hover:bg-cyan-300">

                                    {{ $t['view_quotation'] }}

                                </a>

                            </div>
                        @endforeach

                    </div>
                @else
                    <div class="px-6 py-20 text-center">

                        <div
                            class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-600 dark:bg-cyan-400/10 dark:text-cyan-400">

                            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                    d="M9 14l2 2 4-4m5-5v10a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2h5l2-2h5a2 2 0 012 2v1z" />
                            </svg>

                        </div>

                        <h3 class="mt-5 text-lg font-extrabold text-slate-950 dark:text-white">
                            {{ $t['no_quotations'] }}
                        </h3>

                        <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400">
                            {{ $t['no_quotations_description'] }}
                        </p>

                        <a href="{{ route('quotations.create') }}"
                            class="mt-6 inline-flex rounded-xl bg-cyan-400 px-5 py-3 text-sm font-bold text-slate-950 transition hover:bg-cyan-300">

                            {{ $t['request_quote'] }}

                        </a>

                    </div>

                @endif


                @if ($quotations->hasPages())
                    <div class="border-t border-slate-200 px-6 py-5 dark:border-slate-800">

                        {{ $quotations->links() }}

                    </div>
                @endif

            </div>

        </div>

    </div>

</x-app-layout>
