{{-- The public site's page: the dark header (wordmark, buy / rent, language, staff sign-in) and the page below it at
     full height. Metronic's theme underneath, the site's own palette on body.site (app.css); Livewire's script for Alpine. --}}
@php
  $locales = ['en' => ['EN', 'united-states.svg'], 'th' => ['TH', 'thailand.svg']];
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
  <header class="site-header">
    <a href="{{ route('home') }}" class="site-wordmark">
      <img src="{{ asset('assets/img/logo-white.png') }}" alt="{{ config('app.name') }}" class="size-8 object-contain">
      <span class="flex flex-col gap-1">
        <span class="site-wordmark-name">{{ config('app.name') }}</span>
        <span class="site-wordmark-place">@lang('home.island')</span>
      </span>
    </a>

    <nav class="site-nav" aria-label="@lang('home.menu')">
      @yield('nav')
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
