<x-admin-layout>
 @php
  $ar = app()->getLocale() === 'ar';
 @endphp
 <div class="space-y-8 p-6" dir="{{ $ar?'rtl':'ltr' }}">
  <h1 class="text-2xl font-bold">{{ $ar?'مركز الحساب والسياسات':'Account & policy center' }}</h1>
  <p>{{ $ar?'طلبات الحذف تحتاج مراجعة وتنفيذًا منفصلًا. تغيير الحالة إلى قيد المراجعة لا يحذف الحساب أو السجلات التجارية.':'Deletion requests require separate review and fulfillment. Marking a request as reviewing does not delete the account or business records.' }}</p>
  @if(session('success'))<p role="status">{{ session('success') }}</p>@endif
  @if(session('error'))<p role="alert" class="text-red-700">{{ session('error') }}</p>@endif
  @if($errors->any())<div role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
  <section class="overflow-x-auto rounded-2xl border p-5">
   <h2 class="mb-4 text-xl font-bold">{{ $ar?'طلبات حذف الحساب':'Account deletion requests' }}</h2>
   @forelse($requests as $item)
    @php
     $statusLabels = [
      'pending' => $ar ? 'بانتظار المراجعة' : 'Pending review',
      'reviewing' => $ar ? 'قيد المراجعة' : 'Under review',
      'confirmation_sent' => $ar ? 'أُرسلت رسالة التأكيد' : 'Confirmation email sent',
      'email_confirmed' => $ar ? 'أكد المستخدم طلب الحذف' : 'User confirmed deletion',
      'rejected' => $ar ? 'مرفوض' : 'Rejected',
      'completed' => $ar ? 'اكتمل الحذف' : 'Deletion completed',
     ];
    @endphp
    <article class="border-b py-4">
     <strong>{{ $item->name ?: ($ar ? 'حساب محذوف أو غير مرتبط' : 'Deleted or unlinked account') }}</strong>
     <p>{{ $item->email ?: $item->requested_email ?: '—' }}</p>
     <p>{{ $item->created_at }} · {{ $statusLabels[$item->status] ?? $item->status }}</p>

     @if($item->email_sent_at)
      <p class="text-sm text-slate-500">{{ $ar?'تاريخ إرسال التأكيد: ':'Confirmation sent: ' }}{{ $item->email_sent_at }}</p>
     @endif
     @if($item->confirmed_at)
      <p class="text-sm text-slate-500">{{ $ar?'تاريخ التأكيد: ':'Confirmed at: ' }}{{ $item->confirmed_at }}</p>
     @endif
     @if($item->completed_at)
      <p class="text-sm text-slate-500">{{ $ar?'تاريخ اكتمال الحذف: ':'Deletion completed: ' }}{{ $item->completed_at }}</p>
     @endif

     @if($item->status==='pending')
      <form method="POST" action="{{ route('admin.account-center.review',$item->id) }}">
       @csrf
       <input type="hidden" name="version" value="{{ $item->version }}">
       <button class="mt-2 rounded-xl bg-slate-800 px-4 py-2 text-white">{{ $ar?'بدء المراجعة':'Start review' }}</button>
      </form>
     @elseif(in_array($item->status, ['reviewing', 'confirmation_sent'], true))
      <form method="POST" action="{{ route('admin.account-center.send-confirmation',$item->id) }}">
       @csrf
       <button class="mt-2 rounded-xl bg-slate-800 px-4 py-2 text-white">
        {{ $item->status==='confirmation_sent' ? ($ar?'إعادة إرسال رسالة التأكيد':'Resend confirmation email') : ($ar?'إرسال رسالة التأكيد':'Send confirmation email') }}
       </button>
      </form>
     @endif

     @if($item->status === 'email_confirmed')
      <form method="POST" action="{{ route('admin.account-center.complete-deletion', $item->id) }}" class="mt-3 rounded-xl border border-red-300 bg-red-50 p-3">
       @csrf
       <p class="mb-2 text-sm font-medium text-red-800">
        {{ $ar ? 'سيتم حذف حساب المستخدم نهائيًا. لن تُحذف مساحة الشركة إذا كان الحساب مالكًا لها.' : 'This permanently deletes the user account. A company workspace will not be deleted if this user owns it.' }}
       </p>
       <button class="rounded-xl bg-red-700 px-4 py-2 text-white" onclick="return confirm('{{ $ar ? 'تأكيد الحذف النهائي للحساب؟ لا يمكن التراجع عن هذه العملية.' : 'Permanently delete this account? This action cannot be undone.' }}')">
        {{ $ar ? 'تنفيذ الحذف النهائي' : 'Complete permanent deletion' }}
       </button>
      </form>
     @endif

     @if($item->rejection_reason)
      <p class="mt-2 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-800">
       <strong>{{ $ar?'سبب الرفض: ':'Rejection reason: ' }}</strong>{{ $item->rejection_reason }}
      </p>
     @endif

     @if(in_array($item->status, ['pending', 'reviewing', 'confirmation_sent'], true))
      <form method="POST" action="{{ route('admin.account-center.reject',$item->id) }}" class="mt-3 space-y-2 rounded-xl border border-red-200 p-3">
       @csrf
       <input type="hidden" name="version" value="{{ $item->version }}">
       <label class="block text-sm font-medium">
        {{ $ar?'سبب رفض الطلب (إلزامي)':'Reason for rejection (required)' }}
        <textarea name="rejection_reason" class="mt-1 block w-full rounded-xl border p-3 text-slate-900" rows="2" minlength="5" maxlength="2000" required></textarea>
       </label>
       <button class="rounded-xl border border-red-600 px-4 py-2 text-red-700" onclick="return confirm('{{ $ar?'هل تريد رفض طلب الحذف؟':'Reject this deletion request?' }}')">
        {{ $ar?'رفض الطلب':'Reject request' }}
       </button>
      </form>
     @endif
    </article>
   @empty
    <p>{{ $ar?'لا توجد طلبات.':'No requests.' }}</p>
   @endforelse
   {{ $requests->links() }}
  </section>
  <p>{{ $ar?'اكتب النصين العربي والإنجليزي بعد مراجعة سياسة المنصة الفعلية. لا توجد نصوص قانونية منشورة تلقائيًا.':'Write both languages after reviewing the actual platform policy. No legal text is published automatically.' }}</p>
  @foreach(['privacy'=>($ar?'سياسة الخصوصية':'Privacy policy'),'terms'=>($ar?'شروط الاستخدام':'Terms of use')] as $slug=>$label)
   @php($doc=$documents->get($slug))
   <form method="POST" action="{{ route('admin.account-center.legal',$slug) }}" class="space-y-4 rounded-2xl border p-5">@csrf @method('PUT')
    <h2 class="text-xl font-bold">{{ $label }}</h2>
    <input type="hidden" name="version" value="{{ $doc->version??0 }}">
    @foreach(['ar','en'] as $lang)
     <label class="block">{{ $lang==='ar'?'العنوان بالعربية':'English title' }}<input class="block w-full rounded-xl border p-3 text-slate-900" name="title_{{ $lang }}" value="{{ $doc->{'title_'.$lang}??'' }}" required maxlength="255"></label>
     <label class="block">{{ $lang==='ar'?'النص بالعربية':'English content' }}<textarea class="block w-full rounded-xl border p-3 text-slate-900" rows="12" name="body_{{ $lang }}" required maxlength="40000">{{ $doc->{'body_'.$lang}??'' }}</textarea></label>
    @endforeach
    <label class="block"><input type="hidden" name="published" value="0"><input type="checkbox" name="published" value="1" @checked($doc->published??false)> {{ $ar?'نشر النصين بعد مراجعتهما':'Publish both languages after review' }}</label>
    <button class="rounded-xl bg-slate-800 px-5 py-3 text-white">{{ $ar?'حفظ':'Save' }}</button>
   </form>
  @endforeach
 </div>
</x-admin-layout>
