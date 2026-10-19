@extends('layouts.auth')

@section('title', __('login.title'))

@section('content')
  <div class="text-center">
    <h1 class="text-lg font-medium text-mono">@lang('login.title')</h1>
    <p class="mt-1.5 text-sm text-secondary-foreground lg:hidden">@lang('login.intro')</p>
  </div>

  @if (session('status'))
    <div class="rounded-lg border border-border bg-muted px-4 py-3 text-sm">{{ session('status') }}</div>
  @endif

  <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-5" novalidate>
    @csrf

    {{-- Honeypot: hidden from people and password managers; bots fill it in --}}
    <div class="absolute -left-[9999px]" aria-hidden="true">
      <input type="text" name="website" value="" tabindex="-1" autocomplete="off">
    </div>

    <div class="flex flex-col gap-1">
      <label for="login" class="kt-form-label text-mono required">@lang('login.login')</label>
      <input id="login" type="text" name="login" value="{{ old('login') }}" class="kt-input" autofocus autocomplete="username"
        autocapitalize="none" spellcheck="false" @error('login') aria-invalid="true" aria-describedby="login-error" @enderror>
      @error('login') <span id="login-error" class="text-sm text-destructive">{{ $message }}</span> @enderror
    </div>

    <div class="flex flex-col gap-1">
      <label for="password" class="kt-form-label text-mono required">@lang('login.password')</label>
      <div class="kt-input" @error('password') aria-invalid="true" @enderror>
        <input id="password" type="password" name="password" autocomplete="current-password" @error('password') aria-describedby="password-error" @enderror>
        <button type="button" class="kt-btn kt-btn-sm kt-btn-ghost kt-btn-icon -me-1.5" data-toggle-password aria-controls="password"
          data-show="@lang('login.show')" data-hide="@lang('login.hide')" aria-label="@lang('login.show')">
          <i class="ki-filled ki-eye"></i>
        </button>
      </div>
      @error('password') <span id="password-error" class="text-sm text-destructive">{{ $message }}</span> @enderror
    </div>

    <label class="kt-label">
      <input type="checkbox" name="remember" value="1" class="kt-checkbox kt-checkbox-sm" @checked(old('_token') ? old('remember') : true)>
      <span class="kt-checkbox-label">@lang('login.remember')</span>
    </label>

    <button type="submit" class="kt-btn kt-btn-primary flex grow justify-center">@lang('login.submit')</button>
  </form>
@endsection
