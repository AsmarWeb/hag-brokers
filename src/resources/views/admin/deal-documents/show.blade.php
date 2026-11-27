<x-admin-layout>
    @php
        $isAr = app()->getLocale() === 'ar';
        $statusLabel = $document->status === 'signed'
            ? __('deal_documents.fully_signed')
            : __('deal_documents.'.$document->status);
        $typeLabel = match ($document->type) {
            'ncnda' => __('deal_documents.ncnda'),
            'fixture_recap' => __('deal_documents.fixture_recap'),
            'commission_addendum' => __('deal_documents.commission_addendum'),
            default => $document->type,
        };
    @endphp
    <div class="min-h-screen bg-slate-950 text-white" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
        <header class="border-b border-white/10 bg-gradient-to-br from-slate-900 via-slate-950 to-cyan-950/30 px-4 py-7 sm:px-6 lg:px-8">
            <div class="mx-auto flex max-w-7xl flex-col justify-between gap-5 md:flex-row md:items-end">
                <div>
                    <a href="{{ route('admin.deal-documents.index') }}" class="text-sm font-bold text-cyan-300">← {{ __('deal_documents.back') }}</a>
                    <p class="mt-4 font-mono text-xs text-cyan-300" dir="ltr">{{ $document->reference }}</p>
                    <h1 class="mt-1 text-2xl font-black sm:text-3xl">{{ $document->title }}</h1>
                    <p class="mt-2 text-sm text-slate-400">{{ $typeLabel }} · {{ $document->deal?->reference }} · {{ $document->deal?->customer?->company_name }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full border px-4 py-2 text-xs font-black {{ $document->status === 'signed' ? 'border-emerald-300/20 bg-emerald-300/10 text-emerald-200' : ($document->status === 'draft' ? 'border-slate-400/20 bg-white/5 text-slate-300' : 'border-amber-300/20 bg-amber-300/10 text-amber-200') }}">{{ $statusLabel }}</span>
                    <button type="button" onclick="window.print()" class="rounded-xl bg-cyan-400 px-4 py-2.5 text-sm font-black text-slate-950 hover:bg-cyan-300">{{ __('deal_documents.print') }}</button>
                    <section class="rounded-2xl border p-5 {{ $integrity['valid'] ? 'border-emerald-300/20 bg-emerald-300/5' : 'border-rose-300/25 bg-rose-300/5' }}">
                <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                    <div>
                        <h2 class="font-black {{ $integrity['valid'] ? 'text-emerald-200' : 'text-rose-200' }}">{{ __('deal_documents.integrity') }}</h2>
                        <p class="mt-1 text-sm {{ $integrity['valid'] ? 'text-emerald-100/75' : 'text-rose-100/75' }}">{{ $integrity['valid'] ? __('deal_documents.integrity_verified') : __('deal_documents.integrity_failed') }}</p>
                    </div>
                    <span class="rounded-full px-3 py-1 text-xs font-black {{ $integrity['valid'] ? 'bg-emerald-300/10 text-emerald-200' : 'bg-rose-300/10 text-rose-200' }}">{{ $integrity['valid'] ? 'SHA-256 OK' : 'CHECK REQUIRED' }}</span>
                </div>
                @if (! $integrity['valid'])
                    <div class="mt-3 border-t border-rose-200/10 pt-3">
                        <p class="mb-2 text-xs font-bold text-rose-100">{{ __('deal_documents.integrity_issues') }}</p>
                        <ul class="list-inside list-disc space-y-1 text-xs text-rose-100/80">
                            @foreach ($integrity['issues'] as $issue)
                                <li class="font-mono">{{ $issue }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </section>

            @if ($document->status === 'draft' && $integrity['valid'])
                        <a href="{{ route('admin.deal-documents.edit', $document) }}" class="rounded-xl border border-white/10 px-4 py-2.5 text-sm font-bold text-white hover:bg-white/5">{{ __('deal_documents.edit') }}</a>
                    @endif
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl space-y-7 px-4 py-7 sm:px-6 lg:px-8">
            @if ($document->type === 'fixture_recap')
                <section class="flex flex-col justify-between gap-4 rounded-2xl border border-cyan-300/20 bg-cyan-300/5 p-5 md:flex-row md:items-center">
                    <div>
                        <p class="font-black text-cyan-100">{{ __('deal_execution.handoff_intro') }}</p>
                        <p class="mt-1 text-sm text-cyan-100/70">{{ __('deal_execution.handoff_note') }}</p>
                    </div>
                    @if ($document->status === 'signed' && $integrity['valid'] && $document->deal?->status === 'approved')
                        <a href="{{ route('admin.deal-execution.create', $document) }}" class="rounded-xl bg-cyan-400 px-5 py-3 text-sm font-black text-slate-950 hover:bg-cyan-300">{{ __('deal_execution.open_handoff') }} →</a>
                    @else
                        <span class="text-xs font-bold text-slate-400">{{ __('deal_execution.handoff_not_ready') }}</span>
                    @endif
                </section>
            @endif
            @if (session('success'))
                <div class="rounded-xl border border-emerald-300/20 bg-emerald-300/10 px-4 py-3 text-sm text-emerald-100">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="rounded-xl border border-rose-300/20 bg-rose-300/10 px-4 py-3 text-sm text-rose-100">@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>
            @endif

            @if ($document->status === 'draft')
                <section class="flex flex-col justify-between gap-4 rounded-2xl border border-amber-300/20 bg-amber-300/5 p-5 md:flex-row md:items-center">
                    <div><p class="font-black text-amber-100">{{ __('deal_documents.draft_warning') }}</p><p class="mt-1 text-sm text-amber-100/70">{{ __('deal_documents.legal_review') }}</p></div>
                    @if ($integrity['valid'])
                        <form method="POST" action="{{ route('admin.deal-documents.prepare', $document) }}" onsubmit="return confirm('{{ $isAr ? 'سيتم قفل محتوى المستند وبيانات الأطراف. هل تريد المتابعة؟' : 'The document content and party details will be locked. Continue?' }}')">
                            @csrf
                            <button class="rounded-xl bg-amber-300 px-5 py-3 text-sm font-black text-slate-950 hover:bg-amber-200">{{ __('deal_documents.prepare') }}</button>
                        </form>
                    @else
                        <p class="max-w-md text-sm font-bold text-rose-200">{{ __('deal_documents.integrity_action_blocked') }}</p>
                    @endif
                </section>
            @elseif ($document->status === 'signed')
                <section class="rounded-2xl border border-emerald-300/20 bg-emerald-300/5 p-5 text-sm leading-7 text-emerald-100">{{ __('deal_documents.signed_warning') }}</section>
            @else
                <section class="rounded-2xl border border-cyan-300/20 bg-cyan-300/5 p-5 text-sm leading-7 text-cyan-100">{{ __('deal_documents.prepared_warning') }}<p class="mt-2 text-xs text-cyan-100/70">{{ __('deal_documents.not_esign') }}</p></section>
            @endif

            <section class="grid gap-4 md:grid-cols-3">
                <div class="rounded-2xl border border-white/10 bg-white/[0.035] p-4"><p class="text-xs text-slate-500">{{ __('deal_documents.deal_reference') }}</p><p class="mt-1 font-mono font-bold text-cyan-200" dir="ltr">{{ $document->deal?->reference }}</p></div>
                <div class="rounded-2xl border border-white/10 bg-white/[0.035] p-4"><p class="text-xs text-slate-500">{{ __('deal_documents.version') }}</p><p class="mt-1 text-xl font-black">{{ $document->versions->count() }}</p></div>
                <div class="rounded-2xl border border-white/10 bg-white/[0.035] p-4"><p class="text-xs text-slate-500">{{ __('deal_documents.hash') }}</p><p class="mt-1 break-all font-mono text-xs text-slate-300">{{ $document->document_sha256 ?? __('deal_documents.not_set') }}</p></div>
            </section>

            <section class="space-y-4">
                <div class="flex items-center justify-between"><h2 class="text-xl font-black">{{ __('deal_documents.parties') }}</h2><span class="text-xs text-slate-500">{{ __('deal_documents.not_esign') }}</span></div>
                <div class="grid gap-4 lg:grid-cols-2">
                    @foreach ($document->parties as $party)
                        @php($earlierPartyPending = $document->parties->contains(fn ($candidate) => $candidate->signing_order < $party->signing_order && $candidate->status !== 'signed'))
                        <article class="rounded-2xl border border-white/10 bg-white/[0.035] p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div><h3 class="font-black">{{ $party->company_name }}</h3><p class="mt-1 text-sm text-slate-400">{{ $party->contact_name }} · <span dir="ltr">{{ $party->email }}</span></p></div>
                                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $party->status === 'signed' ? 'bg-emerald-300/10 text-emerald-200' : 'bg-amber-300/10 text-amber-200' }}">{{ $party->status === 'signed' ? __('deal_documents.signature_received') : __('deal_documents.signature_pending') }}</span>
                            </div>
                            @if ($party->status === 'signed')
                                <div class="mt-4 rounded-xl border border-emerald-300/10 bg-emerald-300/5 p-3 text-sm text-emerald-100">
                                    <p>{{ __('deal_documents.signed_by') }}: {{ $party->signed_by_name }} ({{ $party->signed_by_title }})</p>
                                    <p class="mt-1">{{ __('deal_documents.signed_at') }}: {{ $party->signed_at?->format('Y-m-d') }}</p>
                                    <a class="mt-3 inline-flex font-bold text-cyan-200 hover:text-cyan-100" href="{{ route('admin.deal-documents.parties.evidence', [$document, $party]) }}">{{ __('deal_documents.download_evidence') }} →</a>
                                </div>
                            @elseif (in_array($document->status, ['ready_for_signature', 'partially_signed'], true) && $integrity['valid'] && ! $earlierPartyPending)
                                <form method="POST" enctype="multipart/form-data" action="{{ route('admin.deal-documents.parties.signature', [$document, $party]) }}" class="mt-4 space-y-3 border-t border-white/10 pt-4">
                                    @csrf
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <div><label class="mb-1 block text-xs font-bold text-slate-400">{{ __('deal_documents.signed_by') }}</label><input required name="signed_by_name" maxlength="255" class="w-full rounded-lg border border-white/10 bg-slate-900 px-3 py-2 text-sm text-white"></div>
                                        <div><label class="mb-1 block text-xs font-bold text-slate-400">{{ __('deal_documents.signer_title') }}</label><input required name="signed_by_title" maxlength="160" class="w-full rounded-lg border border-white/10 bg-slate-900 px-3 py-2 text-sm text-white"></div>
                                        <div><label class="mb-1 block text-xs font-bold text-slate-400">{{ __('deal_documents.signed_at') }}</label><input type="date" required name="signed_at" max="{{ now()->toDateString() }}" value="{{ now()->toDateString() }}" class="w-full rounded-lg border border-white/10 bg-slate-900 px-3 py-2 text-sm text-white"></div>
                                        <div><label class="mb-1 block text-xs font-bold text-slate-400">{{ __('deal_documents.evidence') }}</label><input type="file" required name="evidence" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-xs text-slate-400"></div>
                                    </div>
                                    <button class="rounded-lg bg-cyan-400 px-4 py-2.5 text-xs font-black text-slate-950 hover:bg-cyan-300">{{ __('deal_documents.upload_evidence') }}</button>
                                </form>
                            @elseif (in_array($document->status, ['ready_for_signature', 'partially_signed'], true) && $earlierPartyPending)
                                <p class="mt-4 border-t border-amber-300/10 pt-4 text-xs font-bold text-amber-200">{{ __('deal_documents.waiting_signing_order') }}</p>
                            @elseif (in_array($document->status, ['ready_for_signature', 'partially_signed'], true))
                                <p class="mt-4 border-t border-rose-300/10 pt-4 text-xs font-bold text-rose-200">{{ __('deal_documents.integrity_action_blocked') }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="space-y-3">
                <div class="flex items-center justify-between"><h2 class="text-xl font-black">{{ __('deal_documents.preview') }}</h2><span class="text-xs text-slate-500">{{ __('deal_documents.legal_review') }}</span></div>
                @include('admin.deal-documents.print')
            </section>

            <div class="grid gap-6 lg:grid-cols-2">
                <section class="rounded-2xl border border-white/10 bg-white/[0.035] p-5">
                    <h2 class="mb-4 text-lg font-black">{{ __('deal_documents.versions') }}</h2>
                    <div class="space-y-3">
                        @forelse ($document->versions as $version)
                            <div class="flex items-center justify-between gap-3 border-b border-white/5 pb-3 last:border-0">
                                <div><p class="font-bold">{{ __('deal_documents.version') }} {{ $version->version }}</p><p class="text-xs text-slate-500">{{ $version->created_at?->format('Y-m-d H:i') }} · {{ $version->actor?->name ?? 'System' }}</p></div>
                                <span class="max-w-[55%] break-all font-mono text-[10px] text-slate-500">{{ $version->snapshot_sha256 }}</span>
                            </div>
                            <details class="rounded-lg border border-white/5 bg-slate-950/50 p-3">
                                <summary class="cursor-pointer text-xs font-bold text-cyan-200">{{ __('deal_documents.view_snapshot') }}</summary>
                                <pre class="mt-3 max-h-80 overflow-auto whitespace-pre-wrap break-words text-[10px] leading-5 text-slate-400">{{ json_encode($version->snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                            </details>
                        @empty
                            <p class="text-sm text-slate-500">—</p>
                        @endforelse
                    </div>
                </section>
                <section class="rounded-2xl border border-white/10 bg-white/[0.035] p-5">
                    <h2 class="mb-4 text-lg font-black">{{ __('deal_documents.audit') }}</h2>
                    <div class="space-y-3">
                        @forelse ($document->events as $event)
                            <div class="border-b border-white/5 pb-3 last:border-0">
                                <div class="flex flex-wrap justify-between gap-2"><p class="text-sm font-bold text-cyan-200">{{ $event->event }}</p><p class="text-xs text-slate-500">{{ $event->created_at?->format('Y-m-d H:i:s') }}</p></div>
                                <p class="mt-1 text-xs text-slate-500">{{ $event->actor?->name ?? 'System' }}</p>
                                <p class="mt-1 break-all font-mono text-[10px] text-slate-600">{{ $event->payload_sha256 }}</p>
                                <details class="mt-2 rounded-lg border border-white/5 bg-slate-950/50 p-3">
                                    <summary class="cursor-pointer text-xs font-bold text-slate-400">{{ __('deal_documents.event_details') }}</summary>
                                    <pre class="mt-3 max-h-48 overflow-auto whitespace-pre-wrap break-words text-[10px] leading-5 text-slate-500">{{ json_encode($event->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                </details>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">{{ __('deal_documents.no_events') }}</p>
                        @endforelse
                    </div>
                </section>
            </div>
        </main>
    </div>

    <style>
        @media print {
            @page { size: A4; margin: 12mm; }
            body { background: white !important; }
            body * { visibility: hidden !important; }
            .printable-document, .printable-document * { visibility: visible !important; }
            .printable-document { position: absolute !important; inset: 0 !important; width: 100% !important; border: 0 !important; box-shadow: none !important; padding: 0 !important; }
        }
    </style>
</x-admin-layout>
