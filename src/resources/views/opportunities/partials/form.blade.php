@php
    $catalog = \App\Services\Platform\OpportunityCatalog::class;
    $details = old('commercial_details', $editing ? ($opportunity->commercial_details ?? []) : []);
    $config = [
        'subcategory' => $details['subcategory'] ?? '',
        'subcategories' => $catalog::SUBCATEGORIES,
        'title' => old('title', $editing ? $opportunity->title : ''),
        'description' => old('description', $editing ? $opportunity->description : ''),
        'type' => old('opportunity_type', $editing ? $opportunity->opportunity_type : 'sell'),
        'currency' => $editing ? $opportunity->currency : old('currency', 'AED'),
        'typeLabels' => __('trade.types'), 'categoryLabels' => __('trade.categories'), 'unitLabels' => __('trade.units'),
        'category' => old('category', $editing ? $opportunity->category : ''),
        'step' => $errors->has('opportunity_type') || $errors->has('category') ? 1 : ($errors->has('media') || $errors->has('media.*') ? 4 : ($errors->has('commercial_details.*') || $errors->has('vessel.*') ? 3 : ($errors->any() ? 2 : 1))),
        'stepLabels' => array_values(__('trade.step_labels')),
        'mediaLabel' => __('trade.media_invalid'),
        'endpoint' => route('company.opportunity-assistant'), 'language' => app()->getLocale(),
        'mediaError' => __('trade.photo_hint'),
        'error' => __('trade.ai_error'), 'consentError' => __('trade.ai_consent'),
    ];
@endphp
<form class="trade-wizard" method="POST" enctype="multipart/form-data" novalidate @submit="submit($event)"
      action="{{ $editing ? route('company.opportunities.update', $opportunity) : route('opportunities.store') }}"
      x-data="tradeOpportunity(@js($config))">
    @csrf
    @if ($editing) @method('PATCH') @else <input type="hidden" name="request_key" value="{{ old('request_key', $requestKey) }}"> @endif
<div class="trade-wizard-top"><div><span class="trade-eyebrow">{{ __('trade.guided_publish') }}</span><h2 x-text="stepLabels[step - 1]"></h2></div><span class="trade-progress-text"><bdi x-text="Math.round(step / 5 * 100)"></bdi>%</span></div><div class="trade-progress-track"><span :style="{ width: (step / 5 * 100) + '%' }"></span></div>
<div class="trade-wizard-grid"><nav class="trade-wizard-nav" aria-label="{{ __('trade.steps_label') }}">
@foreach (['goal','details','terms','media','review'] as $index => $key)<button type="button" @click="go({{ $index + 1 }})" :class="{ 'is-current': step === {{ $index + 1 }}, 'is-complete': step > {{ $index + 1 }} }" :aria-current="step === {{ $index + 1 }} ? 'step' : null"><span class="trade-nav-number">{{ $index + 1 }}</span><span><strong>{{ __('trade.step_labels.'.$key) }}</strong><small>{{ __('trade.step_hints.'.$key) }}</small></span></button>@endforeach
<div class="trade-wizard-tip"><strong>{{ __('trade.guide_heading') }}</strong><p>{{ __('trade.guide_intro') }}</p></div></nav>
    <div class="trade-sections">
<section class="trade-panel trade-step" data-trade-step="1" x-show="step === 1">
<div class="trade-step-heading"><span class="trade-step-number">01</span><div><p class="trade-eyebrow">HAG BROKERS</p><h2>{{ __('trade.choose_goal') }}</h2><p>{{ __('trade.choose_goal_help') }}</p></div></div>
@if ($editing)<p class="trade-notice">{{ \Illuminate\Support\Facades\Lang::has('trade.types.'.$opportunity->opportunity_type) ? __('trade.types.'.$opportunity->opportunity_type) : $opportunity->opportunity_type }}</p><input type="hidden" name="opportunity_type" value="{{ $opportunity->opportunity_type }}">
@else<fieldset class="trade-card-group"><legend>{{ __('trade.fields.opportunity_type') }} *</legend><div class="trade-choice-grid trade-type-grid">
@foreach (['sell','buy','service_offer','service_request','partnership','distribution'] as $value)
<label class="trade-choice" :class="type === @js($value) ? 'is-selected' : ''"><input type="radio" name="opportunity_type" value="{{ $value }}" x-model="type" @checked($config['type'] === $value) required>
<span class="trade-choice-mark">@include('opportunities.partials.icon', ['name' => $value])</span><span class="trade-choice-copy"><strong>{{ __('trade.types.'.$value) }}</strong>@if (in_array($value, ['sell','buy','service_offer','service_request','partnership','distribution']))<small>{{ __('trade.type_help.'.$value) }}</small>@endif</span><span class="trade-choice-check" aria-hidden="true">✓</span></label>
@endforeach</div><details class="trade-more-choices" :open="!['sell','buy','service_offer','service_request','partnership','distribution'].includes(type)"><summary>{{ __('trade.more_types') }}</summary><div class="trade-choice-grid trade-type-grid">@foreach (array_values(array_diff($catalog::TYPES, ['sell','buy','service_offer','service_request','partnership','distribution'])) as $value)
<label class="trade-choice" :class="type === @js($value) ? 'is-selected' : ''"><input type="radio" name="opportunity_type" value="{{ $value }}" x-model="type" @checked($config['type'] === $value) required>
<span class="trade-choice-mark">@include('opportunities.partials.icon', ['name' => $value])</span><span class="trade-choice-copy"><strong>{{ __('trade.types.'.$value) }}</strong>@if (in_array($value, ['sell','buy','service_offer','service_request','partnership','distribution']))<small>{{ __('trade.type_help.'.$value) }}</small>@endif</span><span class="trade-choice-check" aria-hidden="true">✓</span></label>
@endforeach</div></details><div></div></fieldset>@endif
<fieldset class="trade-card-group"><legend>{{ __('trade.fields.category') }}</legend><p>{{ __('trade.category_hint') }}</p>
<div class="trade-choice-grid trade-sector-grid">
<label class="trade-choice trade-sector" :class="category === '' ? 'is-selected' : ''"><input type="radio" name="category" value="" x-model="category" @checked(!$config['category'])><span class="trade-choice-mark">@include('opportunities.partials.icon', ['name' => 'other'])</span><strong>{{ __('trade.choose_later') }}</strong><span class="trade-choice-check" aria-hidden="true">✓</span></label>
@if ($editing && $opportunity->category && !in_array($opportunity->category, $catalog::CATEGORIES, true))<label class="trade-choice trade-sector"><input type="radio" name="category" x-model="category" value="{{ $opportunity->category }}" @checked($config['category'] === $opportunity->category)><strong>{{ $opportunity->category }}</strong></label>@endif
@foreach ($catalog::CATEGORIES as $sectorIndex => $value)<label x-show="{{ $sectorIndex }} < 9 || showAllSectors || category === @js($value)" class="trade-choice trade-sector" :class="category === @js($value) ? 'is-selected' : ''"><input type="radio" name="category" value="{{ $value }}" x-model="category" @checked($config['category'] === $value)><span class="trade-choice-mark">@include('opportunities.partials.icon', ['name' => $value])</span><strong>{{ __('trade.categories.'.$value) }}</strong><span class="trade-choice-check" aria-hidden="true">✓</span></label>@endforeach
</div><button type="button" class="trade-link trade-more-sectors" @click="showAllSectors = !showAllSectors" :aria-expanded="showAllSectors" x-text="showAllSectors ? @js(__('trade.fewer_sectors')) : @js(__('trade.more_sectors'))">{{ __('trade.more_sectors') }}</button></fieldset></section>
    <section class="trade-panel" data-trade-step="1" x-show="step === 1"><h2>{{ __('marketplace.filters.subcategory') }}</h2><p>{{ __('marketplace.category_help') }}</p>
@foreach($catalog::SUBCATEGORIES as $sector=>$children)<fieldset x-show="category === @js($sector)" :disabled="category !== @js($sector)"><legend class="trade-small">{{ __('trade.categories.'.$sector) }}</legend><div class="trade-choice-grid"><label class="trade-choice" :class="subcategory === '' ? 'is-selected' : ''"><input type="radio" name="commercial_details[subcategory]" value="" x-model="subcategory"><strong>{{ __('trade.optional') }}</strong></label>@foreach($children as $child)<label class="trade-choice" :class="subcategory === @js($child) ? 'is-selected' : ''"><input type="radio" name="commercial_details[subcategory]" value="{{ $child }}" x-model="subcategory"><strong>{{ __('marketplace.subcategories.'.$child) }}</strong><span class="trade-choice-check" aria-hidden="true">✓</span></label>@endforeach</div></fieldset>@endforeach</section>
<section class="trade-panel trade-step" data-trade-step="2" x-show="step === 2"><h2>{{ __('trade.step_labels.details') }}</h2>
        <div class="trade-field"><label for="trade-title">{{ __('trade.fields.title') }} *</label><input id="trade-title" name="title" x-model="title" value="{{ $config['title'] }}" required maxlength="255" placeholder="{{ __('trade.example') }}"><p>{{ __('trade.help_title') }}</p></div>
        <div class="trade-field"><label for="trade-description">{{ __('trade.fields.description') }} *</label><textarea id="trade-description" name="description" x-model="description" rows="7" required minlength="20" maxlength="20000" placeholder="{{ __('trade.description_example') }}">{{ $config['description'] }}</textarea><p>{{ __('trade.help_description') }}</p></div>
        <div class="trade-grid">
        @foreach (['country', 'city'] as $field)<div class="trade-field"><label for="trade-{{ $field }}">{{ __('trade.fields.'.$field) }}</label><input id="trade-{{ $field }}" name="{{ $field }}" maxlength="100" value="{{ old($field, $editing ? $opportunity->$field : ($field === 'country' ? 'United Arab Emirates' : '')) }}" @if ($field === 'city') list="trade-emirates" @endif><p>{{ __('trade.help_'.$field) }}</p></div>@endforeach
        </div>
        <datalist id="trade-emirates">@foreach (['Abu Dhabi', 'Dubai', 'Sharjah', 'Ajman', 'Umm Al Quwain', 'Ras Al Khaimah', 'Fujairah'] as $city)<option value="{{ $city }}">@endforeach</datalist>
        @if (!$editing)<div class="trade-grid"><div class="trade-field"><label for="trade-currency">{{ __('trade.fields.currency') }} *</label><select id="trade-currency" name="currency">@foreach ($catalog::CURRENCIES as $currency)<option value="{{ $currency }}" @selected(old('currency', 'AED') === $currency)>{{ $currency }}</option>@endforeach</select></div><div class="trade-field"><label for="trade-expires">{{ __('trade.fields.expires_at') }}</label><input type="date" id="trade-expires" name="expires_at" min="{{ now('Asia/Dubai')->toDateString() }}" value="{{ old('expires_at', now('Asia/Dubai')->addDays(30)->toDateString()) }}"></div></div>@else<p><bdi>{{ $opportunity->currency }}</bdi> · {{ __('trade.expires') }} <bdi>{{ $opportunity->expires_at?->setTimezone('Asia/Dubai')->format('Y-m-d') }}</bdi></p>@endif
    </section>
    <section class="trade-panel trade-step" data-trade-step="3" x-show="step === 3"><h2>{{ __('trade.step_labels.terms') }}</h2><p>{{ __('trade.public_notice') }}</p>
        <div class="trade-field"><label for="trade-product_name">{{ __('trade.fields.product_name') }}</label><input id="trade-product_name" name="commercial_details[product_name]" maxlength="180" value="{{ $details['product_name'] ?? '' }}"><p>{{ __('trade.help_product_name') }}</p></div>
        <div class="trade-field"><label for="trade-specifications">{{ __('trade.fields.specifications') }}</label><textarea id="trade-specifications" name="commercial_details[specifications]" rows="5" maxlength="5000">{{ $details['specifications'] ?? '' }}</textarea><p>{{ __('trade.help_specifications') }}</p></div>
        <div class="trade-grid">
        @foreach (['quantity' => '0.001', 'min_order_quantity' => '0.001', 'unit_price' => '0.01', 'lead_time_days' => '1'] as $field => $step)<div class="trade-field"><label for="trade-{{ $field }}">{{ __('trade.fields.'.$field) }}</label><input type="number" id="trade-{{ $field }}" name="commercial_details[{{ $field }}]" step="{{ $step }}" min="{{ $field === 'unit_price' ? '0' : $step }}" max="{{ $field === 'lead_time_days' ? '3650' : ($field === 'unit_price' ? '999999999.99' : '999999999.999') }}" value="{{ $details[$field] ?? '' }}">@if ($field === 'unit_price')<p>{{ __('trade.unit_price_hint') }}</p>@endif</div>@endforeach
        @foreach (['unit' => $catalog::UNITS, 'price_basis' => ['negotiable', 'fixed', 'quotation'], 'condition' => ['new', 'used', 'refurbished', 'not_applicable'], 'delivery_terms' => $catalog::TERMS] as $field => $values)<div class="trade-field"><label for="trade-{{ $field }}">{{ __('trade.fields.'.$field) }}</label><select id="trade-{{ $field }}" name="commercial_details[{{ $field }}]"><option value="">{{ __('trade.optional') }}</option>@foreach ($values as $value)<option value="{{ $value }}" @selected(($details[$field] ?? '') === $value)>{{ $field === 'unit' ? __('trade.units.'.$value) : (in_array($value, ['exw', 'fca', 'fob', 'cfr', 'cif', 'dap', 'ddp']) ? strtoupper($value) : __('trade.options.'.$value)) }}</option>@endforeach</select></div>@endforeach
        @foreach (['target_market' => 150, 'payment_terms' => 500, 'certifications' => 500] as $field => $max)<div class="trade-field"><label for="trade-{{ $field }}">{{ __('trade.fields.'.$field) }}</label><input id="trade-{{ $field }}" name="commercial_details[{{ $field }}]" maxlength="{{ $max }}" value="{{ $details[$field] ?? '' }}">@if ($field !== 'certifications')<p>{{ __('trade.help_'.$field) }}</p>@endif</div>@endforeach
        </div>
    </section>
    @if (!$editing)
    <fieldset class="trade-panel" data-trade-step="3" x-show="step === 3 && type === 'logistics'" :disabled="type !== 'logistics'"><legend>{{ __('trade.shipping') }}</legend><div class="trade-grid">@foreach (['cargo_type', 'container_type'] as $field)<div class="trade-field"><label for="trade-{{ $field }}">{{ __('trade.fields.'.$field) }}</label><input id="trade-{{ $field }}" name="{{ $field }}" value="{{ old($field) }}" maxlength="255"></div>@endforeach
@foreach (['origin_port_id', 'destination_port_id'] as $field)<div class="trade-field"><label for="trade-{{ $field }}">{{ __('trade.fields.'.$field) }}</label><select id="trade-{{ $field }}" name="{{ $field }}"><option value="">{{ __('trade.optional') }}</option>@foreach ($ports as $port)<option value="{{ $port->id }}" @selected((string)old($field) === (string)$port->id)>{{ $port->name }}</option>@endforeach</select></div>@endforeach</div></fieldset>
    @endif
    @include('opportunities.partials.market-specifications')
    @if (!$editing)<fieldset class="trade-panel" data-trade-step="3" x-show="step === 3 && type === 'investment'" :disabled="type !== 'investment'"><legend>{{ __('trade.legacy_financial') }}</legend><div class="trade-grid">@foreach (['required_capital', 'min_investment'] as $field)<div class="trade-field"><label for="trade-{{ $field }}">{{ __('trade.fields.'.$field) }}</label><input id="trade-{{ $field }}" name="{{ $field }}" type="number" step="0.01" min="0" max="9999999999999.99" value="{{ old($field) }}"></div>@endforeach</div></fieldset>@endif
    <section class="trade-panel trade-step" data-trade-step="4" x-show="step === 4"><h2>{{ __('trade.step_labels.media') }}</h2><p>{{ __('trade.media_intro') }}</p>
@if (!$editing)
<div class="trade-upload" :class="dragging ? 'is-dragging' : ''" @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false" @drop.prevent="dropMedia($event)">
<span class="trade-upload-icon">@include('opportunities.partials.icon', ['name' => 'media'])</span><h3>{{ __('trade.drop_media') }}</h3><p>{{ __('trade.photo_hint') }}</p>
<div class="trade-actions"><button type="button" class="trade-button" @click="chooseMedia('image')">{{ __('trade.add_images') }}</button><button type="button" class="trade-button trade-secondary" @click="chooseMedia('video')">{{ __('trade.add_video') }}</button></div>
<input type="file" id="trade-media" name="media[]" x-ref="mediaInput" @change="addMedia($event)" multiple accept="image/jpeg,image/png,image/webp,video/mp4,video/quicktime,video/webm" class="trade-file-input" aria-label="{{ __('trade.fields.media') }}">
</div><p class="trade-error" x-text="mediaError" role="alert"></p>
<div class="trade-upload-meter"><span><bdi x-text="files.length"></bdi> / 10 {{ __('trade.files_label') }}</span><span><bdi x-text="mediaMegabytes"></bdi> / 20 MB</span></div>
<div class="trade-previews"><template x-for="file in files" :key="file.id"><article class="trade-preview">
<template x-if="file.kind === 'image'"><img :src="file.url" :alt="file.file.name"></template><template x-if="file.kind === 'video'"><video :src="file.url" controls preload="metadata"></video></template>
<div><span class="trade-preview-badge" x-text="file.kind === 'video' ? @js(__('trade.video_label')) : @js(__('trade.image_label'))"></span><p x-text="file.file.name"></p><small x-text="(file.file.size / 1048576).toFixed(1) + ' MB'"></small><button type="button" class="trade-link" @click="removeMedia(file.id)" :aria-label="@js(__('trade.remove_file')) + ' ' + file.file.name">{{ __('trade.remove_file') }}</button></div>
</article></template></div>
@else
<p class="trade-notice">{{ __('trade.existing_media_help') }}</p><div class="trade-previews">@foreach ($opportunity->media as $file)<article class="trade-preview">@if ($file->type === 'image')<img src="{{ asset('storage/'.$file->path) }}" alt="{{ $opportunity->title }}" loading="lazy">@else<video src="{{ asset('storage/'.$file->path) }}" controls preload="metadata"></video>@endif</article>@endforeach</div>
@endif
</section>
<section class="trade-panel trade-step" data-trade-step="5" x-show="step === 5"><h2>{{ __('trade.step_labels.review') }}</h2><p>{{ __('trade.review_help') }}</p>
<div class="trade-review-card"><div class="trade-badges"><span x-text="selectedTypeLabel"></span><span x-show="category" x-text="selectedCategoryLabel"></span></div><h3 x-text="title || @js(__('trade.example'))"></h3><p class="trade-prose" x-text="description"></p><dl class="trade-data"><div><dt>{{ __('trade.fields.country') }}</dt><dd x-text="summary.country"></dd></div><div><dt>{{ __('trade.fields.city') }}</dt><dd x-text="summary.city"></dd></div><div><dt>{{ __('trade.fields.product_name') }}</dt><dd x-text="summary.product_name"></dd></div><div><dt>{{ __('trade.fields.quantity') }}</dt><dd x-text="summary.quantity"></dd></div><div><dt>{{ __('trade.fields.unit_price') }}</dt><dd x-text="summary.price"></dd></div></dl><button type="button" class="trade-link" @click="go(2)">{{ __('trade.edit') }}</button></div>
        <div class="trade-field"><label for="trade-notes">{{ __('trade.fields.notes') }}</label><textarea id="trade-notes" name="notes" rows="3" maxlength="5000">{{ old('notes', $editing ? $opportunity->notes : '') }}</textarea></div>
        <p class="trade-notice">{{ __('trade.auto_publish') }}</p>
        <label class="trade-check"><input type="checkbox" required> <span>{{ __('trade.confirm') }}</span></label>

    </section>
        <aside class="trade-assistant-inline" x-show="step === 2"><section class="trade-panel"><h2>{{ __('trade.ai_title') }}</h2><p>{{ __('trade.guide_intro') }}</p>
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

<div class="trade-wizard-footer"><button type="button" class="trade-button trade-secondary" @click="go(step - 1)" x-show="step > 1">{{ __('trade.previous') }}</button><span class="trade-step-counter"><bdi x-text="step"></bdi> / 5</span><button type="button" class="trade-button" @click="next()" x-show="step < 5">{{ __('trade.next') }} <span aria-hidden="true">{{ app()->getLocale() === 'ar' ? '←' : '→' }}</span></button><button type="submit" class="trade-button" x-show="step === 5">{{ $editing ? __('trade.save') : __('trade.publish') }}</button></div>
    </div></div>
</form>
