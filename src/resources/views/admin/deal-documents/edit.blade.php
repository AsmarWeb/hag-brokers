<x-admin-layout>
    @php($isAr = app()->getLocale() === 'ar')
    <div class="min-h-screen bg-slate-950 text-white" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
        <div class="mx-auto max-w-6xl px-4 pt-7 sm:px-6 lg:px-8">
            <a href="{{ route('admin.deal-documents.show', $document) }}" class="text-sm font-bold text-cyan-300 hover:text-cyan-200">← {{ __('deal_documents.back') }} · {{ $document->reference }}</a>
            <h1 class="mt-4 text-2xl font-black sm:text-3xl">{{ __('deal_documents.edit') }}</h1>
        </div>
        @include('admin.deal-documents._form', [
            'action' => route('admin.deal-documents.update', $document),
            'deal' => $deal,
            'document' => $document,
            'type' => $type,
            'parties' => $parties,
        ])
    </div>
</x-admin-layout>
