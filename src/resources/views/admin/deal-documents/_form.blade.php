@php
    $isAr = app()->getLocale() === 'ar';
    $oldParties = old('parties', $parties ?? []);
    $oldContent = old('content', $document?->content ?? []);
    $isEdit = $document !== null;
    $typeLabels = [
        'ncnda' => __('deal_documents.ncnda'),
        'fixture_recap' => __('deal_documents.fixture_recap'),
        'commission_addendum' => __('deal_documents.commission_addendum'),
    ];
@endphp

<div class="min-h-screen bg-slate-950 text-white" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
    <div class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @if ($errors->any())
            <div class="rounded-2xl border border-rose-400/20 bg-rose-500/10 p-5 text-sm text-rose-100">
                <p class="mb-2 font-black">{{ $isAr ? 'يرجى مراجعة الحقول التالية' : 'Please review the following fields' }}</p>
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="{{ $action }}" method="POST" class="space-y-6">
            @csrf
            @if ($isEdit)
                @method('PUT')
                <input type="hidden" name="type" value="{{ $type }}">
            @endif

            <section class="rounded-2xl border border-white/10 bg-white/[0.035] p-5 sm:p-6">
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-cyan-300">HAG Brokers · {{ $deal->reference }}</p>
                        <h2 class="mt-1 text-lg font-black">{{ __('deal_documents.document') }}</h2>
                    </div>
                    <span class="rounded-full border border-cyan-300/20 bg-cyan-300/10 px-3 py-1 text-xs font-bold text-cyan-200">{{ $typeLabels[$type] }}</span>
                </div>

                @if (! $isEdit)
                    <label class="mb-2 block text-sm font-bold text-slate-300" for="type">{{ __('deal_documents.type') }}</label>
                    <select id="type" name="type" class="mb-5 w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white" onchange="window.location='{{ route('admin.deals.documents.create', $deal) }}?type='+this.value">
                        @foreach ($typeLabels as $value => $label)
                            @if ($value !== 'commission_addendum' || $deal->status === 'approved')
                                <option value="{{ $value }}" @selected($type === $value)>{{ $label }}</option>
                            @endif
                        @endforeach
                    </select>
                @endif

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-300" for="title">{{ __('deal_documents.title_field') }}</label>
                        <input id="title" name="title" required maxlength="180" value="{{ old('title', $document?->title ?? $typeLabels[$type]) }}" class="w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-300" for="language">{{ __('deal_documents.language') }}</label>
                        <select id="language" name="language" class="w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white">
                            <option value="en" @selected(old('language', $document?->language ?? 'en') === 'en')>English</option>
                            <option value="ar" @selected(old('language', $document?->language ?? ($isAr ? 'ar' : 'en')) === 'ar')>العربية</option>
                        </select>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-white/10 bg-white/[0.035] p-5 sm:p-6">
                <h2 class="mb-5 text-lg font-black">{{ __('deal_documents.parties') }}</h2>
                <div class="grid gap-5 lg:grid-cols-2">
                    @foreach ([0, 1] as $index)
                        @php($party = $oldParties[$index] ?? [])
                        <div class="space-y-4 rounded-xl border border-white/10 bg-slate-900/60 p-4">
                            <div class="flex items-center justify-between">
                                <h3 class="font-black text-cyan-200">{{ $index === 0 ? __('deal_documents.party_a') : __('deal_documents.party_b') }}</h3>
                                <span class="text-xs text-slate-500">{{ $index + 1 }}</span>
                            </div>
                            <input type="hidden" name="parties[{{ $index }}][role_key]" value="{{ $index === 0 ? 'party_a' : 'party_b' }}">
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-400">{{ __('deal_documents.company_name') }}</label>
                                <input required name="parties[{{ $index }}][company_name]" value="{{ old("parties.$index.company_name", data_get($party, 'company_name')) }}" class="w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2.5 text-sm text-white">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-400">{{ __('deal_documents.contact_name') }}</label>
                                <input required name="parties[{{ $index }}][contact_name]" value="{{ old("parties.$index.contact_name", data_get($party, 'contact_name')) }}" class="w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2.5 text-sm text-white">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-400">{{ __('deal_documents.email') }}</label>
                                <input required type="email" name="parties[{{ $index }}][email]" value="{{ old("parties.$index.email", data_get($party, 'email')) }}" class="w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2.5 text-sm text-white" dir="ltr">
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-2xl border border-white/10 bg-white/[0.035] p-5 sm:p-6">
                <h2 class="mb-2 text-lg font-black">{{ __('deal_documents.document_content') }}</h2>

                @if ($type === 'ncnda')
                    <div class="mb-5 rounded-xl border border-amber-300/20 bg-amber-300/5 p-4 text-sm leading-7 text-amber-100">
                        <strong>{{ __('deal_documents.legal_review') }}:</strong> {{ __('deal_documents.ncnda_notice') }}
                    </div>
                    <div class="grid gap-5 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-bold text-slate-300">{{ __('deal_documents.protected_contacts') }}</label>
                            <textarea name="content[protected_contacts]" required rows="3" class="w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white">{{ old('content.protected_contacts', $oldContent['protected_contacts'] ?? '') }}</textarea>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-300">{{ __('deal_documents.term_months') }}</label>
                            <input type="number" name="content[term_months]" min="1" max="120" required value="{{ old('content.term_months', $oldContent['term_months'] ?? 12) }}" class="w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-300">{{ __('deal_documents.effective_date') }}</label>
                            <input type="date" name="content[effective_date]" value="{{ old('content.effective_date', $oldContent['effective_date'] ?? '') }}" class="w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white">
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-bold text-slate-300">{{ __('deal_documents.territory') }}</label>
                            <input name="content[territory]" value="{{ old('content.territory', $oldContent['territory'] ?? '') }}" class="w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-300">{{ __('deal_documents.legal_reviewed_by') }}</label>
                            <input required name="content[legal_reviewed_by]" maxlength="255" value="{{ old('content.legal_reviewed_by', $oldContent['legal_reviewed_by'] ?? '') }}" class="w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-300">{{ __('deal_documents.legal_reviewed_at') }}</label>
                            <input required type="date" name="content[legal_reviewed_at]" max="{{ now()->toDateString() }}" value="{{ old('content.legal_reviewed_at', $oldContent['legal_reviewed_at'] ?? now()->toDateString()) }}" class="w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white">
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-bold text-slate-300">{{ __('deal_documents.agreement_text') }}</label>
                            <p class="mb-2 text-xs text-slate-500">{{ __('deal_documents.agreement_text_hint') }}</p>
                            <textarea name="content[agreement_text]" required minlength="20" maxlength="15000" rows="12" class="w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white">{{ old('content.agreement_text', $oldContent['agreement_text'] ?? '') }}</textarea>
                        </div>
                    </div>
                @elseif ($type === 'fixture_recap')
                    <div class="grid gap-5 md:grid-cols-2">
                        @foreach ([['commodity','text'],['quantity','number'],['quantity_unit','text'],['loading_port','text'],['discharge_port','text'],['laycan_from','date'],['laycan_to','date'],['vessel_name','text'],['freight_rate','number'],['freight_currency','text'],['freight_rate_basis','text'],['demurrage_rate','number'],['despatch_rate','number']] as [$field,$inputType])
                            <div>
                                <label class="mb-2 block text-sm font-bold text-slate-300">{{ __('deal_documents.'.$field) }}</label>
                                <input type="{{ $inputType }}" step="{{ $inputType === 'number' ? '0.01' : 'any' }}" name="content[{{ $field }}]" value="{{ old('content.'.$field, $oldContent[$field] ?? ($field === 'freight_currency' ? $deal->currency : '')) }}" @if (in_array($field, ['commodity','quantity','quantity_unit','loading_port','discharge_port','laycan_from','laycan_to','freight_currency'])) required @endif class="w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white" dir="{{ $field === 'freight_currency' ? 'ltr' : 'auto' }}">
                            </div>
                        @endforeach
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-bold text-slate-300">{{ __('deal_documents.payment_terms') }}</label>
                            <textarea name="content[payment_terms]" required rows="3" class="w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white">{{ old('content.payment_terms', $oldContent['payment_terms'] ?? '') }}</textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-bold text-slate-300">{{ __('deal_documents.additional_terms') }}</label>
                            <textarea name="content[additional_terms]" rows="4" class="w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white">{{ old('content.additional_terms', $oldContent['additional_terms'] ?? '') }}</textarea>
                        </div>
                    </div>
                @else
                    <div class="rounded-xl border border-cyan-300/20 bg-cyan-300/5 p-5 text-sm leading-7 text-cyan-100">
                        <h3 class="mb-2 font-black">{{ __('deal_documents.commission_snapshot') }}</h3>
                        <p>{{ __('deal_documents.commission_snapshot_notice') }}</p>
                        @if ($deal->status !== 'approved')
                            <p class="mt-3 font-bold text-amber-200">{{ __('deal_documents.no_commission_source') }}</p>
                        @endif
                    </div>
                @endif
            </section>

            <div class="flex flex-wrap items-center justify-end gap-3">
                <a href="{{ $isEdit ? route('admin.deal-documents.show', $document) : route('admin.deals.show', $deal) }}" class="rounded-xl border border-white/10 px-5 py-3 text-sm font-bold text-slate-300 hover:bg-white/5">{{ __('deal_documents.cancel') }}</a>
                <button type="submit" class="rounded-xl bg-cyan-400 px-6 py-3 text-sm font-black text-slate-950 shadow-lg shadow-cyan-400/10 hover:bg-cyan-300">{{ $isEdit ? __('deal_documents.update') : __('deal_documents.create') }}</button>
            </div>
        </form>
    </div>
</div>
