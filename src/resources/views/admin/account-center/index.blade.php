<x-admin-layout>
 @php($ar=app()->getLocale()==='ar')
 <div class="space-y-8 p-6" dir="{{ $ar?'rtl':'ltr' }}">
  <h1 class="text-2xl font-bold">{{ $ar?'مركز الحساب والسياسات':'Account & policy center' }}</h1>
  <p>{{ $ar?'طلبات الحذف تحتاج مراجعة وتنفيذًا منفصلًا. تغيير الحالة إلى قيد المراجعة لا يحذف الحساب أو السجلات التجارية.':'Deletion requests require separate review and fulfillment. Marking a request as reviewing does not delete the account or business records.' }}</p>
  @if(session('success'))<p role="status">{{ session('success') }}</p>@endif
  @if($errors->any())<div role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
  <section class="overflow-x-auto rounded-2xl border p-5">
   <h2 class="mb-4 text-xl font-bold">{{ $ar?'طلبات حذف الحساب':'Account deletion requests' }}</h2>
   @forelse($requests as $item)
    <article class="border-b py-4"><strong>{{ $item->name }}</strong><p>{{ $item->email }}</p><p>{{ $item->created_at }} · {{ $item->status==='pending'?($ar?'بانتظار المراجعة':'Pending review'):($ar?'قيد المراجعة':'Under review') }}</p>
    @if($item->status==='pending')<form method="POST" action="{{ route('admin.account-center.review',$item->id) }}">@csrf<input type="hidden" name="version" value="{{ $item->version }}"><button class="mt-2 rounded-xl bg-slate-800 px-4 py-2 text-white">{{ $ar?'بدء المراجعة':'Start review' }}</button></form>@endif</article>
   @empty<p>{{ $ar?'لا توجد طلبات.':'No requests.' }}</p>@endforelse
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
