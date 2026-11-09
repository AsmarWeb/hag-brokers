<x-admin-layout>
    <section class="mx-auto max-w-7xl space-y-6">
        <header class="hag-broker-hero">
            <a class="text-sm font-semibold text-cyan-200 underline underline-offset-4" href="{{ route('admin.freight-sailings.index') }}">{{ __('sailings.admin_title') }}</a>
            <h1 class="mt-3 font-black">{{ __('sailing_import.title') }}</h1>
            <p class="mt-2 max-w-2xl text-sm leading-relaxed text-slate-200">{{ __('sailing_import.intro') }}</p>
        </header>

        <ol class="grid grid-cols-1 gap-2 sm:grid-cols-3">
            @foreach(['template', 'preview', 'publish_step'] as $step)
                @php($current = $step === (isset($plan) ? 'publish_step' : 'preview'))
                <li @if($current) aria-current="step" @endif class="hag-broker-card flex items-center gap-3 {{ $current ? 'ring-2 ring-cyan-600 dark:ring-cyan-400' : '' }}">
                    <span aria-hidden="true" class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-cyan-100 text-sm font-black text-cyan-950 dark:bg-cyan-950 dark:text-cyan-200">{{ $loop->iteration }}</span>
                    <span class="text-sm font-semibold">{{ __('sailing_import.'.$step) }}</span>
                </li>
            @endforeach
        </ol>

        <div class="grid items-start gap-4 lg:grid-cols-3">
            <div class="hag-broker-card space-y-4 lg:col-span-2">
                <h2>{{ __('sailing_import.file') }}</h2>
                @if($errors->any())
                    <ul role="alert" class="space-y-2 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-950 dark:border-rose-800 dark:bg-rose-950 dark:text-rose-200">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                @endif
                <form method="POST" enctype="multipart/form-data" action="{{ route('admin.freight-sailings.import.preview') }}" id="sailing-upload-form" class="space-y-4">
                    @csrf
                    <label id="sailing-drop-zone" for="sailing-upload-file" class="relative flex min-h-44 cursor-pointer flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-cyan-700 bg-cyan-50 p-5 text-center text-slate-900 focus-within:ring-2 focus-within:ring-cyan-600 dark:border-cyan-500 dark:bg-slate-950 dark:text-slate-100">
                        <svg aria-hidden="true" class="h-8 w-8 text-cyan-800 dark:text-cyan-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m-4 4 4-4 4 4M5 15v5h14v-5"/>
                        </svg>
                        <span class="font-bold">{{ __('sailing_import.drop_file') }}</span>
                        <span id="sailing-upload-hint" class="hag-broker-muted">{{ __('sailing_import.file_limits') }}</span>
                        <input id="sailing-upload-file" class="sr-only" type="file" name="schedule" accept=".csv,text/csv" aria-describedby="sailing-upload-hint" required>
                    </label>
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <p id="sailing-file-name" role="status" class="min-w-0 break-all text-sm font-semibold"></p>
                        <button id="sailing-file-clear" type="button" hidden class="text-sm font-semibold underline">{{ __('sailing_import.remove_file') }}</button>
                    </div>
                    <p id="sailing-file-error" role="alert" class="text-sm font-semibold text-rose-800 dark:text-rose-300"></p>
                    <button class="hag-broker-action">{{ __('sailing_import.preview') }}</button>
                </form>
            </div>

            <aside class="hag-broker-card space-y-3">
                <h2>{{ __('sailing_import.guide') }}</h2>
                <p class="hag-broker-muted">{{ __('sailing_import.instructions') }}</p>
                <p class="hag-broker-muted">{{ __('sailing_import.format') }}</p>
                <a class="inline-block text-sm font-bold text-cyan-800 underline dark:text-cyan-300" href="{{ route('admin.freight-sailings.import.template') }}">{{ __('sailing_import.template') }}</a>
                <nav aria-label="{{ __('sailing_import.guide') }}" class="flex flex-wrap gap-3 border-t border-slate-200 pt-3 text-sm font-semibold dark:border-slate-700">
                    @foreach(['carriers', 'vessels', 'ports'] as $catalog)
                        <a class="underline" href="{{ route('admin.'.$catalog.'.index') }}">{{ __('sailing_import.'.$catalog) }}</a>
                    @endforeach
                </nav>
            </aside>
        </div>

        @isset($plan)
            @php($counts = collect($plan)->countBy('action'))
            <section class="space-y-4" aria-labelledby="sailing-preview-title">
                <div class="hag-broker-card space-y-3">
                    <h2 id="sailing-preview-title">{{ __('sailing_import.preview') }}</h2>
                    <p class="hag-broker-muted">{{ __('sailing_import.review') }}</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach(['new', 'update', 'unchanged'] as $action)
                            <span class="rounded-full bg-cyan-100 px-3 py-1 text-sm font-semibold text-cyan-950 dark:bg-cyan-950 dark:text-cyan-200">{{ __('sailing_import.'.$action) }} · {{ $counts[$action] ?? 0 }}</span>
                        @endforeach
                    </div>
                </div>
                @foreach($plan as $row)
                    <details @if($row['action'] !== 'unchanged') open @endif class="hag-broker-card">
                        <summary class="cursor-pointer font-bold">{{ __('sailing_import.'.$row['action']) }} · <bdi>{{ $row['data']['voyage_number'] }}</bdi></summary>
                        <p class="mt-2 hag-broker-muted"><bdi>{{ $row['label'][0] }} · {{ $row['label'][1] }} · {{ $row['label'][2] }} → {{ $row['label'][3] }}</bdi></p>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            @foreach(['before' => $row['action'] === 'update' ? $row['before'] : null, 'after' => $row['data']] as $stage => $details)
                                @if($details)
                                    <div class="space-y-1 rounded-xl bg-slate-50 p-3 text-sm dark:bg-slate-950">
                                        <h3 class="font-bold">{{ __('sailing_import.'.$stage) }}</h3>
                                        <p>{{ $details['vessel_name'] }} · {{ __('sailings.statuses.'.$details['status']) }}</p>
                                        @foreach(['departure_at', 'arrival_at', 'cargo_cutoff_at'] as $field)
                                            <p>{{ __('sailings.'.$field) }} <bdi>{{ \Carbon\CarbonImmutable::parse($details[$field], 'UTC')->setTimezone('Asia/Dubai')->format('Y-m-d H:i') }}</bdi></p>
                                        @endforeach
                                        @php($types = is_string($details['container_types']) ? json_decode($details['container_types'], true) : $details['container_types'])
                                        <p>{{ implode(' · ', array_map(fn ($type) => __('sailings.containers.'.$type), (array) $types)) }}</p>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </details>
                @endforeach
                @if(($counts['new'] ?? 0) + ($counts['update'] ?? 0) > 0)
                    <form method="POST" action="{{ route('admin.freight-sailings.import.publish') }}" class="hag-broker-card flex flex-wrap items-center justify-between gap-3">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        <p class="hag-broker-muted">{{ __('sailings.dubai_time') }}</p>
                        <button class="hag-broker-action">{{ __('sailing_import.publish') }}</button>
                    </form>
                @else
                    <p role="status" class="hag-broker-empty">{{ __('sailing_import.no_changes') }}</p>
                @endif
            </section>
        @endisset
    </section>

    <script>
        (() => {
            const zone = document.getElementById('sailing-drop-zone');
            const input = document.getElementById('sailing-upload-file');
            const name = document.getElementById('sailing-file-name');
            const clear = document.getElementById('sailing-file-clear');
            const error = document.getElementById('sailing-file-error');

            function showSelection(file, message = '') {
                name.textContent = file ? file.name : '';
                clear.hidden = !file;
                error.textContent = message;
            }

            function validate(files) {
                if (!files.length) return '';
                if (files.length !== 1 || !/\.csv$/i.test(files[0].name)) {
                    return @js(__('sailing_import.invalid_file'));
                }
                if (files[0].size > 128 * 1024) return @js(__('sailing_import.file_too_large'));
                return '';
            }

            input.addEventListener('change', () => {
                const message = validate(input.files);
                if (message) input.value = '';
                showSelection(input.files[0], message);
            });
            clear.addEventListener('click', () => {
                input.value = '';
                showSelection();
                input.focus();
            });
            zone.addEventListener('dragover', event => {
                if (!Array.from(event.dataTransfer.types).includes('Files')) return;
                event.preventDefault();
                event.dataTransfer.dropEffect = 'copy';
                zone.classList.add('ring-2', 'ring-cyan-600');
            });
            zone.addEventListener('dragleave', event => {
                if (!zone.contains(event.relatedTarget)) zone.classList.remove('ring-2', 'ring-cyan-600');
            });
            zone.addEventListener('drop', event => {
                event.preventDefault();
                zone.classList.remove('ring-2', 'ring-cyan-600');
                const files = event.dataTransfer.files;
                if (!files.length) return;
                const message = validate(files);
                if (message) {
                    showSelection(input.files[0], message);
                    return;
                }
                try {
                    input.files = files;
                    showSelection(input.files[0]);
                } catch {
                    showSelection(input.files[0], @js(__('sailing_import.use_picker')));
                }
            });
        })();
    </script>
</x-admin-layout>
