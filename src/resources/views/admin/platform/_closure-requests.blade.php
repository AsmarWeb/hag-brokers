@php
    $isArabic = app()->getLocale() === 'ar';
    $statusLabels = [
        'email_pending' => $isArabic ? 'بانتظار التحقق من البريد' : 'Awaiting email verification',
        'owner_confirmation' => $isArabic ? 'بانتظار تأكيد المالك' : 'Awaiting owner confirmation',
        'pending_review' => $isArabic ? 'بانتظار مراجعة الإدارة' : 'Pending administration review',
        'completed' => $isArabic ? 'مكتمل' : 'Completed',
        'rejected' => $isArabic ? 'مرفوض' : 'Rejected',
    ];
@endphp
<section class="hag-broker-card space-y-4">
    <h2 class="text-lg font-bold">{{ $isArabic ? 'طلبات إغلاق مساحة الشركة' : 'Company workspace closure requests' }}</h2>
    @forelse($closureRequests as $closure)
        <article class="space-y-3 rounded-xl border border-slate-200 p-4 dark:border-slate-700">
            <div class="flex flex-wrap justify-between gap-2">
                <strong>{{ $isArabic ? 'الطلب رقم' : 'Request #' }}{{ $closure->id }}</strong>
                <span>{{ $statusLabels[$closure->status] ?? str_replace('_', ' ', $closure->status) }}</span>
            </div>
            <p class="hag-broker-muted">
                {{ $isArabic ? 'المالك رقم' : 'Owner #' }}{{ $closure->owner_id }}
                · {{ $closure->email }}
                · {{ $closure->created_at?->format('Y-m-d H:i') }} UTC
            </p>
            @if($closure->status === 'pending_review')
                <div class="flex flex-wrap gap-3">
                    <form method="POST" action="{{ route('admin.platform.closure.approve', $closure) }}"
                        onsubmit="return confirm('{{ $isArabic ? 'هل توافق على الإغلاق وإلغاء الاشتراك المدفوع في نهاية الفترة وفصل الأعضاء وإغلاق مساحة الشركة؟' : 'Approve closure, cancel the paid subscription at period end, detach all members and close this workspace?' }}')">
                        @csrf
                        <button class="hag-broker-action">
                            {{ $isArabic ? 'الموافقة وإكمال الإغلاق' : 'Approve and complete closure' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.platform.closure.reject', $closure) }}" class="flex flex-1 flex-wrap gap-2">
                        @csrf
                        <textarea name="reason" required minlength="5" maxlength="1000"
                            placeholder="{{ $isArabic ? 'سبب الرفض (مطلوب)' : 'Required rejection reason' }}"
                            class="min-w-48 flex-1 rounded-xl dark:bg-slate-900"></textarea>
                        <button class="rounded-xl border px-4 py-2">
                            {{ $isArabic ? 'رفض الطلب' : 'Reject' }}
                        </button>
                    </form>
                </div>
            @elseif($closure->review_reason)
                <p>{{ $closure->review_reason }}</p>
            @endif
        </article>
    @empty
        <p class="hag-broker-muted">{{ $isArabic ? 'لا توجد طلبات إغلاق لهذه الشركة.' : 'No closure requests for this workspace.' }}</p>
    @endforelse
</section>
