@extends('layouts.site')

{{-- The landing page (HomeController): a hero (slideshow, Alpine `heroSlides` in app.js) with buy / rent, the newest listings as plain links (search engines follow
     them to every listing's page) and the areas, each opening the map search filtered to it. --}}
@php
  $site = config('site');
  $thai = app()->getLocale() === 'th';
  $placeholder = asset('assets/img/placeholder.png');
  $short = fn ($amount) => $amount >= 1e6 ? number_format($amount / 1e6, 1).'M' : number_format($amount);
@endphp

@section('title', __('home.title'))

@push('scripts')
  <script>
    // The hero's slideshow: the next slide every 6 s, paused under the pointer and for reduced motion
    document.addEventListener('alpine:init', () => {
      window.Alpine.data('heroSlides', (count) => ({
        current: 0,
        timer: null,
        init() {
          if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) this.play();
        },
        play() {
          if (count < 2 || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
          clearInterval(this.timer);
          this.timer = setInterval(() => { this.current = (this.current + 1) % count; }, 6000);
        },
        pause() {
          clearInterval(this.timer);
        },
      }));
    });
  </script>
@endpush

@section('content')
  <div class="kt-scrollable-y-auto grow">
    <section class="landing-hero" x-data="heroSlides({{ $slides->count() }})" @mouseenter="pause()" @mouseleave="play()">
      @foreach ($slides as $slide)
        <div @class(['landing-slide', 'is-on' => $loop->first]) :class="{ 'is-on': current === {{ $loop->index }} }"
          style="background-image: url('{{ $slide }}')"></div>
      @endforeach
      @if ($slides->count() > 1)
        <div class="landing-dots">
          @foreach ($slides as $slide)
            <button type="button" :class="{ 'is-on': current === {{ $loop->index }} }" @click="current = {{ $loop->index }}"
              aria-label="{{ $loop->iteration }} / {{ $loop->count }}"></button>
          @endforeach
        </div>
      @endif
      <div class="landing-hero-inner">
        <h1 class="landing-title">@lang('site.hero_title')</h1>
        <p class="landing-lead">@lang('site.hero_text')</p>
        <div class="landing-actions">
          <a href="{{ route('listings', ['offer' => 'sale']) }}" class="landing-button">
            <i class="ki-filled ki-home-2"></i> @lang('site.buy_cta') <small>@lang('site.for_sale_n', ['count' => $forSale])</small>
          </a>
          <a href="{{ route('listings', ['offer' => 'rent']) }}" class="landing-button">
            <i class="ki-filled ki-key"></i> @lang('site.rent_cta') <small>@lang('site.for_rent_n', ['count' => $forRent])</small>
          </a>
        </div>
        <a href="{{ route('listings') }}" class="landing-map-link"><i class="ki-filled ki-map"></i> @lang('site.map_cta')</a>
      </div>
    </section>

    @if ($newest->isNotEmpty())
      <section class="landing-section">
        <div class="landing-section-head">
          <h2 class="landing-h2"><i class="ki-filled ki-star"></i> @lang('site.newest')</h2>
          <a href="{{ route('listings') }}" class="landing-more">@lang('site.see_all') <i class="ki-filled ki-arrow-right"></i></a>
        </div>
        <div class="landing-cards">
          @foreach ($newest as $property)
            <a href="{{ $property->publicUrl() }}" class="home-card">
              <div class="home-card-photo">
                <img src="{{ $property->coverPhoto?->src('md') ?? $placeholder }}" alt="{{ $property->title() }}" loading="lazy"
                  onerror="this.onerror = null; this.src = '{{ $placeholder }}'">
              </div>
              <div class="home-card-body">
                <h3 class="home-card-title">{{ $property->title() }}</h3>
                <p class="home-card-meta flex items-center gap-1.5">
                  <i class="ki-filled ki-geolocation text-xs text-[var(--mist)]"></i>
                  @lang('home.type_in_area', ['type' => e($property->type->label()), 'area' => e($property->location ?: __('home.island'))])
                </p>
                <div class="home-card-footer">
                  <p class="home-card-price">
                    @if ($property->is_sale)
                      ฿{{ $short((float) $property->sale_price) }}
                    @else
                      ฿{{ $short((float) $property->rent_price) }}<small>@lang('home.per_month')</small>
                    @endif
                  </p>
                  <span class="home-card-link" aria-hidden="true"><i class="ki-filled ki-eye"></i></span>
                </div>
              </div>
            </a>
          @endforeach
        </div>
      </section>
    @endif

    @if ($areas->isNotEmpty())
      <section class="landing-section" id="areas">
        <h2 class="landing-h2"><i class="ki-filled ki-geolocation"></i> @lang('site.areas')</h2>
        <p class="landing-sub">@lang('site.areas_text')</p>
        <div class="landing-areas">
          @foreach ($areas as $area => $total)
            <a href="{{ route('listings', ['area' => $area]) }}" class="landing-area">
              <span>{{ $area }}</span>
              <small>@lang('site.listings_n', ['count' => $total])</small>
            </a>
          @endforeach
        </div>
      </section>
    @endif

    <section class="landing-section" id="about">
      <div class="landing-about">
        <div>
          <h2 class="landing-h2"><i class="ki-filled ki-people"></i> @lang('site.who_title')</h2>
          <p class="landing-about-lead">@lang('site.who_lead')</p>
          <p class="landing-about-text">@lang('site.who_text', ['since' => $site['since']])</p>
          <div class="landing-stats">
            <div><strong>{{ now()->year - $site['since'] }}+</strong> @lang('site.stat_years')</div>
            <div><strong>{{ $total }}</strong> @lang('site.stat_listings')</div>
            <div><strong>{{ $areas->count() }}</strong> @lang('site.stat_areas')</div>
            <div><strong>{{ $site['team'] }}</strong> @lang('site.stat_team')</div>
          </div>
        </div>
        @if ($slides->isNotEmpty())
          <img src="{{ $slides->last() }}" alt="" class="landing-about-photo" loading="lazy">
        @endif
      </div>
    </section>

    <section class="landing-section" id="contact">
      <h2 class="landing-h2"><i class="ki-filled ki-messages"></i> @lang('site.contact_title')</h2>
      <p class="landing-sub">@lang('site.contact_text')</p>
      <div class="landing-contact">
        <div class="landing-contact-list">
          <a href="tel:{{ preg_replace('/[^0-9+]/', '', $site['phone']) }}" class="landing-contact-item">
            <i class="ki-filled ki-phone"></i><span><small>@lang('site.call')</small>{{ $site['phone'] }}</span>
          </a>
          <a href="https://line.me/R/ti/p/{{ rawurlencode($site['line']) }}" target="_blank" rel="noopener" class="landing-contact-item">
            <i class="ki-filled ki-message-text"></i><span><small>@lang('site.line')</small>{{ $site['line'] }}</span>
          </a>
          <a href="https://wa.me/{{ $site['whatsapp'] }}" target="_blank" rel="noopener" class="landing-contact-item">
            <i class="ki-filled ki-whatsapp"></i><span><small>@lang('site.whatsapp')</small>{{ $site['phone'] }}</span>
          </a>
          <a href="mailto:{{ $site['email'] }}" class="landing-contact-item">
            <i class="ki-filled ki-sms"></i><span><small>@lang('site.email')</small>{{ $site['email'] }}</span>
          </a>
          <div class="landing-contact-item">
            <i class="ki-filled ki-geolocation"></i><span><small>@lang('site.office')</small>{{ $thai ? $site['address_th'] : $site['address'] }}</span>
          </div>
          <div class="landing-contact-item">
            <i class="ki-filled ki-time"></i><span><small>@lang('site.hours')</small>{{ $site['hours'][$thai ? 'th' : 'en'] }}</span>
          </div>
        </div>
        <a href="https://www.google.com/maps?q={{ $site['office']['lat'] }},{{ $site['office']['lng'] }}" target="_blank" rel="noopener"
          class="listing-map landing-contact-map" title="@lang('site.directions')">
          @if ($mapToken)
            <img src="https://api.mapbox.com/styles/v1/mapbox/light-v11/static/pin-l+2f6b62({{ $site['office']['lng'] }},{{ $site['office']['lat'] }})/{{ $site['office']['lng'] }},{{ $site['office']['lat'] }},14,0/800x500@2x?access_token={{ $mapToken }}"
              alt="@lang('site.office_map')" loading="lazy">
          @else
            <span>@lang('site.directions')</span>
          @endif
        </a>
      </div>
    </section>

    @include('layouts.partials.site-footer')
  </div>
@endsection
