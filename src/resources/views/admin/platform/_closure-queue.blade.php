@php($isArabic = app()->getLocale() === 'ar')
<section class="hag-broker-card space-y-3">
    <h2 class="text-lg font-bold">{{ $isArabic ? 'طلبات إغلاق مساحات الشركات بانتظار المراجعة' : 'Pending workspace closure reviews' }}</h2>
    @forelse($closureQueue as $closure)
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 py-3 dark:border-slate-700">
            <div>
                <strong>{{ \App\Models\Customer::find($closure->customer_id)?->company_name }}</strong>
                <p class="hag-broker-muted">{{ $closure->email }} · {{ $isArabic ? 'الطلب رقم' : 'Request #' }}{{ $closure->id }}</p>
            </div>
            <a class="hag-broker-action" href="{{ route('admin.platform.show', $closure->company_workspace_id) }}">
                {{ $isArabic ? 'مراجعة الطلب' : 'Review request' }}
            </a>
        </div>
    @empty
        <p class="hag-broker-muted">{{ $isArabic ? 'لا توجد طلبات إغلاق بانتظار المراجعة.' : 'No workspace closure requests are waiting for review.' }}</p>
    @endforelse
</section>
