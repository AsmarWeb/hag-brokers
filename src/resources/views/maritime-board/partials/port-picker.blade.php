@php
    $selectedPort = (string) old($fieldName, '');
    $countryFlags = [
        'AE' => '🇦🇪', 'SA' => '🇸🇦', 'OM' => '🇴🇲', 'QA' => '🇶🇦',
        'BH' => '🇧🇭', 'IQ' => '🇮🇶', 'KW' => '🇰🇼',
    ];
@endphp

<fieldset class="marine-port-picker trade-span-full" data-port-picker="{{ $searchKey }}">
    <legend class="marine-port-legend">
        {{ $fieldLabel }} @if ($required) <span aria-hidden="true">*</span> @else <span class="marine-optional-label">{{ __('maritime_board.optional_label') }}</span> @endif
    </legend>
    <p class="marine-port-help">{{ $fieldHelp }}</p>

    <div class="marine-port-toolbar">
        <label class="marine-port-search" for="{{ $fieldId }}-search">
            <span aria-hidden="true">⌕</span>
            <input id="{{ $fieldId }}-search" type="search"
                   x-model.debounce.120ms="portSearch.{{ $searchKey }}"
                   placeholder="{{ __('maritime_board.port_search_placeholder') }}"
                   autocomplete="off">
        </label>
        <div class="marine-port-filters" role="group" aria-label="{{ __('maritime_board.port_regions_label') }}">
            <button type="button" @click="portFilters.{{ $searchKey }} = 'all'"
                    :class="portFilters.{{ $searchKey }} === 'all' ? 'is-active' : ''"
                    :aria-pressed="portFilters.{{ $searchKey }} === 'all'">{{ __('maritime_board.port_filter_all') }}</button>
            <button type="button" @click="portFilters.{{ $searchKey }} = 'uae'"
                    :class="portFilters.{{ $searchKey }} === 'uae' ? 'is-active' : ''"
                    :aria-pressed="portFilters.{{ $searchKey }} === 'uae'">{{ __('maritime_board.port_filter_uae') }}</button>
            <button type="button" @click="portFilters.{{ $searchKey }} = 'gulf'"
                    :class="portFilters.{{ $searchKey }} === 'gulf' ? 'is-active' : ''"
                    :aria-pressed="portFilters.{{ $searchKey }} === 'gulf'">{{ __('maritime_board.port_filter_gulf') }}</button>
        </div>
    </div>

    <div class="marine-port-grid" role="radiogroup" aria-label="{{ $fieldLabel }}">
        @foreach ($ports as $port)
            @php
                $countryCode = strtoupper((string) ($port->country_code ?? ''));
                $countryName = __('maritime_board.port_countries.'.$countryCode);
                $flag = $countryFlags[$countryCode] ?? '⚓';
            @endphp
            <label class="marine-port-card"
                   data-port-card="{{ $searchKey }}"
                   :class="portSelections.{{ $fieldName }} === @js((string) $port->id) ? 'is-selected' : ''"
                   x-show="matchesPort(@js($port->name), @js($countryCode), '{{ $searchKey }}')">
                <input class="marine-port-card-input" type="radio" name="{{ $fieldName }}"
                       value="{{ $port->id }}" x-model="portSelections.{{ $fieldName }}"
                       @checked($selectedPort === (string) $port->id)
                       @if ($required) required @endif>
                <span class="marine-port-card-content">
                    <span class="marine-port-flag" aria-hidden="true">{{ $flag }}</span>
                    <span class="marine-port-card-copy">
                        <strong class="marine-port-name">{{ $port->name }}</strong>
                        <span class="marine-port-country">{{ $countryName }}</span>
                        <small class="marine-port-code">{{ $countryCode }}</small>
                    </span>
                    <span class="marine-port-check" aria-hidden="true">✓</span>
                </span>
            </label>
        @endforeach
    </div>

    <p class="marine-port-empty" x-show="portResultsCount('{{ $searchKey }}') === 0" x-cloak>
        {{ __('maritime_board.port_no_results') }}
    </p>
    <p class="marine-port-footnote">{{ __('maritime_board.port_selection_note') }}</p>
</fieldset>
