{{-- One page for every HTTP error (each errors/<code> view includes it). Standalone on purpose: no shared header,
     footer, menus, Vite or database calls, so it still renders when the app itself is what broke. Metronic's theme only. --}}
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
</head>
<body class="antialiased flex h-full items-center justify-center bg-background p-5 text-center text-foreground">
  <main class="flex max-w-md flex-col items-center">
    <p class="text-[clamp(6rem,25vw,10rem)] font-semibold leading-none text-primary" aria-hidden="true">{{ $code }}</p>
    <h1 class="mt-4 text-2xl font-semibold text-mono">@lang("error.page.{$key}.title")</h1>
    <p class="mt-3 text-secondary-foreground">@lang("error.page.{$key}.desc")</p>
    <div class="mt-8 flex flex-wrap justify-center gap-3">
      <a href="{{ $back }}" class="kt-btn kt-btn-outline">@lang('error.back')</a>
      <a href="{{ url('/') }}" class="kt-btn kt-btn-primary">@lang('error.home')</a>
    </div>
  </main>
</body>
</html>
