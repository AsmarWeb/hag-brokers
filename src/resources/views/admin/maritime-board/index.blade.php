@php
    $input = 'w-full rounded-lg border-gray-300 bg-white text-sm text-gray-900 placeholder-gray-400 shadow-sm
                focus:border-cyan-500 focus:ring-cyan-500
                dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500
                dark:focus:border-cyan-400 dark:focus:ring-cyan-400 dark:[color-scheme:dark]';
    $section =
        'overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:shadow-none';
    $head = 'border-b border-gray-100 p-5 dark:border-gray-800';
    $divide = 'divide-y divide-gray-100 dark:divide-gray-800';
    $foot = 'border-t border-gray-100 p-4 dark:border-gray-800';
    $ref = 'text-xs font-black text-cyan-800 dark:text-cyan-300';
    $muted = 'text-sm text-gray-600 dark:text-gray-400';
    $btn = 'rounded-lg bg-slate-900 px-4 py-2 text-sm font-bold text-white transition hover:bg-slate-700
                focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2
                dark:bg-cyan-400 dark:text-slate-950 dark:hover:bg-cyan-300 dark:focus:ring-offset-gray-900';
    $empty = 'p-6 text-sm text-gray-500 dark:text-gray-400';
@endphp

<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-xl font-black text-gray-900 dark:text-white">{{ __('maritime_board.admin_title') }}</h2>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-10 px-4 py-8 sm:px-6 lg:px-8">

        @if (session('status'))
            <div
                class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800
                        dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        @foreach ([['title' => __('maritime_board.cargoes_tab'), 'items' => $cargoes, 'kind' => 'cargo'], ['title' => __('maritime_board.vessels_tab'), 'items' => $vessels, 'kind' => 'vessel']] as $group)
            <section class="{{ $section }}">
                <div class="{{ $head }}">
                    <h3 class="text-lg font-black text-gray-900 dark:text-white">{{ $group['title'] }}</h3>
                </div>

                <div class="{{ $divide }}">
                    @forelse($group['items'] as $item)
                        <article class="grid gap-4 p-5 lg:grid-cols-[minmax(0,1fr)_360px]">
                            <div>
                                <p class="{{ $ref }}">{{ $item->public_reference }} ·
                                    {{ __('maritime_board.' . $item->status) }}</p>
                                <h4 class="mt-2 font-black text-gray-900 dark:text-white">
                                    {{ $group['kind'] === 'cargo' ? $item->cargo_type : $item->vessel_type }} —
                                    {{ $item->company_name }}
                                </h4>
                                <p class="mt-1 {{ $muted }}">{{ $item->contact_name }} · {{ $item->email }} ·
                                    {{ $item->phone }}</p>

                                @if ($group['kind'] === 'cargo')
                                    <p class="mt-2 {{ $muted }}">
                                        {{ $item->loadingPort?->name }} → {{ $item->dischargingPort?->name }} ·
                                        {{ $item->quantity }} MT ·
                                        {{ $item->laycan_from?->format('Y-m-d') }} /
                                        {{ $item->laycan_to?->format('Y-m-d') }}
                                    </p>
                                    <p class="mt-2 {{ $muted }}">{{ $item->description }}</p>
                                @else
                                    <p class="mt-2 {{ $muted }}">
                                        {{ $item->dwt_capacity }} DWT ·
                                        {{ $item->currentPort?->name ?: $item->current_position }} ·
                                        {{ $item->open_date?->format('Y-m-d') }} · {{ $item->preferred_trading_area }}
                                    </p>
                                @endif

                                @if ($item->review_notes)
                                    <p class="mt-2 text-sm text-amber-800 dark:text-amber-300">
                                        {{ $item->review_notes }}</p>
                                @endif
                            </div>

                            <form method="POST"
                                action="{{ route('admin.maritime-board.review', ['kind' => $group['kind'], 'listing' => $item->id]) }}"
                                class="grid content-start gap-2">
                                @csrf
                                @method('PATCH')

                                @if ($group['kind'] === 'cargo')
                                    <label class="grid gap-1 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        {{ __('maritime_board.public_description') }}
                                        <textarea name="public_description" rows="4" maxlength="1000" class="{{ $input }}">{{ old('public_description', $item->public_description) }}</textarea>
                                        <span
                                            class="text-xs font-normal text-gray-500 dark:text-gray-400">{{ __('maritime_board.public_description_hint') }}</span>
                                    </label>
                                @endif

                                <select name="status" class="{{ $input }}">
                                    <option value="published" @selected($item->status === 'published')>
                                        {{ __('maritime_board.published') }}</option>
                                    <option value="pending_review" disabled>{{ __('maritime_board.pending') }}</option>
                                    <option value="rejected" @selected($item->status === 'rejected')>
                                        {{ __('maritime_board.rejected') }}</option>
                                    <option value="closed" @selected($item->status === 'closed')>
                                        {{ __('maritime_board.closed') }}</option>
                                </select>

                                <textarea name="review_notes" rows="2" maxlength="5000" placeholder="{{ __('maritime_board.review_notes') }}"
                                    class="{{ $input }}">{{ $item->review_notes }}</textarea>

                                <button type="submit"
                                    class="{{ $btn }}">{{ __('maritime_board.save_review') }}</button>
                            </form>
                        </article>
                    @empty
                        <div class="{{ $empty }}">{{ __('maritime_board.no_records') }}</div>
                    @endforelse
                </div>

                <div class="{{ $foot }}">{{ $group['items']->links() }}</div>
            </section>
        @endforeach

        <section class="{{ $section }}">
            <div class="{{ $head }}">
                <h3 class="text-lg font-black text-gray-900 dark:text-white">{{ __('maritime_board.inquiries') }}</h3>
            </div>

            <div class="{{ $divide }}">
                @forelse($inquiries as $inquiry)
                    <article class="grid gap-4 p-5 lg:grid-cols-[minmax(0,1fr)_260px]">
                        <div>
                            <p class="{{ $ref }}">{{ $inquiry->public_reference }} ·
                                {{ __('maritime_board.' . $inquiry->status) }}</p>
                            <p class="mt-2 font-bold text-gray-900 dark:text-white">{{ $inquiry->name }} —
                                {{ $inquiry->company_name }}</p>
                            <p class="{{ $muted }}">{{ $inquiry->email }} · {{ $inquiry->phone }}</p>
                            <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">{{ $inquiry->message }}</p>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                {{ $inquiry->cargoListing?->public_reference ?: $inquiry->vesselListing?->public_reference }}
                            </p>
                        </div>

                        <form method="POST" action="{{ route('admin.maritime-board.inquiries.update', $inquiry) }}"
                            class="grid content-start gap-2">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="{{ $input }}">
                                <option value="new" @selected($inquiry->status === 'new')>{{ __('maritime_board.new') }}
                                </option>
                                <option value="contacted" @selected($inquiry->status === 'contacted')>
                                    {{ __('maritime_board.contacted') }}</option>
                                <option value="closed" @selected($inquiry->status === 'closed')>{{ __('maritime_board.closed') }}
                                </option>
                            </select>
                            <button type="submit"
                                class="{{ $btn }}">{{ __('maritime_board.save_review') }}</button>
                        </form>
                    </article>
                @empty
                    <div class="{{ $empty }}">{{ __('maritime_board.no_records') }}</div>
                @endforelse
            </div>

            <div class="{{ $foot }}">{{ $inquiries->links() }}</div>
        </section>
    </div>
</x-admin-layout>
