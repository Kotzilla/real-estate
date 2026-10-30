{{-- One page for every HTTP error (each errors/<code> view includes it). Standalone on purpose: no shared header,
     footer, menus, Vite or database calls, so it still renders when the app itself is what broke. Metronic's theme plus
     the brand colours inline (the same values as the brand block in resources/css/app.css, which needs Vite). --}}
@php
  $code = isset($exception) && method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500;
  $key = Lang::has("error.page.{$code}.title") ? $code : 'generic';
  $back = url()->previous() !== url()->current() ? url()->previous() : url('/');
@endphp
<!DOCTYPE html>
<html class="h-full" data-kt-theme="true" data-kt-theme-mode="light" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>{{ $code }} · @lang("error.page.{$key}.title") · {{ config('app.name') }}</title>
  <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
  <style>
    :root:root { --primary: #3e5083; --primary-foreground: #ffffff; --ring: #8a99b3; }
    body { background: #f7f9fc; }
    .error-logo { display: flex; align-items: center; justify-content: center; width: 3.5rem; height: 3.5rem; border-radius: 9999px; background: #c1df0f; }
    .error-logo img { width: 2rem; height: 2rem; object-fit: contain; }
    .error-code { margin-top: 1.5rem; font-size: clamp(6rem, 25vw, 10rem); font-weight: 700; line-height: 1; letter-spacing: -0.04em; color: var(--primary); }
    .error-main { max-width: 28rem; }
    .error-actions { margin-top: 2rem; }
    .error-bar { width: 4rem; height: .375rem; margin-top: 1.25rem; border-radius: 9999px; background: #c1df0f; }
  </style>
</head>
<body class="antialiased flex h-full items-center justify-center bg-background p-5 text-center text-foreground">
  <main class="error-main flex flex-col items-center">
    <a href="{{ url('/') }}" class="error-logo"><img src="{{ asset('assets/img/logo.png') }}" alt="{{ config('app.name') }}"></a>
    <p class="error-code" aria-hidden="true">{{ $code }}</p>
    <span class="error-bar" aria-hidden="true"></span>
    <h1 class="mt-6 text-2xl font-semibold text-mono">@lang("error.page.{$key}.title")</h1>
    <p class="mt-3 text-secondary-foreground">@lang("error.page.{$key}.desc")</p>
    <div class="error-actions flex flex-wrap justify-center gap-3">
      <a href="{{ $back }}" class="kt-btn kt-btn-outline">@lang('error.back')</a>
      <a href="{{ url('/') }}" class="kt-btn kt-btn-primary">@lang('error.home')</a>
    </div>
  </main>
</body>
</html>
