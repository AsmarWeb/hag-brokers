@if ($opportunity->commercial_details)
<section class="hag-broker-card"><h2 class="text-lg font-bold">{{ __('trade.specs') }}</h2><dl class="mt-4 grid gap-4 sm:grid-cols-2">
@foreach (\App\Services\Platform\OpportunityCatalog::FIELDS as $field)
@php $value = $opportunity->commercial_details[$field] ?? null; @endphp
@if ($value !== null && $value !== '')<div><dt class="text-sm font-semibold hag-broker-muted">{{ __('trade.fields.'.$field) }}</dt><dd class="mt-1 whitespace-pre-wrap break-words">@if ($field === 'unit'){{ __('trade.units.'.$value) }}@elseif (in_array($field, ['condition', 'price_basis'])){{ __('trade.options.'.$value) }}@elseif ($field === 'delivery_terms'){{ in_array($value, ['negotiable', 'not_applicable']) ? __('trade.options.'.$value) : strtoupper($value) }}@else{{ $value }}@endif @if ($field === 'unit_price')<bdi>{{ $opportunity->currency }}</bdi>@endif</dd></div>@endif
@endforeach
</dl><p class="mt-4 text-sm hag-broker-muted">{{ __('trade.declared') }}</p></section>
@endif
