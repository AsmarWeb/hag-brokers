<x-market-layout>
    @section('title', __('trade.marketplace'))
    @section('meta_description', __('trade.intro'))
    @php $catalog = \App\Services\Platform\OpportunityCatalog::class; @endphp
    <div class="market-wrap">
        <header class="market-intro">
            <div>
                <p class="trade-eyebrow">HAG MARKETPLACE · B2B</p>
                <h1>{{ __('marketplace.headline') }}</h1>
                <p>{{ __('marketplace.intro') }}</p>
            </div><a class="trade-button" href="{{ route('opportunities.create') }}">{{ __('trade.new') }} ↗</a>
        </header>
        <nav class="market-sectors" aria-label="{{ __('trade.fields.category') }}"><a @class(['active' => !request('category')])
                href="{{ route('opportunities.index') }}">{{ __('trade.all') }}</a>
            @foreach (['marine', 'automotive', 'machinery', 'food', 'electronics', 'real_estate', 'professional_services'] as $sector)
                <a @class(['active' => request('category') === $sector])
                    href="{{ route('opportunities.index', ['category' => $sector, 'lang' => app()->getLocale()]) }}">{{ __('trade.categories.' . $sector) }}</a>
            @endforeach
        </nav>
        @include('admin.platform.messages')
        <div class="market-catalog">
            <aside>
                <details class="market-filters" open>
                    <summary>{{ __('trade.filter') }}</summary>
                    <form method="GET" action="{{ route('opportunities.index') }}"><input type="hidden"
                            name="lang" value="{{ app()->getLocale() }}">
                        <div class="trade-field"><label for="market-search">{{ __('trade.search') }}</label><input
                                id="market-search" type="search" name="search" maxlength="150"
                                value="{{ request('search') }}"></div>
                        @foreach (['type' => $catalog::TYPES, 'category' => $catalog::CATEGORIES, 'subcategory' => $catalog::SUBCATEGORIES[request('category', '')] ?? [], 'vessel_category' => ['yacht', 'boat', 'commercial_vessel'], 'currency' => $catalog::CURRENCIES, 'sort' => ['newest', 'oldest', 'price_asc', 'price_desc']] as $field => $values)
                            <div class="trade-field"><label
                                    for="market-{{ $field }}">{{ __('marketplace.filters.' . $field) }}</label><select
                                    id="market-{{ $field }}" name="{{ $field }}">
                                    <option value="">{{ __('trade.all') }}</option>
                                    @foreach ($values as $value)
                                        <option value="{{ $value }}" @selected(request($field) === $value)>
                                            {{ $field === 'currency' ? $value : ($field === 'type' ? __('trade.types.' . $value) : ($field === 'category' ? __('trade.categories.' . $value) : ($field === 'subcategory' ? __('marketplace.subcategories.' . $value) : ($field === 'sort' ? __('marketplace.sort.' . $value) : __('trade.options.' . $value))))) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                        @foreach (['country', 'min_price', 'max_price'] as $field)
                            <div class="trade-field"><label
                                    for="market-{{ $field }}">{{ __('marketplace.filters.' . $field) }}</label><input
                                    id="market-{{ $field }}" name="{{ $field }}"
                                    value="{{ request($field) }}"
                                    {{ $field === 'country' ? 'maxlength=100' : 'type=number min=0 step=0.01' }}></div>
                        @endforeach
                        <p class="trade-small">{{ __('marketplace.price_help') }}</p><button
                            class="trade-button">{{ __('trade.filter') }}</button><a class="trade-link"
                            href="{{ route('opportunities.index') }}">{{ __('trade.reset') }}</a>
                    </form>
                </details>
            </aside>
            <section>
                <div class="market-results">
                    <h2>{{ __('marketplace.discover') }}</h2><span>{{ $opportunities->total() }}
                        {{ __('trade.results') }}</span>
                </div>
                <div class="market-cards">
                    @forelse($opportunities as $opportunity)@php
                        $cover =
                            $opportunity->media->where('type', 'image')->firstWhere('is_primary', true) ??
                            $opportunity->media->firstWhere('type', 'image');
                        $marine = $opportunity->isLuxuryMarineListing();
                    @endphp
                        <article @class(['market-card', 'market-card-marine' => $marine])><a class="market-card-image"
                                href="{{ route('opportunities.show', $opportunity) }}">
                                @if ($cover)
                                    <img src="{{ asset('storage/' . $cover->path) }}" alt="{{ $opportunity->title }}"
                                    loading="lazy" width="800" height="520">@else<div class="market-no-image">
                                        <span>HAG</span><small>{{ __('marketplace.no_image') }}</small>
                                    </div>
                                @endif
                                <span class="market-card-type">
                                    {{ __('trade.types.' . $opportunity->opportunity_type) }}</span>
                            </a>
                            <div class="market-card-content">
                                <p class="trade-eyebrow">
                                    {{ __('trade.categories.' . ($opportunity->category ?? 'other')) }} @if ($opportunity->commercial_details['subcategory'] ?? null)
                                        ·
                                        {{ $catalog::display('subcategory', $opportunity->commercial_details['subcategory']) }}
                                    @endif
                                </p>
                                <h3><a
                                        href="{{ route('opportunities.show', $opportunity) }}">{{ $opportunity->title }}</a>
                                </h3>
                                <p>{{ \Illuminate\Support\Str::limit($opportunity->description, 100) }}</p>
                                <div class="market-card-specs">
                                    @if ($opportunity->vessel)
                                        @foreach (['length_m', 'year_built', 'cabins'] as $spec)
                                            @if ($opportunity->vessel->$spec !== null)
                                                <span>{{ __('marketplace.vessel.' . $spec) }}
                                                    <bdi>{{ $opportunity->vessel->$spec }}</bdi></span>
                                            @endif
                                        @endforeach
                                    @endif
                                </div>
                                <div class="market-card-bottom">
                                    <span>{{ $opportunity->city ?: $opportunity->country }}</span><strong>
                                        @if ($opportunity->vessel?->price_on_request)
                                            {{ __('marketplace.on_request') }}
                                        @elseif($opportunity->estimated_value !== null)
                                            <bdi>{{ $opportunity->estimated_value }}
                                                {{ $opportunity->currency }}</bdi><small>{{ __('marketplace.indicative') }}</small>@else{{ __('marketplace.on_request') }}
                                        @endif
                                    </strong>
                                </div>
                                <p class="market-publisher">{{ $opportunity->customer?->company_name }}</p>
                            </div>
                        </article>
                    @empty<div class="trade-panel">
                            <h3>{{ __('trade.empty') }}</h3><a class="trade-link"
                                href="{{ route('opportunities.index') }}">{{ __('trade.reset') }}</a>
                        </div>
                    @endforelse
                </div>
                <div class="trade-pagination">{{ $opportunities->links() }}</div>
            </section>
        </div>
    </div>
</x-market-layout>س
