@php
    $isEdit = isset($shipment) && $shipment;
@endphp

<form action="{{ $action }}" method="POST" class="space-y-6">
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="rounded-2xl border border-red-500/30 bg-red-500/10 p-5">
            <div class="mb-3 flex items-center gap-2">
                <span class="text-red-400">⚠</span>

                <h3 class="font-black text-red-300">
                    Please fix the following errors
                </h3>
            </div>

            <ul class="space-y-1 text-sm text-red-200">
                @foreach ($errors->all() as $error)
                    <li>• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    {{-- =========================================================
         CUSTOMER & BOOKING
    ========================================================== --}}

    <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-6">

        <div class="mb-6">
            <div class="mb-2 text-xs font-black uppercase tracking-[0.2em] text-cyan-400">
                Shipment
            </div>

            <h2 class="text-xl font-black text-white">
                Customer & Booking
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Connect the shipment with the customer and quotation.
            </p>
        </div>

        <div class="grid gap-5 md:grid-cols-2">

            {{-- Customer --}}
            <div>
                <label for="customer_id" class="mb-2 block text-sm font-bold text-slate-300">
                    Customer
                    <span class="text-cyan-400">*</span>
                </label>

                <select id="customer_id" name="customer_id" required
                    class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/10">
                    <option value="">
                        Select customer
                    </option>

                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}" @selected(old('customer_id', $shipment?->customer_id) == $customer->id)>
                            {{ $customer->company_name }}

                            @if ($customer->contact_person)
                                — {{ $customer->contact_person }}
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>


            {{-- Quotation --}}
            <div>
                <label for="quotation_id" class="mb-2 block text-sm font-bold text-slate-300">
                    Quotation
                </label>

                <select id="quotation_id" name="quotation_id"
                    class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/10">
                    <option value="">
                        No quotation
                    </option>

                    @foreach ($quotations as $quotation)
                        <option value="{{ $quotation->id }}" @selected(old('quotation_id', $shipment?->quotation_id) == $quotation->id)>
                            {{ $quotation->reference_no }}

                            @if ($quotation->customer)
                                — {{ $quotation->customer->company_name }}
                            @endif

                            @if ($quotation->originPort && $quotation->destinationPort)
                                — {{ $quotation->originPort->name }}
                                →
                                {{ $quotation->destinationPort->name }}
                            @endif
                        </option>
                    @endforeach
                </select>

                <p class="mt-2 text-xs text-slate-600">
                    Optional. Recommended when the shipment comes from a quotation.
                </p>
            </div>


            {{-- Booking Number --}}
            <div>
                <label for="booking_no" class="mb-2 block text-sm font-bold text-slate-300">
                    Booking Number
                </label>

                <input id="booking_no" type="text" name="booking_no"
                    value="{{ old('booking_no', $shipment?->booking_no) }}" placeholder="Example: HAG-BKG-2026-001"
                    class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white outline-none transition placeholder:text-slate-600 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/10">

                <p class="mt-2 text-xs text-slate-600">
                    Carrier or booking reference, if available.
                </p>
            </div>


            {{-- Status --}}
            <div>
                <label for="status" class="mb-2 block text-sm font-bold text-slate-300">
                    Shipment Status
                    <span class="text-cyan-400">*</span>
                </label>

                @php
                    $status = old('status', $shipment?->status ?? 'pending');
                @endphp

                <select id="status" name="status" required
                    class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/10">
                    <option value="pending" @selected($status === 'pending')>
                        Pending
                    </option>

                    <option value="booked" @selected($status === 'booked')>
                        Booked
                    </option>

                    <option value="confirmed" @selected($status === 'confirmed')>
                        Confirmed
                    </option>

                    <option value="in_transit" @selected($status === 'in_transit')>
                        In Transit
                    </option>

                    <option value="arrived" @selected($status === 'arrived')>
                        Arrived
                    </option>

                    <option value="delivered" @selected($status === 'delivered')>
                        Delivered
                    </option>

                    <option value="cancelled" @selected($status === 'cancelled')>
                        Cancelled
                    </option>
                </select>
            </div>

        </div>
    </div>


    {{-- =========================================================
         ROUTE
    ========================================================== --}}

    <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-6">

        <div class="mb-6">
            <h2 class="text-xl font-black text-white">
                Shipping Route
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Define the origin and destination ports.
            </p>
        </div>

        <div class="grid gap-5 md:grid-cols-2">

            {{-- Origin --}}
            <div>
                <label for="origin_port_id" class="mb-2 block text-sm font-bold text-slate-300">
                    Origin Port
                    <span class="text-cyan-400">*</span>
                </label>

                <select id="origin_port_id" name="origin_port_id" required
                    class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/10">
                    <option value="">
                        Select origin port
                    </option>

                    @foreach ($ports as $port)
                        <option value="{{ $port->id }}" @selected(old('origin_port_id', $shipment?->origin_port_id) == $port->id)>
                            {{ $port->name }}

                            @if ($port->port_code)
                                ({{ $port->port_code }})
                            @elseif($port->unlocode)
                                ({{ $port->unlocode }})
                            @endif

                            @if ($port->country)
                                — {{ $port->country }}
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>


            {{-- Destination --}}
            <div>
                <label for="destination_port_id" class="mb-2 block text-sm font-bold text-slate-300">
                    Destination Port
                    <span class="text-cyan-400">*</span>
                </label>

                <select id="destination_port_id" name="destination_port_id" required
                    class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/10">
                    <option value="">
                        Select destination port
                    </option>

                    @foreach ($ports as $port)
                        <option value="{{ $port->id }}" @selected(old('destination_port_id', $shipment?->destination_port_id) == $port->id)>
                            {{ $port->name }}

                            @if ($port->port_code)
                                ({{ $port->port_code }})
                            @elseif($port->unlocode)
                                ({{ $port->unlocode }})
                            @endif

                            @if ($port->country)
                                — {{ $port->country }}
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>

        </div>

        <div class="mt-5 rounded-xl border border-cyan-400/10 bg-cyan-400/5 p-4">

            <div class="flex gap-3">

                <div class="mt-0.5 text-cyan-400">
                    ⛴
                </div>

                <div>
                    <div class="text-sm font-bold text-cyan-300">
                        Shipping Route
                    </div>

                    <div class="mt-1 text-xs leading-5 text-slate-500">
                        The route represents the shipment's planned movement
                        from the origin port to the destination port.
                    </div>
                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         CARRIER & VESSEL
    ========================================================== --}}

    <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-6">

        <div class="mb-6">
            <h2 class="text-xl font-black text-white">
                Carrier & Vessel
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Assign the shipping carrier and vessel when available.
            </p>
        </div>

        <div class="grid gap-5 md:grid-cols-2">

            {{-- Carrier --}}
            <div>
                <label for="carrier_id" class="mb-2 block text-sm font-bold text-slate-300">
                    Carrier
                </label>

                <select id="carrier_id" name="carrier_id"
                    class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/10">
                    <option value="">
                        Select carrier
                    </option>

                    @foreach ($carriers as $carrier)
                        <option value="{{ $carrier->id }}" @selected(old('carrier_id', $shipment?->carrier_id) == $carrier->id)>
                            {{ $carrier->name }}

                            @if ($carrier->code)
                                — {{ $carrier->code }}
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>


            {{-- Vessel --}}
            <div>
                <label for="vessel_id" class="mb-2 block text-sm font-bold text-slate-300">
                    Vessel
                </label>

                <select id="vessel_id" name="vessel_id"
                    class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/10">
                    <option value="">
                        Select vessel
                    </option>

                    @foreach ($vessels as $vessel)
                        <option value="{{ $vessel->id }}" @selected(old('vessel_id', $shipment?->vessel_id) == $vessel->id)>
                            {{ $vessel->name }}

                            @if ($vessel->imo)
                                — IMO {{ $vessel->imo }}
                            @endif

                            @if ($vessel->carrier)
                                — {{ $vessel->carrier->name }}
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>

        </div>

        <div class="mt-5 rounded-xl border border-amber-400/10 bg-amber-400/5 p-4">

            <div class="flex gap-3">

                <div class="mt-0.5 text-amber-400">
                    ⚓
                </div>

                <div>
                    <div class="text-sm font-bold text-amber-300">
                        Carrier Assignment
                    </div>

                    <div class="mt-1 text-xs leading-5 text-slate-500">
                        HAG Brokers acts as the intermediary. The carrier and
                        vessel represent the actual shipping service selected
                        for this shipment.
                    </div>
                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         CARGO
    ========================================================== --}}

    <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-6">

        <div class="mb-6">
            <h2 class="text-xl font-black text-white">
                Cargo Information
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Enter the cargo and container requirements.
            </p>
        </div>

        <div class="grid gap-5 md:grid-cols-2">

            {{-- Cargo Type --}}
            <div>
                <label for="cargo_type" class="mb-2 block text-sm font-bold text-slate-300">
                    Cargo Type
                </label>

                <input id="cargo_type" type="text" name="cargo_type"
                    value="{{ old('cargo_type', $shipment?->cargo_type) }}" placeholder="Example: General Cargo"
                    class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white outline-none transition placeholder:text-slate-600 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/10">
            </div>


            {{-- Container Type --}}
            <div>
                <label for="container_type" class="mb-2 block text-sm font-bold text-slate-300">
                    Container Type
                </label>

                <select id="container_type" name="container_type"
                    class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/10">
                    @php
                        $containerType = old('container_type', $shipment?->container_type);
                    @endphp

                    <option value="">
                        Select container type
                    </option>

                    <option value="20GP" @selected($containerType === '20GP')>
                        20GP — 20ft General Purpose
                    </option>

                    <option value="40GP" @selected($containerType === '40GP')>
                        40GP — 40ft General Purpose
                    </option>

                    <option value="40HC" @selected($containerType === '40HC')>
                        40HC — 40ft High Cube
                    </option>

                    <option value="45HC" @selected($containerType === '45HC')>
                        45HC — 45ft High Cube
                    </option>

                    <option value="REEFER" @selected($containerType === 'REEFER')>
                        Reefer
                    </option>

                    <option value="OPEN_TOP" @selected($containerType === 'OPEN_TOP')>
                        Open Top
                    </option>

                    <option value="FLAT_RACK" @selected($containerType === 'FLAT_RACK')>
                        Flat Rack
                    </option>

                    <option value="OTHER" @selected($containerType === 'OTHER')>
                        Other
                    </option>

                </select>
            </div>


            {{-- Quantity --}}
            <div>
                <label for="quantity" class="mb-2 block text-sm font-bold text-slate-300">
                    Quantity
                    <span class="text-cyan-400">*</span>
                </label>

                <input id="quantity" type="number" name="quantity" min="1"
                    value="{{ old('quantity', $shipment?->quantity ?? 1) }}" required
                    class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/10">
            </div>


            {{-- Weight --}}
            <div>
                <label for="weight" class="mb-2 block text-sm font-bold text-slate-300">
                    Weight
                </label>

                <div class="relative">

                    <input id="weight" type="number" name="weight" min="0" step="0.01"
                        value="{{ old('weight', $shipment?->weight) }}" placeholder="Example: 12500"
                        class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 pr-16 text-sm text-white outline-none transition placeholder:text-slate-600 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/10">

                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-600">
                        KG
                    </span>

                </div>
            </div>

        </div>

    </div>


    {{-- =========================================================
         SCHEDULE
    ========================================================== --}}

    <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-6">

        <div class="mb-6">
            <h2 class="text-xl font-black text-white">
                Shipment Schedule
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Planned and actual shipment dates.
            </p>
        </div>

        <div class="grid gap-5 md:grid-cols-2">

            {{-- Preferred Date --}}
            <div>
                <label for="preferred_date" class="mb-2 block text-sm font-bold text-slate-300">
                    Preferred Date
                </label>

                <input id="preferred_date" type="date" name="preferred_date"
                    value="{{ old('preferred_date', $shipment?->preferred_date?->format('Y-m-d')) }}"
                    class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/10">
            </div>


            {{-- ETD --}}
            <div>
                <label for="etd" class="mb-2 block text-sm font-bold text-slate-300">
                    Estimated Departure (ETD)
                </label>

                <input id="etd" type="datetime-local" name="etd"
                    value="{{ old('etd', $shipment?->etd?->format('Y-m-d\TH:i')) }}"
                    class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/10">
            </div>


            {{-- ETA --}}
            <div>
                <label for="eta" class="mb-2 block text-sm font-bold text-slate-300">
                    Estimated Arrival (ETA)
                </label>

                <input id="eta" type="datetime-local" name="eta"
                    value="{{ old('eta', $shipment?->eta?->format('Y-m-d\TH:i')) }}"
                    class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/10">
            </div>


            {{-- Actual Departure --}}
            <div>
                <label for="actual_departure" class="mb-2 block text-sm font-bold text-slate-300">
                    Actual Departure
                </label>

                <input id="actual_departure" type="datetime-local" name="actual_departure"
                    value="{{ old('actual_departure', $shipment?->actual_departure?->format('Y-m-d\TH:i')) }}"
                    class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/10">
            </div>


            {{-- Actual Arrival --}}
            <div class="md:col-span-2">

                <label for="actual_arrival" class="mb-2 block text-sm font-bold text-slate-300">
                    Actual Arrival
                </label>

                <input id="actual_arrival" type="datetime-local" name="actual_arrival"
                    value="{{ old('actual_arrival', $shipment?->actual_arrival?->format('Y-m-d\TH:i')) }}"
                    class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/10">

            </div>

        </div>

    </div>


    {{-- =========================================================
         ACTIONS
    ========================================================== --}}

    <div class="flex flex-col-reverse justify-between gap-3 sm:flex-row sm:items-center">

        <a href="{{ route('admin.shipments.index') }}"
            class="inline-flex items-center justify-center rounded-xl border border-slate-700 px-6 py-3 text-sm font-bold text-slate-300 transition hover:bg-slate-900 hover:text-white">
            Cancel
        </a>

        <button type="submit"
            class="inline-flex items-center justify-center rounded-xl bg-cyan-400 px-7 py-3 text-sm font-black text-slate-950 shadow-lg shadow-cyan-400/10 transition hover:bg-cyan-300">
            {{ $isEdit ? 'Update Shipment' : 'Create Shipment' }}
        </button>

    </div>

</form>
