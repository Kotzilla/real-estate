{{-- Standalone page for signing in and setting a password: the brand panel (near-black, lime accent) on the left on
     desktop, the form card on the right. Metronic's theme only; no sidebar, header or Livewire. --}}
<!DOCTYPE html>
<html class="h-full" data-kt-theme="true" data-kt-theme-mode="light" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>@yield('title') · {{ config('app.name') }}</title>
  <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
  <link rel="stylesheet" href="{{ asset('assets/vendors/keenicons/styles.bundle.css') }}">
  <link rel="stylesheet" href="https://use.typekit.net/zhu8pmk.css">
  @vite('resources/css/app.css')
  <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
</head>
<body class="antialiased flex h-full text-base text-foreground bg-background">
  <div class="grid w-full grow lg:grid-cols-2">
    <aside class="auth-brand hidden flex-col justify-between p-12 lg:flex">
      <span class="flex items-center gap-3 text-lg font-semibold">
        <span class="brand-lime flex size-10 items-center justify-center rounded-full"><img src="{{ asset('assets/img/logo.png') }}" alt="{{ config('app.name') }}" class="size-6 object-contain"></span>
        {{ config('app.name') }}
      </span>
      <div>
        <p class="text-3xl font-semibold leading-tight">@lang('login.tagline')</p>
        <p class="mt-4 max-w-md text-base opacity-70">@lang('login.intro')</p>
      </div>
      <span class="text-sm opacity-50">{{ date('Y') }} © {{ config('app.name') }}</span>
    </aside>

    <main class="flex items-center justify-center p-5 lg:p-10">
      <div class="kt-card w-full max-w-[400px]">
        <div class="kt-card-content flex flex-col gap-5 p-10">
          @yield('content')
        </div>
      </div>
    </main>
  </div>

  <script>
    // Show / hide a password (the button names what it will do next)
    document.querySelectorAll('[data-toggle-password]').forEach((button) => {
      button.addEventListener('click', () => {
        const input = document.getElementById(button.getAttribute('aria-controls'));
        const showing = input.type === 'text';
        input.type = showing ? 'password' : 'text';
        button.setAttribute('aria-label', showing ? button.dataset.show : button.dataset.hide);
        button.querySelector('i').className = showing ? 'ki-filled ki-eye' : 'ki-filled ki-eye-slash';
      });
    });
  </script>
</body>
</html>
