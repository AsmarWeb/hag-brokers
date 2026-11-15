<x-app-layout>
    <div class="mx-auto max-w-3xl space-y-6 p-6">
        <header><p class="hag-broker-muted">HAG BROKERS</p><h1 class="text-2xl font-bold">{{ app()->getLocale() === 'ar' ? 'حالة طلب إغلاق مساحة الشركة' : 'Company workspace closure status' }}</h1></header>
        @if (session('status'))<div class="rounded-xl border p-4">{{ session('status') }}</div>@endif
        <section class="hag-broker-card space-y-3">
            <p><strong>{{ app()->getLocale() === 'ar' ? 'الحالة:' : 'Status:' }}</strong> {{ str_replace('_', ' ', $closure->status) }}</p>
            <p><strong>{{ app()->getLocale() === 'ar' ? 'البريد:' : 'Email:' }}</strong> {{ $closure->email }}</p>
            @if ($closure->status === 'owner_confirmation')
                <p>{{ app()->getLocale() === 'ar' ? 'تم التحقق من البريد. راجع أثر الإغلاق وأكّد بشكل نهائي. ستتوقف مساحة الشركة فور التأكيد.' : 'Email verified. Review the consequences and explicitly confirm. The workspace will be suspended immediately after confirmation.' }}</p>
                <ul class="list-disc space-y-1 pl-6">
                    <li>{{ app()->getLocale() === 'ar' ? 'ستتوقف عمليات مساحة الشركة فوراً.' : 'Company workspace operations will stop immediately.' }}</li>
                    <li>{{ app()->getLocale() === 'ar' ? 'سيتم إرسال الطلب إلى الإدارة للموافقة أو الرفض.' : 'The request will go to platform administration for approval or rejection.' }}</li>
                    <li>{{ app()->getLocale() === 'ar' ? 'عند الموافقة ستُفصل حسابات الأعضاء دون حذف حساباتهم الشخصية.' : 'If approved, members will be detached without deleting their personal accounts.' }}</li>
                    <li>{{ app()->getLocale() === 'ar' ? 'ستُحفظ الفواتير والسجلات والعروض والمحادثات.' : 'Invoices, billing records, offers and conversations will be retained.' }}</li>
                </ul>
                <form method="POST" action="{{ route('company.workspace.closure.confirm') }}" class="pt-3">
                    @csrf
                    <label class="mb-3 flex items-start gap-2"><input type="checkbox" name="confirm_understood" value="1" required><span>{{ app()->getLocale() === 'ar' ? 'أفهم أن التأكيد سيوقف مساحة الشركة فوراً وأطلب الإغلاق.' : 'I understand that confirmation immediately suspends the workspace and I request its closure.' }}</span></label>
                    <button class="hag-broker-action" onclick="return confirm('{{ app()->getLocale() === 'ar' ? 'هل أنت متأكد من تأكيد إغلاق مساحة الشركة؟' : 'Are you sure you want to confirm workspace closure?' }}')">{{ app()->getLocale() === 'ar' ? 'تأكيد الإغلاق النهائي' : 'Confirm final closure' }}</button>
                </form>
            @elseif ($closure->status === 'pending_review')
                <p>{{ app()->getLocale() === 'ar' ? 'تم تعليق مساحة الشركة. الطلب بانتظار مراجعة الإدارة.' : 'The workspace is suspended. The request is awaiting platform administration review.' }}</p>
            @elseif ($closure->status === 'completed')
                <p>{{ app()->getLocale() === 'ar' ? 'تم إغلاق مساحة الشركة. حسابك الشخصي لم يُحذف.' : 'The company workspace is closed. Your personal account has not been deleted.' }}</p>
            @elseif ($closure->status === 'rejected')
                <p>{{ app()->getLocale() === 'ar' ? 'تم رفض طلب الإغلاق.' : 'The closure request was rejected.' }}</p>
                @if($closure->review_reason)<p>{{ $closure->review_reason }}</p>@endif
            @elseif ($closure->status === 'email_pending')
                <p>{{ app()->getLocale() === 'ar' ? 'بانتظار التحقق من البريد الإلكتروني.' : 'Waiting for email verification.' }}</p>
            @else
                <p>{{ str_replace('_', ' ', $closure->status) }}</p>
            @endif
        </section>
    </div>
</x-app-layout>
