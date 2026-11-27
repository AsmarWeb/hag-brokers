<x-admin-layout>
    @php($isAr = app()->getLocale() === 'ar')
    <div class="min-h-screen bg-slate-950 text-white" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
        <div class="mx-auto max-w-4xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
            <a href="{{ route('admin.deal-documents.index') }}" class="text-sm font-bold text-cyan-300">← {{ __('deal_documents.back') }}</a>
            <div>
                <p class="text-xs font-black uppercase tracking-[0.2em] text-cyan-300">HAG Brokers · Deal Operations</p>
                <h1 class="mt-2 text-3xl font-black">{{ __('deal_documents.new_document') }}</h1>
                <p class="mt-2 text-sm text-slate-400">{{ __('deal_documents.subtitle') }}</p>
            </div>
            <form method="GET" action="{{ route('admin.deal-documents.choose') }}" class="flex flex-col gap-3 sm:flex-row">
                <input type="search" name="search" value="{{ $search }}" placeholder="{{ $isAr ? 'ابحث برقم الصفقة أو اسم العميل' : 'Search deal reference or customer' }}" class="min-w-0 flex-1 rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-sm text-white placeholder:text-slate-500">
                <button class="rounded-xl border border-white/10 px-5 py-3 text-sm font-bold text-slate-200 hover:bg-white/5">{{ $isAr ? 'بحث' : 'Search' }}</button>
            </form>
            <form method="GET" action="{{ route('admin.deal-documents.start') }}" class="space-y-5 rounded-2xl border border-white/10 bg-white/[0.035] p-6">
                <div>
                    <label class="mb-2 block text-sm font-bold text-slate-300">{{ __('deal_documents.deal_reference') }}</label>
                    <select name="deal_id" required class="w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white">
                        <option value="">{{ $isAr ? 'اختر الصفقة' : 'Select a deal' }}</option>
                        @foreach ($deals as $deal)
                            <option value="{{ $deal->id }}">{{ $deal->reference }} · {{ $deal->customer?->company_name }} · {{ ucfirst($deal->status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold text-slate-300">{{ __('deal_documents.type') }}</label>
                    <select name="type" required class="w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white">
                        <option value="ncnda">{{ __('deal_documents.ncnda') }}</option>
                        <option value="fixture_recap">{{ __('deal_documents.fixture_recap') }}</option>
                        <option value="commission_addendum">{{ __('deal_documents.commission_addendum') }} — {{ $isAr ? 'للصفقات المعتمدة فقط' : 'approved deals only' }}</option>
                    </select>
                </div>
                <button class="w-full rounded-xl bg-cyan-400 px-6 py-3 font-black text-slate-950 hover:bg-cyan-300">{{ $isAr ? 'متابعة' : 'Continue' }} →</button>
            </form>
            @if ($deals->isEmpty())
                <p class="rounded-xl border border-amber-300/20 bg-amber-300/5 p-4 text-sm text-amber-100">{{ $isAr ? 'لا توجد صفقات مطابقة. أنشئ صفقة من عرض سعر معتمد أولاً أو غيّر عبارة البحث.' : 'No matching deals. Create a deal from an approved quotation first or change the search.' }}</p>
            @else
                <div class="flex justify-center">{{ $deals->links() }}</div>
            @endif
        </div>
    </div>
</x-admin-layout>
