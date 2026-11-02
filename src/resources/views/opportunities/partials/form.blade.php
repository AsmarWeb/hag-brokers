@php
    $catalog = \App\Services\Platform\OpportunityCatalog::class;
    $details = old('commercial_details', $editing ? ($opportunity->commercial_details ?? []) : []);
    $config = [
        'title' => old('title', $editing ? $opportunity->title : ''),
        'description' => old('description', $editing ? $opportunity->description : ''),
        'type' => old('opportunity_type', $editing ? $opportunity->opportunity_type : 'sell'),
        'endpoint' => route('company.opportunity-assistant'), 'language' => app()->getLocale(),
        'mediaError' => __('trade.photo_hint'),
        'error' => __('trade.ai_error'), 'consentError' => __('trade.ai_consent'),
    ];
@endphp
<form class="trade-layout" method="POST" enctype="multipart/form-data"
      action="{{ $editing ? route('company.opportunities.update', $opportunity) : route('opportunities.store') }}"
      x-data="tradeOpportunity(@js($config))">
    @csrf
    @if ($editing) @method('PATCH') @else <input type="hidden" name="request_key" value="{{ old('request_key', $requestKey) }}"> @endif
    <div class="trade-sections">
    <section class="trade-panel"><h2>{{ __('trade.basics') }}</h2>
        <div class="trade-field"><label for="trade-title">{{ __('trade.fields.title') }} *</label><input id="trade-title" name="title" x-model="title" value="{{ $config['title'] }}" required maxlength="255" placeholder="{{ __('trade.example') }}"><p>{{ __('trade.help_title') }}</p></div>
        <div class="trade-grid">
            <div class="trade-field"><label for="trade-type">{{ __('trade.fields.opportunity_type') }} *</label>
            @if ($editing)<p>{{ __('trade.types.'.$opportunity->opportunity_type) }}</p><input type="hidden" name="opportunity_type" value="{{ $opportunity->opportunity_type }}">
            @else<select id="trade-type" name="opportunity_type" x-model="type" required>@foreach ($catalog::TYPES as $value)<option value="{{ $value }}" @selected($config['type'] === $value)>{{ __('trade.types.'.$value) }}</option>@endforeach</select>@endif</div>
            <div class="trade-field"><label for="trade-category">{{ __('trade.fields.category') }}</label><select id="trade-category" name="category"><option value="">{{ __('trade.optional') }}</option>@if ($editing && $opportunity->category && !in_array($opportunity->category, $catalog::CATEGORIES, true))<option value="{{ $opportunity->category }}" selected>{{ $opportunity->category }}</option>@endif @foreach ($catalog::CATEGORIES as $value)<option value="{{ $value }}" @selected(old('category', $editing ? $opportunity->category : '') === $value)>{{ __('trade.categories.'.$value) }}</option>@endforeach</select><p>{{ __('trade.category_hint') }}</p></div>
        </div>
        <div class="trade-field"><label for="trade-description">{{ __('trade.fields.description') }} *</label><textarea id="trade-description" name="description" x-model="description" rows="7" required minlength="20" maxlength="20000" placeholder="{{ __('trade.description_example') }}">{{ $config['description'] }}</textarea><p>{{ __('trade.help_description') }}</p></div>
        <div class="trade-grid">
        @foreach (['country', 'city'] as $field)<div class="trade-field"><label for="trade-{{ $field }}">{{ __('trade.fields.'.$field) }}</label><input id="trade-{{ $field }}" name="{{ $field }}" maxlength="100" value="{{ old($field, $editing ? $opportunity->$field : ($field === 'country' ? 'United Arab Emirates' : '')) }}" @if ($field === 'city') list="trade-emirates" @endif><p>{{ __('trade.help_'.$field) }}</p></div>@endforeach
        </div>
        <datalist id="trade-emirates">@foreach (['Abu Dhabi', 'Dubai', 'Sharjah', 'Ajman', 'Umm Al Quwain', 'Ras Al Khaimah', 'Fujairah'] as $city)<option value="{{ $city }}">@endforeach</datalist>
        @if (!$editing)<div class="trade-grid"><div class="trade-field"><label for="trade-currency">{{ __('trade.fields.currency') }} *</label><select id="trade-currency" name="currency">@foreach ($catalog::CURRENCIES as $currency)<option value="{{ $currency }}" @selected(old('currency', 'AED') === $currency)>{{ $currency }}</option>@endforeach</select></div><div class="trade-field"><label for="trade-expires">{{ __('trade.fields.expires_at') }}</label><input type="date" id="trade-expires" name="expires_at" min="{{ now('Asia/Dubai')->toDateString() }}" value="{{ old('expires_at', now('Asia/Dubai')->addDays(30)->toDateString()) }}"></div></div>@else<p><bdi>{{ $opportunity->currency }}</bdi> · {{ __('trade.expires') }} <bdi>{{ $opportunity->expires_at?->setTimezone('Asia/Dubai')->format('Y-m-d') }}</bdi></p>@endif
    </section>
    <section class="trade-panel"><h2>{{ __('trade.details') }}</h2><p>{{ __('trade.public_notice') }}</p>
        <div class="trade-field"><label for="trade-product_name">{{ __('trade.fields.product_name') }}</label><input id="trade-product_name" name="commercial_details[product_name]" maxlength="180" value="{{ $details['product_name'] ?? '' }}"><p>{{ __('trade.help_product_name') }}</p></div>
        <div class="trade-field"><label for="trade-specifications">{{ __('trade.fields.specifications') }}</label><textarea id="trade-specifications" name="commercial_details[specifications]" rows="5" maxlength="5000">{{ $details['specifications'] ?? '' }}</textarea><p>{{ __('trade.help_specifications') }}</p></div>
        <div class="trade-grid">
        @foreach (['quantity' => '0.001', 'min_order_quantity' => '0.001', 'unit_price' => '0.01', 'lead_time_days' => '1'] as $field => $step)<div class="trade-field"><label for="trade-{{ $field }}">{{ __('trade.fields.'.$field) }}</label><input type="number" id="trade-{{ $field }}" name="commercial_details[{{ $field }}]" step="{{ $step }}" min="{{ $field === 'unit_price' ? '0' : $step }}" max="{{ $field === 'lead_time_days' ? '3650' : ($field === 'unit_price' ? '999999999.99' : '999999999.999') }}" value="{{ $details[$field] ?? '' }}">@if ($field === 'unit_price')<p>{{ __('trade.unit_price_hint') }}</p>@endif</div>@endforeach
        @foreach (['unit' => $catalog::UNITS, 'price_basis' => ['negotiable', 'fixed', 'quotation'], 'condition' => ['new', 'used', 'refurbished', 'not_applicable'], 'delivery_terms' => $catalog::TERMS] as $field => $values)<div class="trade-field"><label for="trade-{{ $field }}">{{ __('trade.fields.'.$field) }}</label><select id="trade-{{ $field }}" name="commercial_details[{{ $field }}]"><option value="">{{ __('trade.optional') }}</option>@foreach ($values as $value)<option value="{{ $value }}" @selected(($details[$field] ?? '') === $value)>{{ $field === 'unit' ? __('trade.units.'.$value) : (in_array($value, ['exw', 'fca', 'fob', 'cfr', 'cif', 'dap', 'ddp']) ? strtoupper($value) : __('trade.options.'.$value)) }}</option>@endforeach</select></div>@endforeach
        @foreach (['target_market' => 150, 'payment_terms' => 500, 'certifications' => 500] as $field => $max)<div class="trade-field"><label for="trade-{{ $field }}">{{ __('trade.fields.'.$field) }}</label><input id="trade-{{ $field }}" name="commercial_details[{{ $field }}]" maxlength="{{ $max }}" value="{{ $details[$field] ?? '' }}">@if ($field !== 'certifications')<p>{{ __('trade.help_'.$field) }}</p>@endif</div>@endforeach
        </div>
    </section>
    @if (!$editing)
    <fieldset class="trade-panel" x-show="type === 'logistics'" :disabled="type !== 'logistics'"><legend>{{ __('trade.shipping') }}</legend><div class="trade-grid">@foreach (['cargo_type', 'container_type'] as $field)<div class="trade-field"><label for="trade-{{ $field }}">{{ __('trade.fields.'.$field) }}</label><input id="trade-{{ $field }}" name="{{ $field }}" value="{{ old($field) }}" maxlength="255"></div>@endforeach
@foreach (['origin_port_id', 'destination_port_id'] as $field)<div class="trade-field"><label for="trade-{{ $field }}">{{ __('trade.fields.'.$field) }}</label><select id="trade-{{ $field }}" name="{{ $field }}"><option value="">{{ __('trade.optional') }}</option>@foreach ($ports as $port)<option value="{{ $port->id }}" @selected((string)old($field) === (string)$port->id)>{{ $port->name }}</option>@endforeach</select></div>@endforeach</div></fieldset>
    <fieldset class="trade-panel" x-show="type === 'vessel_brokerage'" :disabled="type !== 'vessel_brokerage'"><legend>{{ __('trade.vessel_section') }}</legend><div class="trade-grid">
        @foreach (['listing_type' => ['sale','wanted','charter'], 'vessel_category' => ['yacht','commercial_vessel','boat']] as $field => $values)<div class="trade-field"><label for="trade-{{ $field }}">{{ __('trade.fields.'.$field) }} *</label><select id="trade-{{ $field }}" name="vessel[{{ $field }}]" :required="type === 'vessel_brokerage'">@foreach ($values as $value)<option value="{{ $value }}" @selected(old('vessel.'.$field) === $value)>{{ __('trade.options.'.$value) }}</option>@endforeach</select></div>@endforeach
        @foreach (['vessel_type', 'vessel_name', 'builder', 'year_built', 'length_m', 'imo_number'] as $field)<div class="trade-field"><label for="trade-{{ $field }}">{{ __('trade.fields.'.$field) }}</label><input id="trade-{{ $field }}" name="vessel[{{ $field }}]" value="{{ old('vessel.'.$field) }}" maxlength="{{ $field === 'vessel_type' ? 80 : 255 }}"></div>@endforeach
    </div></fieldset>
    @endif
    @if (!$editing)<fieldset class="trade-panel" x-show="type === 'investment'" :disabled="type !== 'investment'"><legend>{{ __('trade.legacy_financial') }}</legend><div class="trade-grid">@foreach (['required_capital', 'min_investment'] as $field)<div class="trade-field"><label for="trade-{{ $field }}">{{ __('trade.fields.'.$field) }}</label><input id="trade-{{ $field }}" name="{{ $field }}" type="number" step="0.01" min="0" max="9999999999999.99" value="{{ old($field) }}"></div>@endforeach</div></fieldset>@endif
    <section class="trade-panel"><h2>{{ __('trade.media_section') }}</h2>
        @if (!$editing)<div class="trade-field"><label for="trade-media">{{ __('trade.fields.media') }}</label><input type="file" id="trade-media" name="media[]" @change="checkMedia($event)" multiple accept="image/jpeg,image/png,image/webp,video/mp4,video/quicktime,video/webm"><p>{{ __('trade.photo_hint') }}</p></div>@endif
        <div class="trade-field"><label for="trade-notes">{{ __('trade.fields.notes') }}</label><textarea id="trade-notes" name="notes" rows="3" maxlength="5000">{{ old('notes', $editing ? $opportunity->notes : '') }}</textarea></div>
        <p class="trade-notice">{{ __('trade.auto_publish') }}</p>
        <label class="trade-check"><input type="checkbox" required> <span>{{ __('trade.confirm') }}</span></label>
        <div class="trade-actions"><button type="submit" class="trade-button">{{ $editing ? __('trade.save') : __('trade.publish') }}</button><a class="trade-link" href="{{ route('company.marketplace') }}">{{ __('trade.cancel') }}</a></div>
    </section>
    </div>
    <aside class="trade-sidebar"><section class="trade-panel"><h2>{{ __('trade.ai_title') }}</h2><p>{{ __('trade.guide_intro') }}</p>
        <button type="button" class="trade-button trade-secondary" @click="review('guide')" :disabled="busy">{{ __('trade.guide_button') }}</button>
        <label class="trade-check"><input type="checkbox" x-model="consent"><span>{{ __('trade.ai_consent') }}</span></label>
        @if (app(\App\Services\Platform\OpportunityAssistant::class)->ready())<button type="button" class="trade-button" @click="review('ai')" :disabled="busy || !consent || description.length > 6000">{{ __('trade.ai_button') }}</button>@else<p class="trade-notice">{{ __('trade.ai_unavailable') }}</p>@endif
        <p x-show="description.length > 6000">{{ __('trade.ai_length') }}</p>
        <p x-show="busy" role="status">{{ __('trade.ai_working') }}</p><p x-text="error" class="trade-error" role="alert"></p>
        <div x-show="result" class="trade-assistant-result" aria-live="polite">
            <p x-text="result?.message"></p>
            <template x-if="result?.suggestion"><div><h3 x-text="result.suggestion.title"></h3><p class="trade-prose" x-text="result.suggestion.description"></p><button type="button" class="trade-button" @click="apply()">{{ __('trade.ai_apply') }}</button></div></template>
            <h3>{{ __('trade.missing') }}</h3><ul><template x-for="(item, index) in (result?.suggestion?.missing_details || result?.missing_details || [])" :key="index"><li x-text="item"></li></template></ul>
            <p x-show="result && (result.suggestion?.missing_details || result.missing_details || []).length === 0">{{ __('trade.complete') }}</p>
        </div>
        <button type="button" x-show="original" class="trade-link" @click="undo()">{{ __('trade.ai_undo') }}</button>
    </section></aside>
</form>
