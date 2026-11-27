@php
    $errorSteps = [
        1 => ['cargo_type', 'vessel_type'],
        2 => ['quantity', 'description', 'dwt_capacity'],
        3 => [
            'loading_port_id', 'discharging_port_id', 'laycan_from', 'laycan_to',
            'readiness_date', 'current_port_id', 'current_position', 'open_date',
            'preferred_trading_area',
        ],
        4 => ['company_name', 'contact_name', 'email', 'phone'],
        5 => ['terms_accepted'],
    ];
    $initialStep = 1;
    foreach ($errorSteps as $candidateStep => $fields) {
        if (collect($fields)->contains(fn ($field) => $errors->has($field))) {
            $initialStep = $candidateStep;
            break;
        }
    }
    $wizardConfig = [
        'kind' => $kind,
        'step' => $initialStep,
        'stepLabels' => array_values(__('maritime_board.form_steps')),
        'cargoType' => old('cargo_type', ''),
        'vesselType' => old('vessel_type', ''),
        'cargoTypes' => __('maritime_board.cargo_types'),
        'vesselTypes' => __('maritime_board.vessel_types'),
        'differentPortsMessage' => __('maritime_board.different_ports_error'),
        'laycanOrderMessage' => __('maritime_board.laycan_order_error'),
        'positionRequiredMessage' => __('maritime_board.current_position_required'),
        'portSelections' => [
            'loading_port_id' => (string) old('loading_port_id', ''),
            'discharging_port_id' => (string) old('discharging_port_id', ''),
            'current_port_id' => (string) old('current_port_id', ''),
        ],
        'portCountries' => __('maritime_board.port_countries'),
    ];
@endphp

<x-market-layout>
    @section('title', $kind === 'cargo' ? __('maritime_board.post_cargo') : __('maritime_board.list_vessel'))
    <div class="trade-page" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
        <div class="trade-wrap">
            <a class="trade-link" href="{{ route('maritime-board.index', ['tab' => $kind === 'cargo' ? 'cargoes' : 'vessels']) }}">
                {{ __('maritime_board.title') }}
            </a>

            <header class="trade-hero">
                <p class="trade-eyebrow">{{ __('maritime_board.eyebrow') }}</p>
                <h1>{{ $kind === 'cargo' ? __('maritime_board.post_cargo') : __('maritime_board.list_vessel') }}</h1>
                <p>{{ __('maritime_board.review_notice') }}</p>
            </header>

            @include('admin.platform.messages')
            @if ($errors->any())
                <div role="alert" class="trade-panel mb-5 border-rose-300 dark:border-rose-900">
                    <strong class="text-rose-700 dark:text-rose-300">{{ __('maritime_board.validation_heading') }}</strong>
                    <ul class="mt-2 list-disc ps-5 text-sm text-rose-700 dark:text-rose-300">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="trade-wizard" method="POST" action="{{ route('maritime-board.submit.store', $kind) }}"
                  novalidate @submit="submit($event)" x-data="maritimeBoardWizard(@js($wizardConfig))">
                @csrf

                <div class="trade-wizard-top">
                    <div>
                        <span class="trade-eyebrow">{{ __('maritime_board.guided_submission') }}</span>
                        <h2 x-text="stepLabels[step - 1]"></h2>
                    </div>
                    <span class="trade-progress-text"><bdi x-text="Math.round(step / 5 * 100)"></bdi>%</span>
                </div>
                <div class="trade-progress-track"><span :style="{ width: (step / 5 * 100) + '%' }"></span></div>

                <div class="trade-wizard-grid">
                    <nav class="trade-wizard-nav" aria-label="{{ __('maritime_board.steps_label') }}">
                        @foreach (['type', 'details', 'availability', 'contact', 'review'] as $index => $stage)
                            <button type="button"
                                    @click="go({{ $index + 1 }})"
                                    :class="{ 'is-current': step === {{ $index + 1 }}, 'is-complete': step > {{ $index + 1 }} }"
                                    :aria-current="step === {{ $index + 1 }} ? 'step' : null">
                                <span class="trade-nav-number">{{ $index + 1 }}</span>
                                <span>
                                    <strong>{{ __('maritime_board.form_steps.'.$stage) }}</strong>
                                    <small>{{ __('maritime_board.form_step_hints.'.$stage) }}</small>
                                </span>
                            </button>
                        @endforeach
                        <div class="trade-wizard-tip">
                            <strong>{{ __('maritime_board.guidance_title') }}</strong>
                            <p>{{ __('maritime_board.guidance_text') }}</p>
                        </div>
                    </nav>

                    <div class="trade-sections">
                        {{-- Step 1: type cards --}}
                        <section class="trade-panel trade-step" data-trade-step="1" x-show="step === 1">
                            <div class="trade-step-heading">
                                <span class="trade-step-number">01</span>
                                <div>
                                    <p class="trade-eyebrow">HAG MARITIME BROKERAGE</p>
                                    <h2>{{ $kind === 'cargo' ? __('maritime_board.choose_cargo_type') : __('maritime_board.choose_vessel_type') }}</h2>
                                    <p>{{ $kind === 'cargo' ? __('maritime_board.cargo_type_help') : __('maritime_board.vessel_type_help') }}</p>
                                </div>
                            </div>

                            @if ($kind === 'cargo')
                                <fieldset class="trade-card-group">
                                    <legend>{{ __('maritime_board.cargo_type') }} *</legend>
                                    <div class="trade-choice-grid trade-sector-grid">
                                        @php $cargoIcons = ['dry_bulk' => '⚓', 'breakbulk' => '📦', 'project' => '🏗️', 'container' => '🚢', 'liquid' => '💧', 'petroleum' => '🛢️', 'general' => '◈', 'other' => '＋']; @endphp
                                        @foreach (__('maritime_board.cargo_types') as $key => $label)
                                            <label class="trade-choice trade-sector" :class="cargoType === @js($key) ? 'is-selected' : ''">
                                                <input type="radio" name="cargo_type" value="{{ $key }}" x-model="cargoType" @checked($wizardConfig['cargoType'] === $key) required>
                                                <span class="trade-choice-mark" aria-hidden="true">{{ $cargoIcons[$key] ?? '⚓' }}</span>
                                                <strong>{{ $label }}</strong>
                                                <small class="marine-choice-description">{{ __('maritime_board.cargo_type_descriptions.'.$key) }}</small>
                                                <span class="trade-choice-check" aria-hidden="true">✓</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </fieldset>
                            @else
                                <fieldset class="trade-card-group">
                                    <legend>{{ __('maritime_board.vessel_type') }} *</legend>
                                    <div class="trade-choice-grid trade-sector-grid">
                                        @php $vesselIcons = ['general_cargo' => '⚓', 'bulk' => '🚢', 'tanker' => '🛢️', 'lct' => '⛴️', 'ro_ro' => '🚚', 'container' => '📦', 'other' => '＋']; @endphp
                                        @foreach (__('maritime_board.vessel_types') as $key => $label)
                                            <label class="trade-choice trade-sector" :class="vesselType === @js($key) ? 'is-selected' : ''">
                                                <input type="radio" name="vessel_type" value="{{ $key }}" x-model="vesselType" @checked($wizardConfig['vesselType'] === $key) required>
                                                <span class="trade-choice-mark" aria-hidden="true">{{ $vesselIcons[$key] ?? '⚓' }}</span>
                                                <strong>{{ $label }}</strong>
                                                <small class="marine-choice-description">{{ __('maritime_board.vessel_type_descriptions.'.$key) }}</small>
                                                <span class="trade-choice-check" aria-hidden="true">✓</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </fieldset>
                            @endif
                            <div class="marine-guidance-panel">
                                <span class="marine-guidance-icon" aria-hidden="true">ⓘ</span>
                                <p>{{ __('maritime_board.type_selection_tip') }}</p>
                            </div>
                        </section>

                        {{-- Step 2: commercial particulars --}}
                        <section class="trade-panel trade-step" data-trade-step="2" x-show="step === 2">
                            <div class="trade-step-heading">
                                <span class="trade-step-number">02</span>
                                <div>
                                    <p class="trade-eyebrow">{{ __('maritime_board.commercial_particulars') }}</p>
                                    <h2>{{ $kind === 'cargo' ? __('maritime_board.cargo_details_title') : __('maritime_board.vessel_details_title') }}</h2>
                                    <p>{{ $kind === 'cargo' ? __('maritime_board.cargo_details_help') : __('maritime_board.vessel_details_help') }}</p>
                                </div>
                            </div>

                            @if ($kind === 'cargo')
                                <div class="trade-field">
                                    <label for="marine-quantity">{{ __('maritime_board.quantity') }} *</label>
                                    <input id="marine-quantity" type="number" name="quantity" min="0.001" max="999999999999.999" step="0.001" value="{{ old('quantity') }}" required>
                                    <p>{{ __('maritime_board.quantity_help') }}</p>
                                    <p class="marine-example">{{ __('maritime_board.quantity_example') }}</p>
                                </div>
                                <div class="trade-field">
                                    <label for="marine-description">{{ __('maritime_board.description') }} *</label>
                                    <textarea id="marine-description" name="description" rows="6" minlength="20" maxlength="5000" required>{{ old('description') }}</textarea>
                                    <p>{{ __('maritime_board.private_description_hint') }}</p>
                                    <div class="marine-guidance-panel marine-guidance-panel--stacked">
                                        <strong>{{ __('maritime_board.description_checklist_title') }}</strong>
                                        <ul class="marine-guidance-list">
                                            @foreach(__('maritime_board.description_checklist') as $item)
                                                <li>{{ $item }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @else
                                <div class="trade-field">
                                    <label for="marine-dwt">{{ __('maritime_board.dwt_capacity') }} (MT) *</label>
                                    <input id="marine-dwt" type="number" name="dwt_capacity" min="0.01" max="9999999999999.99" step="0.01" value="{{ old('dwt_capacity') }}" required>
                                    <p>{{ __('maritime_board.dwt_help') }}</p>
                                    <p class="marine-example">{{ __('maritime_board.dwt_example') }}</p>
                                </div>
                                <p class="trade-notice">{{ __('maritime_board.vessel_capacity_notice') }}</p>
                                <div class="marine-guidance-panel"><span class="marine-guidance-icon" aria-hidden="true">ⓘ</span><p>{{ __('maritime_board.vessel_dwt_explained') }}</p></div>
                            @endif
                        </section>

                        {{-- Step 3: route and availability --}}
                        <section class="trade-panel trade-step" data-trade-step="3" x-show="step === 3">
                            <div class="trade-step-heading">
                                <span class="trade-step-number">03</span>
                                <div>
                                    <p class="trade-eyebrow">{{ __('maritime_board.route_and_schedule') }}</p>
                                    <h2>{{ __('maritime_board.form_steps.availability') }}</h2>
                                    <p>{{ $kind === 'cargo' ? __('maritime_board.cargo_route_help') : __('maritime_board.vessel_availability_help') }}</p>
                                </div>
                            </div>

                            @if ($kind === 'cargo')
                                <div class="trade-grid">
                                    @include('maritime-board.partials.port-picker', [
                                        'ports' => $ports,
                                        'fieldName' => 'loading_port_id',
                                        'searchKey' => 'loading',
                                        'fieldId' => 'marine-loading-port',
                                        'fieldLabel' => __('maritime_board.loading_port'),
                                        'fieldHelp' => __('maritime_board.loading_port_help'),
                                        'required' => true,
                                    ])
                                    @include('maritime-board.partials.port-picker', [
                                        'ports' => $ports,
                                        'fieldName' => 'discharging_port_id',
                                        'searchKey' => 'discharging',
                                        'fieldId' => 'marine-discharging-port',
                                        'fieldLabel' => __('maritime_board.discharging_port'),
                                        'fieldHelp' => __('maritime_board.discharging_port_help'),
                                        'required' => true,
                                    ])
                                    <div class="trade-field">
                                        <label for="marine-laycan-from">{{ __('maritime_board.laycan_from') }} *</label>
                                        <input id="marine-laycan-from" type="date" name="laycan_from" min="{{ now('Asia/Dubai')->toDateString() }}" value="{{ old('laycan_from') }}" required>
                                        <p>{{ __('maritime_board.laycan_from_help') }}</p>
                                    </div>
                                    <div class="trade-field">
                                        <label for="marine-laycan-to">{{ __('maritime_board.laycan_to') }} *</label>
                                        <input id="marine-laycan-to" type="date" name="laycan_to" min="{{ now('Asia/Dubai')->toDateString() }}" value="{{ old('laycan_to') }}" required>
                                        <p>{{ __('maritime_board.laycan_to_help') }}</p>
                                    </div>
                                    <div class="trade-field trade-span-full">
                                        <label for="marine-readiness">{{ __('maritime_board.readiness_date') }}</label>
                                        <input id="marine-readiness" type="date" name="readiness_date" value="{{ old('readiness_date') }}">
                                        <p>{{ __('maritime_board.readiness_date_help') }}</p>
                                    </div>
                                </div>
                            @else
                                <div class="trade-grid">
                                    @include('maritime-board.partials.port-picker', [
                                        'ports' => $ports,
                                        'fieldName' => 'current_port_id',
                                        'searchKey' => 'current',
                                        'fieldId' => 'marine-current-port',
                                        'fieldLabel' => __('maritime_board.current_port'),
                                        'fieldHelp' => __('maritime_board.current_port_help'),
                                        'required' => false,
                                    ])
                                    <div class="trade-field">
                                        <label for="marine-current-position">{{ __('maritime_board.current_position') }}</label>
                                        <input id="marine-current-position" name="current_position" maxlength="180" value="{{ old('current_position') }}">
                                        <p>{{ __('maritime_board.current_position_help') }}</p>
                                        <p class="marine-example">{{ __('maritime_board.current_position_example') }}</p>
                                    </div>
                                    <div class="trade-field">
                                        <label for="marine-open-date">{{ __('maritime_board.open_date') }} *</label>
                                        <input id="marine-open-date" type="date" name="open_date" min="{{ now('Asia/Dubai')->toDateString() }}" value="{{ old('open_date') }}" required>
                                    </div>
                                    <div class="trade-field">
                                        <label for="marine-trading-area">{{ __('maritime_board.preferred_trading_area') }} *</label>
                                        <input id="marine-trading-area" name="preferred_trading_area" maxlength="255" value="{{ old('preferred_trading_area') }}" required>
                                        <p>{{ __('maritime_board.trading_area_help') }}</p>
                                        <p class="marine-example">{{ __('maritime_board.trading_area_example') }}</p>
                                    </div>
                                </div>
                            @endif
                        </section>

                        {{-- Step 4: private contact details --}}
                        <section class="trade-panel trade-step" data-trade-step="4" x-show="step === 4">
                            <div class="trade-step-heading">
                                <span class="trade-step-number">04</span>
                                <div>
                                    <p class="trade-eyebrow">{{ __('maritime_board.private_contact') }}</p>
                                    <h2>{{ __('maritime_board.contact_details_title') }}</h2>
                                    <p>{{ __('maritime_board.contact_details_help') }}</p>
                                </div>
                            </div>
                            <div class="trade-grid">
                                <div class="trade-field">
                                    <label for="marine-company">{{ __('maritime_board.company_name') }} *</label>
                                    <input id="marine-company" name="company_name" value="{{ old('company_name') }}" maxlength="200" required>
                                    <p>{{ __('maritime_board.company_name_help') }}</p>
                                </div>
                                <div class="trade-field">
                                    <label for="marine-contact">{{ __('maritime_board.name') }} *</label>
                                    <input id="marine-contact" name="contact_name" value="{{ old('contact_name') }}" maxlength="150" required>
                                    <p>{{ __('maritime_board.contact_name_help') }}</p>
                                </div>
                                <div class="trade-field">
                                    <label for="marine-email">{{ __('maritime_board.email') }} *</label>
                                    <input id="marine-email" type="email" name="email" value="{{ old('email') }}" maxlength="255" required>
                                    <p>{{ __('maritime_board.email_help') }}</p>
                                </div>
                                <div class="trade-field">
                                    <label for="marine-phone">{{ __('maritime_board.phone') }} *</label>
                                    <input id="marine-phone" type="tel" name="phone" value="{{ old('phone') }}" maxlength="50" required>
                                    <p>{{ __('maritime_board.phone_help') }}</p>
                                </div>
                            </div>
                            <p class="trade-notice">{{ __('maritime_board.contact_privacy_notice') }}</p>
                            <div class="marine-guidance-panel"><span class="marine-guidance-icon" aria-hidden="true">🔒</span><p>{{ __('maritime_board.contact_data_notice') }}</p></div>
                        </section>

                        {{-- Step 5: review and consent --}}
                        <section class="trade-panel trade-step" data-trade-step="5" x-show="step === 5">
                            <div class="trade-step-heading">
                                <span class="trade-step-number">05</span>
                                <div>
                                    <p class="trade-eyebrow">HAG · FINAL CHECK</p>
                                    <h2>{{ __('maritime_board.review_submission') }}</h2>
                                    <p>{{ __('maritime_board.review_help') }}</p>
                                </div>
                            </div>

                            <div class="trade-review-card">
                                <div class="trade-badges">
                                    <span>{{ $kind === 'cargo' ? __('maritime_board.available_cargo') : __('maritime_board.open_vessel') }}</span>
                                    <span x-text="selectedTypeLabel"></span>
                                </div>
                                @if ($kind === 'cargo')
                                    <dl class="trade-data">
                                        <div><dt>{{ __('maritime_board.cargo_type') }}</dt><dd x-text="summary.type"></dd></div>
                                        <div><dt>{{ __('maritime_board.quantity') }}</dt><dd x-text="summary.quantity === '—' ? '—' : summary.quantity + ' MT'"></dd></div>
                                        <div><dt>{{ __('maritime_board.loading_port') }}</dt><dd x-text="summary.loadingPort"></dd></div>
                                        <div><dt>{{ __('maritime_board.discharging_port') }}</dt><dd x-text="summary.dischargingPort"></dd></div>
                                        <div><dt>{{ __('maritime_board.laycan_from') }}</dt><dd x-text="summary.laycanFrom"></dd></div>
                                        <div><dt>{{ __('maritime_board.laycan_to') }}</dt><dd x-text="summary.laycanTo"></dd></div>
                                        <div><dt>{{ __('maritime_board.readiness_date') }}</dt><dd x-text="summary.readinessDate"></dd></div>
                                    </dl>
                                    <div class="trade-review-description"><h3>{{ __('maritime_board.description') }}</h3><p class="trade-prose" x-text="summary.description"></p></div>
                                @else
                                    <dl class="trade-data">
                                        <div><dt>{{ __('maritime_board.vessel_type') }}</dt><dd x-text="summary.type"></dd></div>
                                        <div><dt>{{ __('maritime_board.dwt_capacity') }}</dt><dd x-text="summary.dwtCapacity === '—' ? '—' : summary.dwtCapacity + ' MT'"></dd></div>
                                        <div><dt>{{ __('maritime_board.current_port') }}</dt><dd x-text="summary.currentPort"></dd></div>
                                        <div><dt>{{ __('maritime_board.current_position') }}</dt><dd x-text="summary.currentPosition"></dd></div>
                                        <div><dt>{{ __('maritime_board.open_date') }}</dt><dd x-text="summary.openDate"></dd></div>
                                        <div><dt>{{ __('maritime_board.preferred_trading_area') }}</dt><dd x-text="summary.preferredTradingArea"></dd></div>
                                    </dl>
                                @endif
                                <button type="button" class="trade-link mt-4" @click="go(2)">{{ __('maritime_board.edit_details') }}</button>
                            </div>

                            <div class="trade-review-card">
                                <h3>{{ __('maritime_board.private_contact') }}</h3>
                                <dl class="trade-data">
                                    <div><dt>{{ __('maritime_board.company_name') }}</dt><dd x-text="summary.company"></dd></div>
                                    <div><dt>{{ __('maritime_board.name') }}</dt><dd x-text="summary.contact"></dd></div>
                                    <div><dt>{{ __('maritime_board.email') }}</dt><dd x-text="summary.email"></dd></div>
                                    <div><dt>{{ __('maritime_board.phone') }}</dt><dd x-text="summary.phone"></dd></div>
                                </dl>
                                <button type="button" class="trade-link mt-4" @click="go(4)">{{ __('maritime_board.edit_contact') }}</button>
                            </div>

                            <div class="marine-guidance-panel marine-guidance-panel--stacked">
                                <strong>{{ __('maritime_board.after_submission_title') }}</strong>
                                <p>{{ __('maritime_board.after_submission_help') }}</p>
                            </div>
                            <p class="trade-notice">{{ __('maritime_board.review_notice') }}</p>
                            <label class="trade-check">
                                <input type="checkbox" name="terms_accepted" value="1" required @checked(old('terms_accepted'))>
                                <span>
                                    {{ __('maritime_board.terms_accept') }}
                                    <a class="trade-link" href="{{ route('legal.show', 'terms') }}" target="_blank" rel="noopener">{{ __('maritime_board.terms_link') }}</a>
                                </span>
                            </label>
                        </section>

                        <div class="trade-wizard-footer">
                            <button type="button" class="trade-button trade-secondary" @click="go(step - 1)" x-show="step > 1">
                                {{ __('maritime_board.previous') }}
                            </button>
                            <span class="trade-step-counter"><bdi x-text="step"></bdi> / 5</span>
                            <button type="button" class="trade-button" @click="next()" x-show="step < 5">
                                {{ __('maritime_board.next') }} <span aria-hidden="true">{{ app()->getLocale() === 'ar' ? '←' : '→' }}</span>
                            </button>
                            <button type="submit" class="trade-button" x-show="step === 5">
                                {{ __('maritime_board.submit_listing') }}
                            </button>
                            <a class="trade-button trade-secondary" href="{{ route('maritime-board.index', ['tab' => $kind === 'cargo' ? 'cargoes' : 'vessels']) }}">
                                {{ __('maritime_board.cancel') }}
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-market-layout>
