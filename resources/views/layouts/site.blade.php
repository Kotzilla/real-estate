{{-- The public site's page: the dark header (wordmark, home / buy / rent, language, staff sign-in) and the page below it at
     full height. Metronic's theme underneath, the site's own palette on body.site (app.css); Livewire's script for Alpine. --}}
@php
  $locales = ['en' => ['EN', 'united-states.svg'], 'th' => ['TH', 'thailand.svg']];
  $offer = request()->routeIs('listings') ? request('offer') : null;
  $menu = [
      [route('home'), 'ki-element-11', 'site.home', request()->routeIs('home')],
      [route('listings', ['offer' => 'sale']), 'ki-home-2', 'home.buy', $offer === 'sale'],
      [route('listings', ['offer' => 'rent']), 'ki-key', 'home.rent', $offer === 'rent'],
      [route('home').'#contact', 'ki-messages', 'site.contact', false],
  ];
  $user = auth()->user();
  $role = $user ? \App\Enums\UserRole::tryFrom((string) $user->getRoleNames()->first()) : null;
@endphp
<!DOCTYPE html>
<html class="h-full" data-kt-theme="true" data-kt-theme-mode="light" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @php
    // Search engines and social cards: a page may override any of these with @section('og_title', ...).
    // Section content is stored escaped by Blade, so decode it and let {{ }} escape it once in the attribute.
    $meta = fn (string $section, string $default) => trim(html_entity_decode(strip_tags($__env->yieldContent($section, $default)), ENT_QUOTES)) ?: $default;
    $metaTitle = $meta('title', config('app.name'));
    $metaDescription = $meta('description', __('home.meta'));
    $metaCanonical = $meta('canonical', url()->current());
    $ogTitle = $meta('og_title', $metaTitle);
    $ogDescription = $meta('og_description', $metaDescription);
    $ogImage = $meta('og_image', asset('assets/img/web-share.jpg'));
  @endphp
  <title>{{ $metaTitle }}</title>
  <meta name="description" content="{{ $metaDescription }}">
  <link rel="canonical" href="{{ $metaCanonical }}">
  <meta property="og:type" content="{{ $meta('og_type', 'website') }}">
  <meta property="og:site_name" content="{{ config('app.name') }}">
  <meta property="og:locale" content="{{ app()->getLocale() === 'th' ? 'th_TH' : 'en_US' }}">
  <meta property="og:locale:alternate" content="{{ app()->getLocale() === 'th' ? 'en_US' : 'th_TH' }}">
  <meta property="og:title" content="{{ $ogTitle }}">
  <meta property="og:description" content="{{ $ogDescription }}">
  <meta property="og:url" content="{{ $meta('og_url', $metaCanonical) }}">
  <meta property="og:image" content="{{ $ogImage }}">
  <meta property="og:image:alt" content="{{ $ogTitle }}">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="{{ $ogTitle }}">
  <meta name="twitter:description" content="{{ $ogDescription }}">
  <meta name="twitter:image" content="{{ $ogImage }}">
  <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Noto+Serif+Thai:wght@400;500&display=swap">
  <link rel="stylesheet" href="https://use.typekit.net/zhu8pmk.css">
  <link rel="stylesheet" href="{{ asset('assets/vendors/keenicons/styles.bundle.css') }}">
  @stack('head')
  @stack('styles')
  @vite('resources/css/app.css')
  <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
  @livewireStyles
</head>
<body class="site antialiased flex h-full flex-col text-base">
  <header class="site-header" x-data="{ menuOpen: false }" :class="{ 'is-open': menuOpen }" @keydown.escape="menuOpen = false" @click.outside="menuOpen = false">
    <a href="{{ route('home') }}" class="site-wordmark">
      <img src="{{ asset('assets/img/logo.png') }}" alt="{{ config('app.name') }}" class="size-8 object-contain">
      <span class="flex flex-col gap-1">
        <span class="site-wordmark-name">{{ config('app.name') }}</span>
        <span class="site-wordmark-place">@lang('home.island')</span>
      </span>
    </a>

    <button type="button" class="site-burger" @click="menuOpen = !menuOpen" :aria-expanded="menuOpen.toString()" aria-expanded="false"
      aria-controls="site-nav" aria-label="@lang('home.menu')">
      <svg x-show="!menuOpen" viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
        <path d="M4 7h16M4 12h16M4 17h16"/>
      </svg>
      <i class="ki-filled ki-cross" x-show="menuOpen" x-cloak></i>
    </button>

    <nav class="site-nav" id="site-nav" aria-label="@lang('home.menu')" @click="menuOpen = false">
      @foreach ($menu as [$url, $icon, $label, $current])
        <a href="{{ $url }}" @class(['site-nav-item', 'is-current' => $current]) @if ($current) aria-current="page" @endif><i class="ki-filled {{ $icon }}"></i> @lang($label)</a>
      @endforeach
      <span class="site-nav-sep"></span>
      @foreach ($locales as $code => [$label, $flag])
        <a href="{{ route('lang.switch', $code) }}" class="{{ app()->getLocale() === $code ? 'is-current' : '' }}" lang="{{ $code }}"><img alt="" class="site-flag" src="{{ asset('assets/media/flags/'.$flag) }}">{{ $label }}</a>
      @endforeach
      @if ($user)
        {{-- Signed-in staff: an avatar with their menu (a dropdown on desktop, laid out in the burger panel below 1024px) --}}
        <div class="site-user" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.stop="open = false" @click.stop>
          <button type="button" class="site-user-toggle" @click="open = !open" :aria-expanded="open.toString()" aria-expanded="false" aria-label="{{ $user->name }}">
            <span class="site-avatar">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
          </button>
          <div class="site-user-menu" :class="{ 'is-open': open }">
            <div class="site-user-head">
              <span class="site-avatar">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
              <span class="flex min-w-0 grow flex-col gap-1">
                <span class="truncate text-sm font-semibold leading-none">{{ $user->name }}</span>
                <span class="truncate text-xs leading-none text-muted-foreground">{{ $user->email }}</span>
              </span>
              @if ($role)
                <span class="kt-badge kt-badge-sm {{ $role->badgeClass() }}">{{ $role->label() }}</span>
              @endif
            </div>
            <a href="{{ route('backend.dashboard') }}"><i class="ki-filled ki-element-11"></i> @lang('Dashboard')</a>
            @can('viewAny', \App\Models\Property::class)
              <a href="{{ route('backend.properties.index') }}"><i class="ki-filled ki-home-2"></i> @lang('Properties')</a>
            @endcan
            @can('viewAny', \App\Models\Enquiry::class)
              <a href="{{ route('backend.enquiries.index') }}"><i class="ki-filled ki-calendar-tick"></i> @lang('Enquiries')</a>
            @endcan
            <form method="POST" action="{{ route('backend.logout') }}">
              @csrf
              <button type="submit"><i class="ki-filled ki-exit-right"></i> @lang('Log out')</button>
            </form>
          </div>
        </div>
      @else
        <a href="{{ route('login') }}" class="site-nav-icon" title="@lang('home.staff')" aria-label="@lang('home.staff')"><i class="ki-filled ki-entrance-left text-lg"></i></a>
      @endif
    </nav>
  </header>

  <main class="flex min-h-0 grow flex-col">
    @yield('content')
  </main>

  @stack('scripts')
  @livewireScripts
</body>
</html>
