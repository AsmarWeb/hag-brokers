<x-admin-layout>
<x-slot name="header"><h2 class="text-xl font-bold">{{ app()->isLocale('ar') ? 'استوديو تطبيق HAG Brokers' : 'HAG Brokers App Studio' }}</h2></x-slot>
@php($ar = app()->isLocale('ar'))
<div class="mx-auto max-w-5xl space-y-6 px-4 py-8" dir="{{ $ar ? 'rtl' : 'ltr' }}">
<p>{{ $ar ? 'تحكم بمحتوى الترحيب والرئيسية. تظهر التعديلات عند فتح التطبيق أو تحديث الرئيسية. اللغة والمظهر اختيارات شخصية للمستخدم. الفرص تخضع لصلاحيات وحالات النشر الحالية.' : 'Manage welcome and home content. Updates appear when opening the app or refreshing Home. Language and appearance remain personal preferences. Existing marketplace permissions and visibility still apply.' }}</p>
<a href="{{ route('admin.settings.edit') }}" class="underline">{{ $ar ? 'إعدادات المنصة' : 'Platform settings' }}</a>
@if(session('success'))<p role="status">{{ session('success') }}</p>@endif
@if($errors->any())<ul class="text-red-500">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
<form method="POST" enctype="multipart/form-data" action="{{ route('admin.mobile-app.update') }}" class="space-y-6">
@csrf @method('PUT')
<input type="hidden" name="version" value="{{ $settings['version'] }}">
<div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
<label class="block font-bold" for="app_name">{{ $ar ? 'اسم العرض في رأس التطبيق' : 'Display name in app header' }}</label>
<input class="mt-3 w-full rounded-xl dark:bg-slate-800" id="app_name" name="app_name" maxlength="40" required value="{{ old('app_name', $settings['content']['app_name']) }}">
<p class="mt-2 text-sm">{{ $ar ? 'اسم التطبيق تحت الأيقونة يتطلب تحديث نسخة التطبيق؛ هذا الحقل يغيّر الاسم داخل الواجهة.' : 'The installed app icon name requires a new app release; this changes the in-app header.' }}</p>
</div>
@foreach(['welcome' => ($ar ? 'شاشة الترحيب' : 'Welcome screen'), 'home' => ($ar ? 'الصفحة الرئيسية' : 'Home screen')] as $section => $label)
<section class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
<h3 class="mb-4 text-xl font-bold">{{ $label }}</h3>
<div class="grid gap-5 md:grid-cols-2">
@foreach(['ar' => 'العربية', 'en' => 'English'] as $lang => $language)
<div dir="{{ $lang === 'ar' ? 'rtl' : 'ltr' }}">
@foreach(['title' => ($ar ? 'العنوان' : 'Title'), 'body' => ($ar ? 'الوصف' : 'Description')] as $part => $partLabel)
@php($key = $section.'_'.$part.'_'.$lang)
<label for="{{ $key }}" class="mb-2 mt-3 block">{{ $partLabel }} — {{ $language }}</label>
<textarea id="{{ $key }}" name="{{ $key }}" maxlength="{{ $part === 'title' ? 100 : 240 }}" rows="3" class="w-full rounded-xl dark:bg-slate-800" @required($part === 'title')>{{ old($key, $settings['content'][$key]) }}</textarea>
@endforeach
</div>
@endforeach
</div>
@php($imageKey = $section.'_image')
<label for="{{ $imageKey }}" class="mb-2 mt-5 block">{{ $ar ? 'الصورة: JPG / PNG / WebP، حتى 3 MB. استخدم صورة طولية للترحيب وعريضة للرئيسية.' : 'Image: JPG / PNG / WebP, up to 3 MB. Portrait for welcome, landscape for home.' }}</label>
<input id="{{ $imageKey }}" type="file" name="{{ $imageKey }}" accept="image/jpeg,image/png,image/webp">
@if($settings['content'][$imageKey])
<img class="my-3 h-48 rounded-xl object-cover" src="{{ Storage::disk('public')->url($settings['content'][$imageKey]) }}" alt="{{ $label }}">
<label class="block"><input type="checkbox" name="remove_{{ $imageKey }}" value="1"> {{ $ar ? 'العودة إلى الصورة الافتراضية' : 'Use default image' }}</label>
@else
<img class="my-3 h-48 rounded-xl object-cover" src="{{ asset('mobile/maritime-welcome.jpg') }}" alt="{{ $label }}">
@endif
</section>
@endforeach
<section class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
<h3 class="mb-4 text-xl font-bold">{{ $ar ? 'التخصيص' : 'Customization' }}</h3>
@foreach(['welcome_enabled' => ($ar ? 'عرض الترحيب عند أول استخدام' : 'Show first-use welcome'), 'show_marine' => ($ar ? 'عرض قسم الفرص البحرية' : 'Show marine opportunities')] as $key => $label)
<input type="hidden" name="{{ $key }}" value="0"><label class="mb-3 block"><input type="checkbox" name="{{ $key }}" value="1" @checked(old($key,$settings['content'][$key]))> {{ $label }}</label>
@endforeach
<p class="mb-3">{{ $ar ? 'القطاعات الظاهرة في شريط الرئيسية؛ لا يغيّر هذا تصنيف الفرص أو صلاحيات نشرها.' : 'Home category shortcuts; this does not change listing taxonomy or publishing permissions.' }}</p>
<div class="grid gap-3 md:grid-cols-3">
@foreach($categories as $category)
<label><input type="checkbox" name="categories[]" value="{{ $category }}" @checked(in_array($category,old('categories',$settings['content']['categories'])))> {{ __('trade.categories.'.$category) }}</label>
@endforeach
</div>
@foreach(['ar'=>'العربية','en'=>'English'] as $lang=>$language)
<label for="announcement_{{ $lang }}" class="mb-2 mt-5 block">{{ $ar ? 'رسالة اختيارية في الرئيسية' : 'Optional home announcement' }} — {{ $language }}</label>
<textarea class="w-full rounded-xl dark:bg-slate-800" id="announcement_{{ $lang }}" name="announcement_{{ $lang }}" maxlength="240">{{ old('announcement_'.$lang,$settings['content']['announcement_'.$lang]) }}</textarea>
@endforeach
</section>
<button class="rounded-xl bg-slate-900 px-7 py-4 font-bold text-white dark:bg-slate-700" type="submit">{{ $ar ? 'حفظ محتوى التطبيق' : 'Save app content' }}</button>
</form>
</div>
</x-admin-layout>
