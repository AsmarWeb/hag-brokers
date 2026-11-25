<x-market-layout>
<section class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:py-14" dir="{{ app()->isLocale('ar') ? 'rtl' : 'ltr' }}">
    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-xl sm:p-9">
        <p class="text-xs font-black tracking-widest text-cyan-700">{{ __('maritime_board.eyebrow') }} · {{ $listing->public_reference }}</p>
        <h1 class="mt-3 text-3xl font-black text-slate-950">{{ __('maritime_board.inquiry_title') }}</h1>
        <p class="mt-3 text-sm leading-7 text-slate-600">{{ __('maritime_board.inquiry_intro') }}</p>
        <form method="POST" action="{{ route('maritime-board.inquiry.store', ['kind' => $kind, 'reference' => $listing->public_reference]) }}" class="mt-7 space-y-5">
            @csrf
            @if($errors->any())<div role="alert" class="rounded-xl bg-rose-50 p-4 text-sm text-rose-800">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
            <div class="grid gap-5 sm:grid-cols-2">
                <label class="grid gap-2 text-sm font-bold">{{ __('maritime_board.name') }}<input name="name" value="{{ old('name') }}" required maxlength="150" class="rounded-xl border-slate-300"></label>
                <label class="grid gap-2 text-sm font-bold">{{ __('maritime_board.company_name') }}<input name="company_name" value="{{ old('company_name') }}" required maxlength="200" class="rounded-xl border-slate-300"></label>
                <label class="grid gap-2 text-sm font-bold">{{ __('maritime_board.email') }}<input type="email" name="email" value="{{ old('email') }}" required maxlength="255" class="rounded-xl border-slate-300"></label>
                <label class="grid gap-2 text-sm font-bold">{{ __('maritime_board.phone') }}<input name="phone" value="{{ old('phone') }}" required maxlength="50" class="rounded-xl border-slate-300"></label>
            </div>
            <label class="grid gap-2 text-sm font-bold">{{ __('maritime_board.message') }}<textarea name="message" rows="5" minlength="20" maxlength="4000" required class="rounded-xl border-slate-300">{{ old('message') }}</textarea></label>
            <label class="flex items-start gap-3 rounded-xl bg-slate-50 p-4 text-sm leading-6 text-slate-700"><input type="checkbox" name="terms_accepted" value="1" required class="mt-1 rounded border-slate-300"><span>{{ __('maritime_board.terms_accept') }} <a class="font-bold text-cyan-800 underline" href="{{ route('legal.show', 'terms') }}" target="_blank" rel="noopener">{{ __('maritime_board.terms_link') }}</a></span></label>
            <div class="flex flex-wrap gap-3"><button class="rounded-xl bg-cyan-300 px-6 py-3 font-black text-slate-950 hover:bg-cyan-200">{{ __('maritime_board.submit') }}</button><a href="{{ route('maritime-board.index', ['tab' => $kind === 'cargo' ? 'cargoes' : 'vessels']) }}" class="rounded-xl border border-slate-200 px-6 py-3 font-bold text-slate-600">{{ __('maritime_board.cancel') }}</a></div>
        </form>
    </div>
</section>
</x-market-layout>
