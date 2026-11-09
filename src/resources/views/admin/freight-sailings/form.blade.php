<x-admin-layout>
    <x-slot name="header">{{ __('sailings.admin_title') }}</x-slot>
    <main class="mx-auto max-w-4xl px-5 py-8">
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
                    <select name="{{ $field }}" class="rounded-xl text-slate-900" required>
                        <option value="">{{ __('sailings.choose') }}</option>
                        @foreach($options as $option)
                            <option value="{{ $option->id }}" @selected((string) old($field, $sailing->$field) === (string) $option->id)>{{ $option->name }}</option>
                        @endforeach
                    </select>
                </label>
            @endforeach
            @foreach(['vessel_name' => 120, 'voyage_number' => 60] as $field => $max)
                <label class="grid gap-2">{{ __('sailings.'.$field) }}<input required name="{{ $field }}" maxlength="{{ $max }}" value="{{ old($field, $sailing->$field) }}" class="rounded-xl text-slate-900"></label>
            @endforeach
            @foreach(['departure_at','arrival_at','cargo_cutoff_at'] as $field)
                <label class="grid gap-2">{{ __('sailings.'.$field) }}<input required name="{{ $field }}" type="datetime-local" value="{{ old($field, $sailing->$field?->setTimezone('Asia/Dubai')->format('Y-m-d\TH:i')) }}" class="rounded-xl text-slate-900"></label>
            @endforeach
            <fieldset>
                <legend>{{ __('sailings.container_type') }}</legend>
                @foreach(\App\Models\FreightSailing::CONTAINER_TYPES as $type)
                    <label class="block"><input type="checkbox" name="container_types[]" value="{{ $type }}" @checked(in_array($type, (array) old('container_types', $sailing->container_types ?? []), true))> {{ __('sailings.containers.'.$type) }}</label>
                @endforeach
            </fieldset>
            <label class="grid gap-2">{{ __('sailings.status') }}
                <select name="status" class="rounded-xl text-slate-900">
                    @foreach(\App\Models\FreightSailing::STATUSES as $status)
                        <option value="{{ $status }}" @selected(old('status', $sailing->status) === $status)>{{ __('sailings.statuses.'.$status) }}</option>
                    @endforeach
                </select>
            </label>
            <button class="rounded-xl bg-cyan-400 px-4 py-3 font-bold text-slate-950">{{ __('sailings.save') }}</button>
        </form>
    </main>
</x-admin-layout>
