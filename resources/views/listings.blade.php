@extends('layouts.site')

{{-- The map search (/properties), three columns as on islanders-properties.com: filters | map | listings. The list follows the map
     (only what is in view, unless switched off); hovering a card lights its pin, a pin lights its card.
     resources/js/home.js holds the logic (Alpine `propertyMap`); app.css (body.site) the look. --}}
@php
  $labels = [
      'perMonth' => __('home.per_month'),
      'reserved' => __('home.reserved'),
      'orRent' => __('home.or_rent'),
      'in' => __('home.type_in_area'),
      'bedrooms' => [__('home.bedroom'), __('home.bedrooms_n')],
      'bathrooms' => [__('home.bathroom'), __('home.bathrooms_n')],
      'area' => __('home.living'),
      'land' => __('home.land'),
  ];
@endphp

@section('title', __('home.title'))

@push('styles')
  <link rel="stylesheet" href="https://api.mapbox.com/mapbox-gl-js/v3.9.4/mapbox-gl.css">
@endpush

@push('scripts')
  <script src="https://api.mapbox.com/mapbox-gl-js/v3.9.4/mapbox-gl.js"></script>
  @vite('resources/js/home.js')
@endpush

@section('content')
  <div class="home-grid" x-data="propertyMap(@js($listings), @js($map), @js($labels))"
    :class="{ 'filters-open': filtersOpen }">

    {{-- 1. Filters --}}
    <aside class="home-filters kt-scrollable-y-auto" aria-label="@lang('home.filters')">
      <div class="flex items-baseline justify-between">
        <h2 class="flex items-center gap-2 text-xl font-medium"><i class="ki-filled ki-filter text-[var(--lagoon)]"></i> @lang('home.filters')</h2>
        <button type="button" class="link-quiet" @click="reset()" :disabled="!filtered">@lang('home.reset')</button>
      </div>

      <div class="home-filter">
        <span class="home-filter-label"><i class="ki-filled ki-price-tag"></i> @lang('home.offer')</span>
        <div class="tabs">
          @foreach (['all' => 'home.any', 'sale' => 'home.buy', 'rent' => 'home.rent'] as $value => $label)
            <button type="button" class="tab" :class="{ 'is-on': offer === '{{ $value }}' }" @click="offer = '{{ $value }}'">@lang($label)</button>
          @endforeach
        </div>
      </div>

      <div class="home-filter">
        <span class="home-filter-label"><i class="ki-filled ki-category"></i> @lang('home.type')</span>
        <div class="flex flex-wrap gap-2">
          @foreach ($types as $value => $label)
            <button type="button" class="chip" :class="{ 'is-on': types.includes('{{ $value }}') }" @click="toggle('types', '{{ $value }}')">{{ $label }}</button>
          @endforeach
        </div>
      </div>

      <div class="home-filter">
        <span class="home-filter-label"><i class="ki-filled ki-dollar"></i> <span x-text="offer === 'rent' ? @js(__('home.price_rent')) : @js(__('home.price'))"></span></span>
        <div class="flex items-center gap-3">
          <input type="text" inputmode="numeric" class="field-box" :value="minPrice ? money(minPrice) : ''" @input="typePrice('minPrice', $el)"
            placeholder="@lang('home.min')" aria-label="@lang('home.min')">
          <span class="text-[var(--mist)]">–</span>
          <input type="text" inputmode="numeric" class="field-box" :value="maxPrice ? money(maxPrice) : ''" @input="typePrice('maxPrice', $el)"
            placeholder="@lang('home.max')" aria-label="@lang('home.max')">
        </div>
        <div class="price-slider" :style="sliderFill()">
          <input type="range" min="0" :max="priceCeiling" :step="priceStep" :value="minPrice || 0"
            @input="slide('minPrice', $el)" aria-label="@lang('home.min')">
          <input type="range" min="0" :max="priceCeiling" :step="priceStep" :value="maxPrice || priceCeiling"
            @input="slide('maxPrice', $el)" aria-label="@lang('home.max')">
        </div>
      </div>

      <div class="home-filter">
        <span class="home-filter-label"><i class="ki-filled ki-moon"></i> @lang('home.bedrooms')</span>
        <div class="tabs">
          @foreach ([0 => __('home.any'), 1 => '1+', 2 => '2+', 3 => '3+', 4 => '4+'] as $value => $label)
            <button type="button" class="tab" :class="{ 'is-on': beds === {{ $value }} }" @click="beds = {{ $value }}">{{ $label }}</button>
          @endforeach
        </div>
      </div>

      <div class="home-filter">
        <span class="home-filter-label"><i class="ki-filled ki-geolocation"></i> @lang('home.areas')</span>
        <div class="flex flex-col gap-2.5">
          @foreach ($areas as $area)
            <label class="check-line">
              <input type="checkbox" value="{{ $area }}" x-model="areas">
              <span>{{ $area }}</span>
              <span class="count">{{ $listings->where('location', $area)->count() }}</span>
            </label>
          @endforeach
        </div>
      </div>

      <label class="check-line border-t border-[var(--hairline)] pt-5">
        <input type="checkbox" x-model="followMap">
        <span>@lang('home.follow_map')</span>
      </label>
    </aside>

    {{-- 2. The island --}}
    <section class="home-map" aria-label="@lang('home.map')">
      <div x-ref="map" class="size-full"></div>
      @unless ($map['token'])
        <div class="absolute inset-0 flex items-center justify-center text-sm text-[var(--mist)]">@lang('home.no_map')</div>
      @endunless
      <button type="button" class="home-filters-toggle" @click="filtersOpen = !filtersOpen" :aria-expanded="filtersOpen.toString()">
        @lang('home.filters') <span x-show="filtered" x-text="'(' + filtered + ')'"></span>
      </button>
    </section>

    {{-- 3. Listings in view --}}
    <section class="home-list kt-scrollable-y-auto">
      <div class="flex flex-wrap items-baseline justify-between gap-x-6 gap-y-3 pb-6">
        <h1 class="serif text-[1.75rem] leading-tight">
          <span x-text="visible.length"></span> @lang('home.properties')
          <span class="sans block text-sm text-[var(--mist)]" x-text="followMap ? @js(__('home.in_view')) : @js(__('home.everywhere'))"></span>
        </h1>
        <div class="tabs">
          @foreach (['newest' => 'home.newest', 'low' => 'home.price_low', 'high' => 'home.price_high'] as $value => $label)
            <button type="button" class="tab" :class="{ 'is-on': sort === '{{ $value }}' }" @click="sort = '{{ $value }}'">@lang($label)</button>
          @endforeach
        </div>
      </div>

      <div class="home-cards">
        <template x-for="item in visible" :key="item.id">
          <article :id="'card-' + item.id" class="home-card" :class="{ 'is-active': active === item.id }"
            @mouseenter="active = item.id" @mouseleave="active = null" @click="focus(item)">
            <div class="home-card-photo">
              <img :src="item.photo || '{{ asset('assets/img/placeholder.png') }}'" :alt="item.title" loading="lazy"
                x-on:error.once="$el.src = '{{ asset('assets/img/placeholder.png') }}'">
            </div>
            <div class="home-card-body">
              <h3 class="home-card-title" x-text="item.title"></h3>
              <p class="home-card-meta flex items-center gap-1.5">
                <i class="ki-filled ki-geolocation text-xs text-[var(--mist)]"></i>
                <span x-text="meta(item)"></span>
              </p>
              <div class="home-card-features">
                <template x-if="item.bedrooms">
                  <span class="home-card-feature">
                    <i class="ki-filled ki-home-2"></i>
                    <span x-text="beds(item)"></span>
                  </span>
                </template>
                <template x-if="item.bathrooms">
                  <span class="home-card-feature">
                    <i class="ki-filled ki-drop"></i>
                    <span x-text="baths(item)"></span>
                  </span>
                </template>
                <template x-if="item.area || item.land">
                  <span class="home-card-feature">
                    <i class="ki-filled ki-size"></i>
                    <span x-text="area(item)"></span>
                  </span>
                </template>
              </div>
              <div class="home-card-footer">
                <p class="home-card-price">
                  <span x-text="'฿' + compact(item.sale ?? item.rent)"></span><small x-show="!item.sale" x-text="labels.perMonth"></small>
                  <small x-show="item.sale && item.rent" x-text="' ' + labels.orRent.replace(':price', '฿' + compact(item.rent))"></small>
                  <small x-show="item.reserved" x-text="' ' + labels.reserved"></small>
                </p>
                <a :href="item.url" class="home-card-link" @click.stop title="@lang('home.view')" aria-label="@lang('home.view')"><i class="ki-filled ki-eye"></i></a>
              </div>
            </div>
          </article>
        </template>

        <p x-show="!visible.length" x-cloak class="home-cards-empty py-10 text-center text-sm text-[var(--mist)]">@lang('home.empty')</p>
      </div>
    </section>
  </div>
@endsection
