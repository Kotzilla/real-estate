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
@endphp
<!DOCTYPE html>
<html class="h-full" data-kt-theme="true" data-kt-theme-mode="light" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', config('app.name'))</title>
  <meta name="description" content="@yield('description', __('home.meta'))">
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
      <img src="{{ asset('assets/img/logo-white.png') }}" alt="{{ config('app.name') }}" class="size-8 object-contain">
      <span class="flex flex-col gap-1">
        <span class="site-wordmark-name">{{ config('app.name') }}</span>
        <span class="site-wordmark-place">@lang('home.island')</span>
      </span>
    </a>

    <button type="button" class="site-burger" @click="menuOpen = !menuOpen" :aria-expanded="menuOpen.toString()" aria-expanded="false"
      aria-controls="site-nav" aria-label="@lang('home.menu')">
      <i class="ki-filled ki-burger-menu-2" x-show="!menuOpen"></i>
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
      <a href="{{ route('login') }}" class="site-nav-icon" title="@lang('home.staff')" aria-label="@lang('home.staff')"><i class="ki-filled ki-entrance-left text-lg"></i></a>
    </nav>
  </header>

  <main class="flex min-h-0 grow flex-col">
    @yield('content')
  </main>

  @stack('scripts')
  @livewireScripts
</body>
</html>
