<x-admin-layout>
    @php
        $isAr = app()->getLocale() === 'ar';
        $typeLabels = [
            'ncnda' => __('deal_documents.ncnda'),
            'fixture_recap' => __('deal_documents.fixture_recap'),
            'commission_addendum' => __('deal_documents.commission_addendum'),
        ];
        $statusLabels = [
            'draft' => __('deal_documents.draft'),
            'ready_for_signature' => __('deal_documents.ready_for_signature'),
            'partially_signed' => __('deal_documents.partially_signed'),
            'signed' => __('deal_documents.fully_signed'),
        ];
    @endphp
    <div class="min-h-screen bg-slate-950 text-white" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
        <header class="border-b border-white/10 bg-gradient-to-br from-slate-900 via-slate-950 to-cyan-950/30 px-4 py-8 sm:px-6 lg:px-8">
            <div class="mx-auto flex max-w-7xl flex-col justify-between gap-5 md:flex-row md:items-end">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.22em] text-cyan-300">HAG Brokers · Deal Operations</p>
                    <h1 class="mt-2 text-3xl font-black tracking-tight">{{ __('deal_documents.title') }}</h1>
                    <p class="mt-2 max-w-2xl text-sm text-slate-400">{{ __('deal_documents.subtitle') }}</p>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="rounded-2xl border border-cyan-300/20 bg-cyan-300/10 px-4 py-3 text-sm text-cyan-100">
                        {{ __('deal_documents.not_esign') }}
                    </div>
                    <a href="{{ route('admin.deal-documents.choose') }}" class="rounded-xl bg-cyan-400 px-5 py-3 text-center text-sm font-black text-slate-950 hover:bg-cyan-300">+ {{ __('deal_documents.new_document') }}</a>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl space-y-6 px-4 py-7 sm:px-6 lg:px-8">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ([['total', $stats['total'], 'cyan'], ['drafts', $stats['draft'], 'slate'], ['awaiting', $stats['awaiting'], 'amber'], ['signed', $stats['signed'], 'emerald']] as [$key, $value, $color])
                    <section class="rounded-2xl border border-white/10 bg-white/[0.035] p-5">
                        <p class="text-sm font-bold text-slate-400">{{ __('deal_documents.'.$key) }}</p>
                        <p class="mt-2 text-3xl font-black tabular-nums text-white">{{ number_format($value) }}</p>
                    </section>
                @endforeach
            </div>

            <form method="GET" class="grid gap-3 rounded-2xl border border-white/10 bg-white/[0.035] p-4 md:grid-cols-[1fr_220px_220px_auto_auto]">
                <input type="search" name="search" value="{{ $filters['search'] }}" placeholder="{{ __('deal_documents.search') }}" class="min-w-0 rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-sm text-white placeholder:text-slate-500">
                <select name="type" class="rounded-xl border border-white/10 bg-slate-900 px-3 py-3 text-sm text-white">
                    <option value="">{{ __('deal_documents.all_types') }}</option>
                    @foreach ($typeLabels as $value => $label)
                        <option value="{{ $value }}" @selected($filters['type'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="status" class="rounded-xl border border-white/10 bg-slate-900 px-3 py-3 text-sm text-white">
                    <option value="">{{ __('deal_documents.all_statuses') }}</option>
                    @foreach ($statusLabels as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="rounded-xl bg-cyan-400 px-5 py-3 text-sm font-black text-slate-950 hover:bg-cyan-300">{{ __('deal_documents.filter') }}</button>
                <a href="{{ route('admin.deal-documents.index') }}" class="rounded-xl border border-white/10 px-5 py-3 text-center text-sm font-bold text-slate-300 hover:bg-white/5">{{ __('deal_documents.reset') }}</a>
            </form>

            <section class="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.035]">
                <div class="border-b border-white/10 px-5 py-4">
                    <h2 class="font-black">{{ __('deal_documents.documents') }}</h2>
                </div>
                @if ($documents->isEmpty())
                    <div class="px-6 py-16 text-center text-slate-400">{{ __('deal_documents.no_documents') }}</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[900px] text-sm">
                            <thead class="bg-slate-900/70 text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-5 py-4 text-start">{{ __('deal_documents.document') }}</th>
                                    <th class="px-5 py-4 text-start">{{ __('deal_documents.type') }}</th>
                                    <th class="px-5 py-4 text-start">{{ __('deal_documents.deal_reference') }}</th>
                                    <th class="px-5 py-4 text-start">{{ __('deal_documents.customer') }}</th>
                                    <th class="px-5 py-4 text-start">{{ __('deal_documents.status') }}</th>
                                    <th class="px-5 py-4 text-start">{{ __('deal_documents.created_at') }}</th>
                                    <th class="px-5 py-4 text-end">{{ __('deal_documents.open') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                @foreach ($documents as $item)
                                    <tr class="transition hover:bg-white/[0.025]">
                                        <td class="px-5 py-4">
                                            <a href="{{ route('admin.deal-documents.show', $item) }}" class="font-bold text-white hover:text-cyan-300">{{ $item->title }}</a>
                                            <p class="mt-1 font-mono text-xs text-slate-500" dir="ltr">{{ $item->reference }}</p>
                                        </td>
                                        <td class="px-5 py-4 text-slate-300">{{ $typeLabels[$item->type] ?? $item->type }}</td>
                                        <td class="px-5 py-4 font-mono text-xs text-cyan-200" dir="ltr">{{ $item->deal?->reference }}</td>
                                        <td class="px-5 py-4 text-slate-300">{{ $item->deal?->customer?->company_name ?? '—' }}</td>
                                        <td class="px-5 py-4">
                                            <span class="inline-flex rounded-full border px-3 py-1 text-xs font-bold {{ $item->status === 'signed' ? 'border-emerald-300/20 bg-emerald-300/10 text-emerald-200' : ($item->status === 'draft' ? 'border-slate-400/20 bg-slate-400/10 text-slate-300' : 'border-amber-300/20 bg-amber-300/10 text-amber-200') }}">{{ $statusLabels[$item->status] ?? $item->status }}</span>
                                        </td>
                                        <td class="px-5 py-4 text-xs text-slate-400">{{ $item->created_at?->format('Y-m-d') }}</td>
                                        <td class="px-5 py-4 text-end"><a href="{{ route('admin.deal-documents.show', $item) }}" class="font-bold text-cyan-300 hover:text-cyan-200">{{ __('deal_documents.open') }} →</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-white/10 px-5 py-4">{{ $documents->links() }}</div>
                @endif
            </section>
        </main>
    </div>
</x-admin-layout>
