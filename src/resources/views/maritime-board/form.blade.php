@php
    $input = 'w-full rounded-xl border-slate-300 bg-white text-slate-900 placeholder-slate-400 shadow-sm
              focus:border-cyan-500 focus:ring-cyan-500
              dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder-slate-500
              dark:focus:border-cyan-400 dark:focus:ring-cyan-400 dark:[color-scheme:dark]';
    $label = 'grid gap-2 text-sm font-bold text-slate-800 dark:text-slate-200';
@endphp

<x-market-layout>
    <section class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:py-14" dir="{{ app()->isLocale('ar') ? 'rtl' : 'ltr' }}">
        <div
            class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-xl
                dark:border-slate-800 dark:bg-slate-900 dark:shadow-none sm:p-9">

            <p class="text-xs font-black tracking-widest text-cyan-700 dark:text-cyan-300">
                {{ __('maritime_board.eyebrow') }}</p>

            <h1 class="mt-3 text-3xl font-black text-slate-950 dark:text-white">
                {{ $kind === 'cargo' ? __('maritime_board.post_cargo') : __('maritime_board.list_vessel') }}
            </h1>

            <p class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-400">{{ __('maritime_board.review_notice') }}
            </p>

            @if ($errors->any())
                <div role="alert"
                    class="mt-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800
                                     dark:border-rose-900 dark:bg-rose-950/50 dark:text-rose-200">
                    <ul class="list-disc ps-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('maritime-board.submit.store', $kind) }}" class="mt-7 space-y-6">
                @csrf

                @if ($kind === 'cargo')
                    <div class="grid gap-5 sm:grid-cols-2">
                        <label class="{{ $label }}">{{ __('maritime_board.cargo_type') }}
                            <select name="cargo_type" required class="{{ $input }}">
                                <option value="">{{ __('maritime_board.select_type') }}</option>
                                @foreach (__('maritime_board.cargo_types') as $key => $text)
                                    <option value="{{ $key }}" @selected(old('cargo_type') === $key)>
                                        {{ $text }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="{{ $label }}">{{ __('maritime_board.quantity') }}
                            <input type="number" name="quantity" min="0.001" step="0.001"
                                value="{{ old('quantity') }}" required class="{{ $input }}">
                        </label>

                        <label class="{{ $label }}">{{ __('maritime_board.loading_port') }}
                            <select name="loading_port_id" required class="{{ $input }}">
                                <option value="">{{ __('maritime_board.select_port') }}</option>
                                @foreach ($ports as $port)
                                    <option value="{{ $port->id }}" @selected((string) old('loading_port_id') === (string) $port->id)>
                                        {{ $port->name }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="{{ $label }}">{{ __('maritime_board.discharging_port') }}
                            <select name="discharging_port_id" required class="{{ $input }}">
                                <option value="">{{ __('maritime_board.select_port') }}</option>
                                @foreach ($ports as $port)
                                    <option value="{{ $port->id }}" @selected((string) old('discharging_port_id') === (string) $port->id)>
                                        {{ $port->name }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="{{ $label }}">{{ __('maritime_board.laycan_from') }}
                            <input type="date" name="laycan_from" value="{{ old('laycan_from') }}" required
                                class="{{ $input }}">
                        </label>

                        <label class="{{ $label }}">{{ __('maritime_board.laycan_to') }}
                            <input type="date" name="laycan_to" value="{{ old('laycan_to') }}" required
                                class="{{ $input }}">
                        </label>

                        <label class="{{ $label }} sm:col-span-2">{{ __('maritime_board.readiness_date') }}
                            <input type="date" name="readiness_date" value="{{ old('readiness_date') }}"
                                class="{{ $input }}">
                        </label>

                        <label class="{{ $label }} sm:col-span-2">{{ __('maritime_board.description') }}
                            <textarea name="description" rows="4" minlength="20" maxlength="5000" required class="{{ $input }}">{{ old('description') }}</textarea>
                        </label>
                    </div>
                @else
                    <div class="grid gap-5 sm:grid-cols-2">
                        <label class="{{ $label }}">{{ __('maritime_board.vessel_type') }}
                            <select name="vessel_type" required class="{{ $input }}">
                                <option value="">{{ __('maritime_board.select_type') }}</option>
                                @foreach (__('maritime_board.vessel_types') as $key => $text)
                                    <option value="{{ $key }}" @selected(old('vessel_type') === $key)>
                                        {{ $text }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="{{ $label }}">{{ __('maritime_board.dwt_capacity') }} (MT)
                            <input type="number" name="dwt_capacity" min="0.01" step="0.01"
                                value="{{ old('dwt_capacity') }}" required class="{{ $input }}">
                        </label>

                        <label class="{{ $label }}">{{ __('maritime_board.current_port') }}
                            <select name="current_port_id" class="{{ $input }}">
                                <option value="">{{ __('maritime_board.select_port') }}</option>
                                @foreach ($ports as $port)
                                    <option value="{{ $port->id }}" @selected((string) old('current_port_id') === (string) $port->id)>
                                        {{ $port->name }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="{{ $label }}">{{ __('maritime_board.current_position') }}
                            <input name="current_position" maxlength="180" value="{{ old('current_position') }}"
                                class="{{ $input }}">
                        </label>

                        <label class="{{ $label }}">{{ __('maritime_board.open_date') }}
                            <input type="date" name="open_date" value="{{ old('open_date') }}" required
                                class="{{ $input }}">
                        </label>

                        <label class="{{ $label }}">{{ __('maritime_board.preferred_trading_area') }}
                            <input name="preferred_trading_area" maxlength="255"
                                value="{{ old('preferred_trading_area') }}" required class="{{ $input }}">
                        </label>
                    </div>
                @endif

                <div class="border-t border-slate-100 pt-6 dark:border-slate-800">
                    <h2 class="text-lg font-black text-slate-900 dark:text-white">
                        {{ __('maritime_board.name') }} &amp; {{ __('maritime_board.company_name') }}
                    </h2>
                    <div class="mt-4 grid gap-5 sm:grid-cols-2">
                        <label class="{{ $label }}">{{ __('maritime_board.company_name') }}
                            <input name="company_name" value="{{ old('company_name') }}" maxlength="200" required
                                class="{{ $input }}">
                        </label>
                        <label class="{{ $label }}">{{ __('maritime_board.name') }}
                            <input name="contact_name" value="{{ old('contact_name') }}" maxlength="150" required
                                class="{{ $input }}">
                        </label>
                        <label class="{{ $label }}">{{ __('maritime_board.email') }}
                            <input type="email" name="email" value="{{ old('email') }}" maxlength="255"
                                required class="{{ $input }}">
                        </label>
                        <label class="{{ $label }}">{{ __('maritime_board.phone') }}
                            <input name="phone" value="{{ old('phone') }}" maxlength="50" required
                                class="{{ $input }}">
                        </label>
                    </div>
                </div>

                <label
                    class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm leading-6 text-slate-700
                          dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-300">
                    <input type="checkbox" name="terms_accepted" value="1" required
                        class="mt-1 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500
                              dark:border-slate-600 dark:bg-slate-800 dark:focus:ring-cyan-400 dark:focus:ring-offset-slate-900">
                    <span>
                        {{ __('maritime_board.terms_accept') }}
                        <a class="font-bold text-cyan-800 underline hover:text-cyan-600 dark:text-cyan-300 dark:hover:text-cyan-200"
                            href="{{ route('legal.show', 'terms') }}" target="_blank"
                            rel="noopener">{{ __('maritime_board.terms_link') }}</a>
                    </span>
                </label>

                <div class="flex flex-wrap gap-3">
                    <button type="submit"
                        class="rounded-xl bg-cyan-300 px-6 py-3 font-black text-slate-950 transition hover:bg-cyan-200
                               focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2
                               dark:bg-cyan-400 dark:hover:bg-cyan-300 dark:focus:ring-offset-slate-900">
                        {{ __('maritime_board.submit_listing') }}
                    </button>
                    <a href="{{ route('maritime-board.index') }}"
                        class="rounded-xl border border-slate-200 px-6 py-3 font-bold text-slate-600 transition hover:bg-slate-50
                          dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                        {{ __('maritime_board.cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </section>
</x-market-layout>
