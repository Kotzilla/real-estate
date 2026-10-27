{{-- Shown instead of the public site while site.coming_soon is on (App\Http\Middleware\ComingSoon): a full-screen beach photo,
     the wordmark, a line about the launch and how to reach us meanwhile. Staff sign in from the corner and see the real site. --}}
@php
  $site = config('site');
  $locales = ['en' => ['EN', 'united-states.svg'], 'th' => ['TH', 'thailand.svg']];
@endphp
<!DOCTYPE html>
<html class="h-full" data-kt-theme="true" data-kt-theme-mode="light" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ config('app.name') }} · @lang('site.soon_title')</title>
  <meta name="description" content="@lang('home.meta')">
  <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Noto+Serif+Thai:wght@400;500&display=swap">
  <link rel="stylesheet" href="https://use.typekit.net/zhu8pmk.css">
  <link rel="stylesheet" href="{{ asset('assets/vendors/keenicons/styles.bundle.css') }}">
  @vite('resources/css/app.css')
  <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
</head>
<body class="site antialiased h-full text-base">
  <main class="landing-hero soon">
    <div class="landing-slide is-on" style="background-image: url('{{ asset('assets/img/hero/slide-01.jpg') }}')"></div>

    <div class="soon-top">
      <span class="site-wordmark">
        <img src="{{ asset('assets/img/logo-white.png') }}" alt="{{ config('app.name') }}" class="size-8 object-contain">
        <span class="flex flex-col gap-1">
          <span class="site-wordmark-name">{{ config('app.name') }}</span>
          <span class="site-wordmark-place">@lang('home.island')</span>
        </span>
      </span>
      <nav class="site-nav">
        @foreach ($locales as $code => [$label, $flag])
          <a href="{{ route('lang.switch', $code) }}" class="{{ app()->getLocale() === $code ? 'is-current' : '' }}" lang="{{ $code }}"><img alt="" class="site-flag" src="{{ asset('assets/media/flags/'.$flag) }}">{{ $label }}</a>
        @endforeach
        <a href="{{ route('login') }}" class="site-nav-icon" title="@lang('home.staff')" aria-label="@lang('home.staff')"><i class="ki-filled ki-entrance-left text-lg"></i></a>
      </nav>
    </div>

    <div class="landing-hero-inner">
      <span class="soon-badge">@lang('site.soon_badge')</span>
      <h1 class="landing-title">@lang('site.soon_title')</h1>
      <p class="landing-lead">@lang('site.soon_text')</p>
      <div class="landing-actions">
        <a href="tel:{{ preg_replace('/[^+\d]/', '', $site['phone']) }}" class="landing-button"><i class="ki-filled ki-phone"></i> {{ $site['phone'] }}</a>
        <a href="mailto:{{ $site['email'] }}" class="landing-button"><i class="ki-filled ki-sms"></i> {{ $site['email'] }}</a>
        <a href="https://wa.me/{{ $site['whatsapp'] }}" class="landing-button" target="_blank" rel="noopener"><i class="ki-filled ki-whatsapp"></i> @lang('site.whatsapp')</a>
      </div>
    </div>
  </main>
</body>
</html>
