<x-admin-layout>
    <x-slot name="header">{{ __('sailings.admin_title') }}</x-slot>
    <main class="mx-auto max-w-6xl space-y-4 px-5 py-8">
        <a class="font-bold underline" href="{{ route('admin.freight-sailings.create') }}">{{ __('sailings.create') }}</a>
        @if(session('success'))<p role="status">{{ session('success') }}</p>@endif
        @forelse($sailings as $sailing)
            <article class="rounded-2xl border p-4">
                <p><bdi>{{ $sailing->originPort->name }} → {{ $sailing->destinationPort->name }}</bdi></p>
                <p><bdi>{{ $sailing->carrier->name }} · {{ $sailing->vessel_name }} · {{ $sailing->voyage_number }}</bdi></p>
                <p>{{ __('sailings.statuses.'.$sailing->status) }} · <bdi>{{ $sailing->departure_at->setTimezone('Asia/Dubai')->format('Y-m-d H:i') }}</bdi></p>
                <a class="underline" href="{{ route('admin.freight-sailings.edit', $sailing) }}">{{ __('sailings.edit') }}</a>
                @if($sailing->status !== 'cancelled')
                    <form method="POST" action="{{ route('admin.freight-sailings.cancel', $sailing) }}" class="mt-2">
                        @csrf @method('PATCH')
                        <button class="underline">{{ __('sailings.cancel') }}</button>
                    </form>
                @endif
            </article>
        @empty
            <p>{{ __('sailings.empty') }}</p>
        @endforelse
        {{ $sailings->links() }}
    </main>
</x-admin-layout>
