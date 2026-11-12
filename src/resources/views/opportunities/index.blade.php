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

        @php
            $marketLocale = app()->getLocale() === 'ar' ? 'ar' : 'en';
        @endphp

        <section class="market-category-section" aria-labelledby="market-category-heading">
            <div class="market-category-heading">
                <div>
                    <p class="trade-eyebrow">HAG BROKERS · MARKET SECTORS</p>
                    <h2 id="market-category-heading">
                        {{ $marketLocale === 'ar' ? 'استكشف المجالات التجارية' : 'Explore Business Sectors' }}
                    </h2>
                    <p>
                        {{ $marketLocale === 'ar'
                            ? 'اكتشف المنتجات والقطاعات والفرص التجارية المتاحة عبر شبكة HAG Brokers.'
                            : 'Explore products, industries and business opportunities across the HAG Brokers network.' }}
                    </p>
                </div>
                @if (request('category'))
                    <a class="market-category-reset" href="{{ route('opportunities.index', ['lang' => $marketLocale]) }}">
                        {{ $marketLocale === 'ar' ? 'عرض جميع المجالات' : 'View all sectors' }}
                    </a>
                @endif
            </div>

            @if ($selectedCategory)
                <div class="market-category-detail">
                    @if ($selectedCategory['image'])
                        <img src="{{ $selectedCategory['image'] }}"
                            alt="{{ $selectedCategory['title_' . $marketLocale] }}"
                            loading="eager">
                    @else
                        <div class="market-category-detail-fallback"><span>HAG</span></div>
                    @endif
                    <div class="market-category-detail-copy">
                        <span>{{ $marketLocale === 'ar' ? 'استكشف المجال' : 'EXPLORE SECTOR' }}</span>
                        <h2>{{ $selectedCategory['title_' . $marketLocale] }}</h2>
                        @if ($selectedCategory['description_' . $marketLocale])
                            <p>{{ $selectedCategory['description_' . $marketLocale] }}</p>
                        @endif
                        <strong>
                            {{ $opportunities->total() }}
                            {{ $marketLocale === 'ar' ? 'فرصة متاحة' : 'opportunities available' }}
                        </strong>
                    </div>
                </div>
            @endif

            <div class="market-category-grid">
                @foreach ($categoryCards as $card)
                    @php
                        $cardTitle = $card['title_' . $marketLocale] ?: __('trade.categories.' . $card['code']);
                        $cardDescription = $card['description_' . $marketLocale] ?? '';
                        $cardActive = request('category') === $card['code'];
                    @endphp
                    <a class="market-category-card{{ $cardActive ? ' is-active' : '' }}"
                        href="{{ route('opportunities.index', ['category' => $card['code'], 'lang' => $marketLocale]) }}"
                        @if ($cardActive) aria-current="page" @endif>
                        <div class="market-category-cover">
                            @if ($card['image'])
                                <img src="{{ $card['image'] }}" alt="{{ $cardTitle }}" loading="lazy">
                            @else
                                <div class="market-category-fallback">
                                    <span>HAG</span>
                                    <small>{{ strtoupper($card['code']) }}</small>
                                </div>
                            @endif
                            @if ($card['featured'])
                                <span class="market-category-featured">
                                    {{ $marketLocale === 'ar' ? 'مجال مميز' : 'Featured' }}
                                </span>
                            @endif
                            <span class="market-category-arrow" aria-hidden="true">↗</span>
                        </div>
                        <div class="market-category-copy">
                            <h3>{{ $cardTitle }}</h3>
                            @if ($cardDescription)
                                <p>{{ $cardDescription }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </section>

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
                    @empty
                        <div class="trade-panel market-empty-state">
                            @if (request()->hasAny(['search', 'category', 'type', 'country', 'subcategory', 'vessel_category', 'currency', 'min_price', 'max_price']))
                                <h3>{{ __('trade.empty') }}</h3>
                                <a class="trade-link" href="{{ route('opportunities.index', ['lang' => $marketLocale]) }}">
                                    {{ __('trade.reset') }}
                                </a>
                            @else
                                <span class="market-empty-mark">HAG</span>
                                <h3>{{ $marketLocale === 'ar' ? 'فرص جديدة بانتظارك' : 'New opportunities are on the way' }}</h3>
                                <p>
                                    {{ $marketLocale === 'ar'
                                        ? 'استكشف المجالات أعلاه، وستظهر الفرص المنشورة ضمن كل مجال هنا.'
                                        : 'Explore the sectors above. Published opportunities will appear here as they become available.' }}
                                </p>
                            @endif
                        </div>
                    @endforelse
                </div>
                <div class="trade-pagination">{{ $opportunities->links() }}</div>
            </section>
        </div>
    </div>
</x-market-layout>
