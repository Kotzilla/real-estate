<!DOCTYPE html>
<html class="h-full" data-kt-theme="true" data-kt-theme-mode="light" dir="ltr" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', config('app.name'))</title>

    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('assets/vendors/keenicons/styles.bundle.css') }}">
    <link rel="stylesheet" href="https://use.typekit.net/zhu8pmk.css">
    {{-- Order matters: our gap-filler utilities load FIRST so the prebuilt Metronic theme (which has the
         responsive lg:/dark: variants) always wins where both define a class. --}}
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
    @livewireStyles


    <script>
      // Apply the saved light/dark mode before first paint (KTUI stores it in localStorage 'kt-theme').
      (function () {
        var mode = localStorage.getItem('kt-theme') || document.documentElement.getAttribute('data-kt-theme-mode') || 'light';
        if (mode === 'system') {
          mode = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        document.documentElement.classList.add(mode);
      })();
    </script>
  </head>
  <body class="antialiased flex h-full text-base text-foreground bg-background demo1 kt-sidebar-fixed kt-header-fixed">
    <div class="flex min-w-0 grow">
      @include('layouts.partials.sidebar')

      <div class="kt-wrapper flex min-w-0 grow flex-col">
        @include('layouts.partials.header')

        <main class="grow pt-5 pb-10" id="content" role="content">
          <div class="kt-container-fluid">
            @hasSection('heading')
              <div class="flex flex-wrap items-center justify-between gap-5 pb-7.5">
                <h1 class="text-xl font-medium leading-none text-mono">@yield('heading')</h1>
                @yield('actions')
              </div>
            @endif

            @yield('content')
          </div>
        </main>

        @include('layouts.partials.footer')
      </div>
    </div>

    @include('layouts.partials.lightbox')

    <script src="{{ asset('assets/js/core.bundle.js') }}"></script>
    <script src="{{ asset('assets/vendors/ktui/ktui.min.js') }}"></script>
    <script src="{{ asset('assets/js/layouts/base.js') }}"></script>
    <script>
      window.__ktSelectI18n = {
        placeholder: @js(__('Select an option')),
        searchPlaceholder: @js(__('Search...')),
        searchEmpty: @js(__('No results')),
      };
      window.__pageExpired = {
        title: @js(__('Page Expired')),
        text: @js(__('Your session has expired. Reload the page to continue.')),
        reload: @js(__('Reload')),
        cancel: @js(__('Cancel')),
      };
    </script>
    @vite('resources/js/app.js')
    @livewireScripts
    @if (session('success') || session('error'))
      <script>
        document.addEventListener('DOMContentLoaded', () => {
          @if (session('success'))
            window.toast('success', @json(session('success')));
          @else
            window.toast('error', @json(session('error')));
          @endif
        });
      </script>
    @endif
  </body>
</html>
