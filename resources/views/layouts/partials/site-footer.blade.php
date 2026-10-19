{{-- The public site's footer, at the end of every page that scrolls (not the full-height map search). The agency's
     details come from config/site.php. --}}
@php
  $site = config('site');
  $thai = app()->getLocale() === 'th';
  $social = ['facebook' => 'Facebook', 'instagram' => 'Instagram', 'youtube' => 'YouTube'];
@endphp
<footer class="site-footer">
  <div class="site-footer-grid">
    <div class="site-footer-about">
      <a href="{{ route('home') }}" class="site-wordmark">
        <img src="{{ asset('assets/img/logo-white.png') }}" alt="" class="size-8 object-contain">
        <span class="site-wordmark-name">{{ config('app.name') }}</span>
      </a>
      <p>@lang('site.footer_about')</p>
      <div class="site-footer-social" aria-label="@lang('site.follow')">
        @foreach ($social as $network => $label)
          @if (! empty($site['social'][$network]))
            <a href="{{ $site['social'][$network] }}" target="_blank" rel="noopener" title="{{ $label }}" aria-label="{{ $label }}"><i class="ki-filled ki-{{ $network }}"></i></a>
          @endif
        @endforeach
      </div>
    </div>

    <nav aria-label="@lang('site.explore')">
      <h3 class="site-footer-h3">@lang('site.explore')</h3>
      <ul class="site-footer-links">
        <li><a href="{{ route('home') }}">@lang('site.home')</a></li>
        <li><a href="{{ route('listings', ['offer' => 'sale']) }}">@lang('site.buy_cta')</a></li>
        <li><a href="{{ route('listings', ['offer' => 'rent']) }}">@lang('site.rent_cta')</a></li>
        <li><a href="{{ route('listings') }}">@lang('site.map_cta')</a></li>
        <li><a href="{{ route('home') }}#about">@lang('site.who_title')</a></li>
        <li><a href="{{ route('privacy') }}">@lang('site.privacy')</a></li>
      </ul>
    </nav>

    <div>
      <h3 class="site-footer-h3">@lang('site.contact')</h3>
      <ul class="site-footer-links">
        <li><i class="ki-filled ki-phone"></i> <a href="tel:{{ preg_replace('/[^0-9+]/', '', $site['phone']) }}">{{ $site['phone'] }}</a></li>
        <li><i class="ki-filled ki-sms"></i> <a href="mailto:{{ $site['email'] }}">{{ $site['email'] }}</a></li>
        <li><i class="ki-filled ki-message-text"></i> <a href="https://line.me/R/ti/p/{{ rawurlencode($site['line']) }}" target="_blank" rel="noopener">LINE {{ $site['line'] }}</a></li>
        <li><i class="ki-filled ki-geolocation"></i> <span>{{ $thai ? $site['address_th'] : $site['address'] }}</span></li>
        <li><i class="ki-filled ki-time"></i> <span>{{ $site['hours'][$thai ? 'th' : 'en'] }}</span></li>
      </ul>
    </div>
  </div>

  <div class="site-footer-bottom">
    <p>@lang('site.rights', ['year' => now()->year, 'name' => e(config('app.name'))])</p>
    <a href="{{ route('privacy') }}">@lang('site.privacy')</a>
  </div>
</footer>
