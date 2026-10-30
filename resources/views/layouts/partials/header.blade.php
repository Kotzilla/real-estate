@php
  $user = auth()->user();
  $avatar = 'flex size-9 shrink-0 items-center justify-center rounded-full border-2 border-green-500 bg-primary text-sm font-semibold text-white';
  $locales = ['en' => ['English', 'united-states.svg'], 'th' => ['ไทย', 'thailand.svg']];
  [$localeName, $localeFlag] = $locales[app()->getLocale()] ?? $locales['en'];
@endphp

<header class="kt-header fixed end-0 start-0 top-0 z-10 flex shrink-0 items-stretch bg-background shadow-sm"
  data-kt-sticky="true" data-kt-sticky-class="border-b border-border" data-kt-sticky-name="header" id="header">
  <div class="kt-container-fluid flex items-stretch justify-between lg:gap-4" id="headerContainer">
    <div class="-ms-1 flex items-center gap-2.5 lg:hidden">
      <button class="kt-btn kt-btn-icon kt-btn-ghost" data-kt-drawer-toggle="#sidebar">
        <i class="ki-filled ki-menu"></i>
      </button>
      <a href="{{ route('backend.dashboard') }}" class="flex items-center gap-2 font-semibold text-mono">
        <span class="brand-lime flex size-7 shrink-0 items-center justify-center rounded-full"><img src="{{ asset('assets/img/logo.png') }}" alt="{{ config('app.name') }}" class="size-4 object-contain"></span>
        <span>{{ config('app.name') }}</span>
      </a>
    </div>

    {{-- Breadcrumb: pages pass [[label, url?], ...] via @extends('layouts.app', ['breadcrumbs' => ...]); the last item is the current page. --}}
    <nav class="hidden items-center gap-2 text-sm lg:flex" aria-label="@lang('Breadcrumb')">
      <a class="text-muted-foreground hover:text-primary" href="{{ route('backend.dashboard') }}" aria-label="@lang('Dashboard')">
        <i class="ki-filled ki-home-2 text-base"></i>
      </a>
      @foreach ($breadcrumbs ?? [] as $crumb)
        <i class="ki-filled ki-right text-xs text-muted-foreground"></i>
        @if (! $loop->last && isset($crumb[1]))
          <a class="text-secondary-foreground hover:text-primary" href="{{ $crumb[1] }}">{{ $crumb[0] }}</a>
        @else
          <span class="font-medium text-mono">{{ $crumb[0] }}</span>
        @endif
      @endforeach
    </nav>

    <div class="flex items-center gap-2.5">
      <button type="button" aria-label="@lang('search.title')" data-kt-modal-toggle="#search_modal"
        class="kt-btn kt-btn-ghost kt-btn-icon hover:bg-primary/10 hover:[&_i]:text-primary group size-9 rounded-full">
        <i class="ki-filled ki-magnifier group-hover:text-primary text-lg"></i>
      </button>

      @livewire('layout.notification-bell')

      <div class="shrink-0" data-kt-dropdown="true" data-kt-dropdown-offset="10px, 10px"
        data-kt-dropdown-placement="bottom-end" data-kt-dropdown-trigger="click">
        <button type="button" class="shrink-0 cursor-pointer rounded-full"
          data-kt-dropdown-toggle="true" aria-label="{{ $user?->name ?? __('Menu') }}">
          <span class="{{ $avatar }}">{{ $user ? mb_strtoupper(mb_substr($user->name, 0, 1)) : '?' }}</span>
        </button>
        <div class="kt-dropdown-menu w-[250px]" data-kt-dropdown-menu="true">
          @if ($user)
            <div class="flex items-center gap-2 px-2.5 py-1.5">
              <span class="{{ $avatar }}">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
              <div class="flex flex-col gap-1.5">
                <span class="text-sm font-semibold leading-none text-foreground">{{ $user->name }}</span>
                <span class="text-xs font-medium leading-none text-secondary-foreground">{{ $user->email }}</span>
              </div>
            </div>
          @endif
          <ul class="kt-dropdown-menu-sub">
            <li><div class="kt-dropdown-menu-separator"></div></li>
            <li data-kt-dropdown="true" data-kt-dropdown-placement="left-start" data-kt-dropdown-trigger="hover">
              <button type="button" class="kt-dropdown-menu-toggle py-1" data-kt-dropdown-toggle="true">
                <span class="flex items-center gap-2">
                  <i class="ki-filled ki-icon text-lg"></i>
                  <span class="font-medium">@lang('Language')</span>
                </span>
                <span class="kt-badge kt-badge-stroke ms-auto shrink-0 gap-1.5">
                  {{ $localeName }}
                  <img alt="" class="inline-block size-3.5 rounded-full" src="{{ asset('assets/media/flags/'.$localeFlag) }}">
                </span>
              </button>
              <div class="kt-dropdown-menu w-[180px]" data-kt-dropdown-menu="true">
                <ul class="kt-dropdown-menu-sub">
                  @foreach ($locales as $code => [$name, $flag])
                    <li class="{{ app()->getLocale() === $code ? 'active' : '' }}">
                      <a class="kt-dropdown-menu-link" href="{{ route('lang.switch', ['locale' => $code]) }}">
                        <span class="flex items-center gap-2">
                          <img alt="" class="inline-block size-4 rounded-full" src="{{ asset('assets/media/flags/'.$flag) }}">
                          <span class="kt-menu-title font-medium">{{ $name }}</span>
                        </span>
                        @if (app()->getLocale() === $code)
                          <i class="ki-solid ki-check-circle ms-auto text-base text-green-500"></i>
                        @endif
                      </a>
                    </li>
                  @endforeach
                </ul>
              </div>
            </li>
            <li><div class="kt-dropdown-menu-separator"></div></li>
          </ul>
          <div class="mb-2.5 flex flex-col gap-3.5 px-2.5 pt-1.5">
            <div class="flex items-center justify-between gap-2">
              <span class="flex items-center gap-2">
                <i class="ki-filled ki-moon text-base text-muted-foreground"></i>
                <span class="text-2sm font-medium">@lang('Dark mode')</span>
              </span>
              <input class="kt-switch" data-kt-theme-switch-state="dark" data-kt-theme-switch-toggle="true" name="check" type="checkbox" value="1">
            </div>
            @if ($user && Route::has('backend.logout'))
              <form method="POST" action="{{ route('backend.logout') }}" class="w-full"
                onsubmit="return confirmSubmit(event, { title: @js(__('Log out?')), text: @js(__('You will need to sign in again.')), confirm: @js(__('Log out')), cancel: @js(__('Cancel')) })">
                @csrf
                <button type="submit" class="kt-btn btn-logout w-full justify-center">@lang('Log out')</button>
              </form>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</header>
