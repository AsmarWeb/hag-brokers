<x-admin-layout>
    @php
        $isAr = app()->getLocale() === 'ar';
        $authUser = auth()->user();
        $isAdmin = $authUser?->hasRole('admin|super-admin') ?? false;
        $can = fn (string $permission): bool => $isAdmin || ($authUser?->hasPermission($permission) ?? false);
        $stages = [
            'new' => $isAr ? 'جديدة' : 'New',
            'lead' => $isAr ? 'فرصة أولية' : 'Lead',
            'qualified' => $isAr ? 'مؤهلة' : 'Qualified',
            'proposal' => $isAr ? 'تقديم العرض' : 'Proposal',
            'negotiation' => $isAr ? 'تفاوض' : 'Negotiation',
            'won' => $isAr ? 'ناجحة' : 'Won',
            'lost' => $isAr ? 'غير مكتملة' : 'Lost',
            'pending' => $isAr ? 'قيد الانتظار' : 'Pending',
            'approved' => $isAr ? 'معتمد' : 'Approved',
            'rejected' => $isAr ? 'مرفوض' : 'Rejected',
            'draft' => $isAr ? 'مسودة' : 'Draft',
            'sent' => $isAr ? 'مُرسل' : 'Sent',
            'accepted' => $isAr ? 'مقبول' : 'Accepted',
        ];
    @endphp
    <div class="space-y-6" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
        <header class="hag-broker-hero">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                <div class="max-w-2xl">
                    <p class="hag-broker-eyebrow">HAG BROKERS · {{ $isAr ? 'مساحة أعمال الوساطة' : 'BROKERAGE WORKSPACE' }}</p>
                    <h1 class="mt-3 font-bold">{{ $isAr ? 'من الفرصة إلى إتمام الأعمال' : 'From opportunity to business completion' }}</h1>
                    <p class="mt-3 text-sm leading-7 text-slate-300">{{ $isAr ? 'تابع الفرص التجارية وأطرافها وعروض الأسعار من مكان واحد، وانتقل إلى التنفيذ عندما تصبح الأعمال جاهزة.' : 'Follow commercial opportunities, business contacts and quotations in one place, then move ready business into execution.' }}</p>
                </div>
                @if ($can('opportunities.create'))
                    <a class="hag-broker-action shrink-0" href="{{ route('admin.opportunities.create') }}">+ {{ $isAr ? 'إضافة فرصة تجارية' : 'New commercial opportunity' }}</a>
                @endif
            </div>
        </header>

        <section aria-label="{{ $isAr ? 'مؤشرات الأعمال' : 'Business overview' }}" class="hag-broker-grid">
            @if ($can('opportunities.view'))
                <a href="{{ route('admin.opportunities.index') }}" class="hag-broker-card block transition hover:border-cyan-500">
                    <p class="hag-broker-muted">{{ $isAr ? 'الفرص التجارية' : 'Commercial opportunities' }}</p>
                    <p class="hag-broker-number mt-3">{{ number_format($stats['total_opportunities']) }}</p>
                    <p class="hag-broker-muted mt-2">{{ $isAr ? 'جميع الفرص المسجلة' : 'All recorded opportunities' }}</p>
                </a>
            @endif
            @if ($can('customers.view'))
                <a href="{{ route('admin.customers.index') }}" class="hag-broker-card block transition hover:border-cyan-500">
                    <p class="hag-broker-muted">{{ $isAr ? 'العملاء والشركات' : 'Customers & companies' }}</p>
                    <p class="hag-broker-number mt-3">{{ number_format($stats['total_customers']) }}</p>
                    <p class="hag-broker-muted mt-2">{{ $isAr ? 'سجلات العملاء في المنصة' : 'Customer records in the platform' }}</p>
                </a>
            @endif
            @if ($can('quotations.view'))
                <a href="{{ route('admin.quotations.index') }}" class="hag-broker-card block transition hover:border-cyan-500">
                    <p class="hag-broker-muted">{{ $isAr ? 'عروض الأسعار' : 'Quotations' }}</p>
                    <p class="hag-broker-number mt-3">{{ number_format($stats['total_quotations']) }}</p>
                    <p class="hag-broker-muted mt-2">{{ $isAr ? 'جميع طلبات عروض الأسعار' : 'All quotation requests' }}</p>
                </a>
                <a href="{{ route('admin.quotations.index', ['status' => 'pending']) }}" class="hag-broker-card block transition hover:border-cyan-500">
                    <p class="hag-broker-muted">{{ $isAr ? 'عروض بانتظار الإجراء' : 'Quotations awaiting action' }}</p>
                    <p class="hag-broker-number mt-3 text-cyan-700 dark:text-cyan-400">{{ number_format($stats['pending_quotations']) }}</p>
                    <p class="hag-broker-muted mt-2">{{ $isAr ? 'الطلبات ذات حالة قيد الانتظار' : 'Requests with pending status' }}</p>
                </a>
            @endif
        </section>

        <div class="hag-broker-columns">
            <section class="hag-broker-card">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="font-bold">{{ $isAr ? 'أحدث الفرص التجارية' : 'Latest commercial opportunities' }}</h2>
                    @if ($can('opportunities.view'))
                        <a class="text-sm font-semibold text-cyan-700 dark:text-cyan-400" href="{{ route('admin.opportunities.index') }}">{{ $isAr ? 'عرض الفرص' : 'View opportunities' }}</a>
                    @endif
                </div>
                <p class="hag-broker-muted mt-2">{{ $isAr ? 'آخر الفرص المضافة، مع الطرف المرتبط والمرحلة الحالية.' : 'Recently added opportunities, their related customer and current stage.' }}</p>
                @if ($can('opportunities.view'))
                    @forelse ($brokerRecentOpportunities as $opportunity)
                        <a class="hag-broker-row" href="{{ route('admin.opportunities.show', $opportunity) }}">
                            <div class="min-w-0">
                                <h3 class="break-words text-sm font-semibold">{{ $opportunity->title }}</h3>
                                <p class="hag-broker-muted mt-1">{{ $opportunity->customer?->company_name ?? $opportunity->customer?->name ?? ($isAr ? 'لم يُربط بعميل' : 'No customer assigned') }}</p>
                            </div>
                            <span class="shrink-0 rounded-lg bg-cyan-50 px-3 py-1.5 text-xs font-semibold text-cyan-800 dark:bg-cyan-400/10 dark:text-cyan-300">{{ $stages[$opportunity->stage] ?? $opportunity->stage ?? ($isAr ? 'غير محددة' : 'Unspecified') }}</span>
                        </a>
                    @empty
                        <div class="hag-broker-empty mt-5">
                            <h3 class="text-sm font-bold">{{ $isAr ? 'ابدأ بتسجيل أول فرصة تجارية' : 'Start with your first commercial opportunity' }}</h3>
                            <p class="hag-broker-muted mt-2">{{ $isAr ? 'سجّل الطلب والطرف المعني والتفاصيل المتاحة ليتمكن الفريق من متابعته.' : 'Record the request, business contact and available details so your team can follow up.' }}</p>
                        </div>
                    @endforelse
                @else
                    <p class="hag-broker-muted mt-5">{{ $isAr ? 'لا تملك صلاحية عرض الفرص.' : 'You do not have permission to view opportunities.' }}</p>
                @endif
            </section>

            <aside class="hag-broker-card">
                <h2>{{ $isAr ? 'خطوات عمل الفريق' : 'Team workflow' }}</h2>
                <ol class="mt-3">
                    @foreach ([
                        [$isAr ? 'تسجيل الفرصة' : 'Record the opportunity', $isAr ? 'وثّق الطلب والأطراف والبيانات المتاحة.' : 'Capture the request, parties and available information.'],
                        [$isAr ? 'تأهيل ومتابعة' : 'Qualify & follow up', $isAr ? 'راجع الملاءمة وحدد المسؤول وخطوة المتابعة.' : 'Review suitability and identify the owner and next follow-up.'],
                        [$isAr ? 'عرض وتفاوض' : 'Quote & negotiate', $isAr ? 'وضّح الشروط والأسعار وأساس العمولة قبل الاتفاق.' : 'Clarify terms, pricing and commission basis before agreement.'],
                        [$isAr ? 'توثيق وتنفيذ' : 'Document & execute', $isAr ? 'وثّق الاتفاق المعتمد وتابع المستندات والتنفيذ.' : 'Record the approved agreement and follow documentation and execution.'],
                    ] as $step)
                        <li class="hag-broker-row items-start">
                            <span class="text-sm font-bold text-cyan-700 dark:text-cyan-400">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <div class="flex-1"><h3 class="text-sm font-bold">{{ $step[0] }}</h3><p class="hag-broker-muted mt-1">{{ $step[1] }}</p></div>
                        </li>
                    @endforeach
                </ol>
                <p class="hag-broker-muted mt-4">{{ $isAr ? 'هذه خطوات إرشادية للفريق، وليست حالات تُحدّث تلقائياً.' : 'These are team guidelines; they do not automatically update record statuses.' }}</p>
            </aside>
        </div>

        <section class="hag-broker-card">
            <h2>{{ $isAr ? 'الوصول السريع' : 'Quick access' }}</h2>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ([
                    ['leads.view', 'admin.leads.index', $isAr ? 'العملاء المحتملون' : 'Business leads', $isAr ? 'متابعة الاستفسارات الأولية' : 'Follow initial enquiries'],
                    ['contacts.view', 'admin.contacts.index', $isAr ? 'جهات الاتصال' : 'Business contacts', $isAr ? 'التواصل مع أطراف الأعمال' : 'Connect with business parties'],
                    ['tasks.view', 'admin.tasks.index', $isAr ? 'المهام والمتابعة' : 'Tasks & follow-up', $isAr ? 'تنظيم أعمال الفريق' : 'Organise team activities'],
                    ['documents.view', 'admin.documents.index', $isAr ? 'المستندات' : 'Documents', $isAr ? 'حفظ ومراجعة ملفات الأعمال' : 'Store and review business files'],
                ] as $item)
                    @if ($can($item[0]))
                        <a href="{{ route($item[1]) }}" class="rounded-xl border border-slate-200 p-4 transition hover:border-cyan-500 dark:border-slate-700">
                            <h3 class="text-sm font-bold">{{ $item[2] }}</h3><p class="hag-broker-muted mt-1">{{ $item[3] }}</p>
                        </a>
                    @endif
                @endforeach
            </div>
        </section>

        <div class="hag-broker-columns">
            @if ($can('quotations.view'))
                <section class="hag-broker-card">
                    <h2>{{ $isAr ? 'آخر عروض الأسعار' : 'Recent quotations' }}</h2>
                    @forelse ($recentQuotations as $quotation)
                        <a class="hag-broker-row" href="{{ route('admin.quotations.show', $quotation) }}">
                            <div class="min-w-0"><h3 class="break-words text-sm font-semibold">{{ $quotation->reference_no ?? '#'.$quotation->id }}</h3><p class="hag-broker-muted">{{ $quotation->created_at?->format('Y-m-d') }}</p></div>
                            <span class="hag-broker-muted">{{ $stages[$quotation->status] ?? $quotation->status }}</span>
                        </a>
                    @empty
                        <p class="hag-broker-muted py-6">{{ $isAr ? 'لا توجد عروض أسعار مسجلة بعد.' : 'No quotations recorded yet.' }}</p>
                    @endforelse
                </section>
            @endif
            @if ($can('shipments.view'))
                <section class="hag-broker-card">
                    <div class="flex flex-wrap items-center justify-between gap-3"><h2 class="font-bold">{{ $isAr ? 'تنفيذ الشحنات' : 'Shipment execution' }}</h2><a href="{{ route('admin.shipments.index') }}" class="text-sm font-semibold text-cyan-700 dark:text-cyan-400">{{ $isAr ? 'عرض الشحنات' : 'View shipments' }}</a></div>
                    <dl class="mt-5 space-y-4 text-sm">
                        <div class="flex justify-between gap-3"><dt>{{ $isAr ? 'جميع الشحنات' : 'All shipments' }}</dt><dd class="font-bold">{{ number_format($stats['total_shipments']) }}</dd></div>
                        <div class="flex justify-between gap-3"><dt>{{ $isAr ? 'قيد النقل' : 'In transit' }}</dt><dd class="font-bold">{{ number_format($stats['in_transit']) }}</dd></div>
                        <div class="flex justify-between gap-3"><dt>{{ $isAr ? 'تم تسليمها' : 'Delivered' }}</dt><dd class="font-bold">{{ number_format($stats['delivered']) }}</dd></div>
                    </dl>
                    <p class="hag-broker-muted mt-5">{{ $isAr ? 'قسم التنفيذ اللوجستي للأعمال التي تشمل خدمات الشحن.' : 'Logistics execution for business involving freight services.' }}</p>
                </section>
            @endif
        </div>
    </div>
</x-admin-layout>
