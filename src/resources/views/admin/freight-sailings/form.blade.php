<x-admin-layout>
    <x-slot name="header">{{ __('sailings.admin_title') }}</x-slot>
    <section class="mx-auto max-w-4xl px-5 py-8">
        <a class="underline" href="{{ route('admin.freight-sailings.index') }}">{{ __('sailings.admin_title') }}</a>
        <p class="my-4">{{ __('sailings.dubai_time') }}</p>
        @if($errors->any())<ul role="alert" class="my-4">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
        <form class="grid gap-4 sm:grid-cols-2" method="POST" action="{{ $sailing->exists ? route('admin.freight-sailings.update', $sailing) : route('admin.freight-sailings.store') }}">
            @csrf
            @if($sailing->exists)
                @method('PUT')
                <input type="hidden" name="revision" value="{{ \App\Http\Controllers\Admin\AdminFreightSailingController::revision($sailing) }}">
            @endif
            @foreach(['origin_port_id' => $ports, 'destination_port_id' => $ports, 'carrier_id' => $carriers] as $field => $options)
                <label class="grid gap-2">{{ __('sailings.'.$field) }}
                    <select name="{{ $field }}" class="rounded-xl border-slate-300 bg-white text-slate-900 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100" required>
                        <option value="">{{ __('sailings.choose') }}</option>
                        @foreach($options as $option)
                            <option value="{{ $option->id }}" @selected((string) old($field, $sailing->$field) === (string) $option->id)>{{ $option->name }}</option>
                        @endforeach
                    </select>
                </label>
            @endforeach
            <label class="grid gap-2">{{ __('sailings.vessel_name') }}
                <select name="vessel_id" class="rounded-xl border-slate-300 bg-white text-slate-900 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100" @required(!$sailing->exists || $sailing->vessel_id)>
                    <option value="">{{ __('sailings.choose') }}</option>
                    @foreach($vessels as $vessel)
                        <option value="{{ $vessel->id }}" @selected((string) old('vessel_id', $sailing->vessel_id) === (string) $vessel->id)>
                            {{ $vessel->name }} · {{ $carriers->find($vessel->carrier_id)?->name }} @if($vessel->imo) · IMO {{ $vessel->imo }} @endif
                        </option>
                    @endforeach
                </select>
            </label>
            @if($sailing->exists && !$sailing->vessel_id)
                <label class="grid gap-2">{{ __('sailing_import.legacy_vessel') }}
                    <input name="vessel_name" maxlength="120" value="{{ old('vessel_name', $sailing->vessel_name) }}" class="rounded-xl border-slate-300 bg-white text-slate-900 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                </label>
            @endif
            <label class="grid gap-2">{{ __('sailings.voyage_number') }}
                <input required name="voyage_number" maxlength="60" value="{{ old('voyage_number', $sailing->voyage_number) }}" class="rounded-xl border-slate-300 bg-white text-slate-900 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
            </label>
            @foreach(['departure_at','arrival_at','cargo_cutoff_at'] as $field)
                <label class="grid gap-2">{{ __('sailings.'.$field) }}<input required name="{{ $field }}" type="datetime-local" value="{{ old($field, $sailing->$field?->setTimezone('Asia/Dubai')->format('Y-m-d\TH:i')) }}" class="rounded-xl border-slate-300 bg-white text-slate-900 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"></label>
            @endforeach
            <fieldset>
                <legend>{{ __('sailings.container_type') }}</legend>
                @foreach(\App\Models\FreightSailing::CONTAINER_TYPES as $type)
                    <label class="block"><input type="checkbox" name="container_types[]" value="{{ $type }}" @checked(in_array($type, (array) old('container_types', $sailing->container_types ?? []), true))> {{ __('sailings.containers.'.$type) }}</label>
                @endforeach
            </fieldset>
            <label class="grid gap-2">{{ __('sailings.status') }}
                <select name="status" class="rounded-xl border-slate-300 bg-white text-slate-900 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                    @foreach(\App\Models\FreightSailing::STATUSES as $status)
                        <option value="{{ $status }}" @selected(old('status', $sailing->status) === $status)>{{ __('sailings.statuses.'.$status) }}</option>
                    @endforeach
                </select>
            </label>
            <button class="rounded-xl bg-cyan-400 px-4 py-3 font-bold text-slate-950">{{ __('sailings.save') }}</button>
        </form>
    </section>
</x-admin-layout>
