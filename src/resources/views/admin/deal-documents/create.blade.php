<x-admin-layout>
    @php($isAr = app()->getLocale() === 'ar')
    <div class="min-h-screen bg-slate-950 text-white" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
        <div class="mx-auto max-w-6xl px-4 pt-7 sm:px-6 lg:px-8">
            <a href="{{ route('admin.deals.show', $deal) }}" class="text-sm font-bold text-cyan-300 hover:text-cyan-200">← {{ __('deal_documents.back') }} · {{ $deal->reference }}</a>
            <h1 class="mt-4 text-2xl font-black sm:text-3xl">{{ __('deal_documents.new_document') }}</h1>
            <p class="mt-1 text-sm text-slate-400">{{ __('deal_documents.subtitle') }}</p>
        </div>
        @include('admin.deal-documents._form', [
            'action' => route('admin.deals.documents.store', $deal),
            'deal' => $deal,
            'document' => null,
            'type' => $type,
            'parties' => $parties,
        ])
    </div>
</x-admin-layout>
