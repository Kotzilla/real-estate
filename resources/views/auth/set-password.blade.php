@extends('layouts.auth')

@section('title', __('set_password.title'))

@section('content')
  <div class="text-center">
    <h1 class="text-lg font-medium text-mono">@lang('set_password.title')</h1>
    <p class="mt-1.5 text-sm text-secondary-foreground">@lang('set_password.intro')</p>
  </div>

  <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-5" novalidate>
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <input type="hidden" name="email" value="{{ old('email', $email) }}">

    @foreach (['password' => 'set_password.password', 'password_confirmation' => 'set_password.confirm'] as $field => $label)
      <div class="flex flex-col gap-1">
        <label for="{{ $field }}" class="kt-form-label text-mono required">@lang($label)</label>
        <div class="kt-input" @error($field) aria-invalid="true" @enderror>
          <input id="{{ $field }}" type="password" name="{{ $field }}" autocomplete="new-password">
          <button type="button" class="kt-btn kt-btn-sm kt-btn-ghost kt-btn-icon -me-1.5" data-toggle-password aria-controls="{{ $field }}"
            data-show="@lang('set_password.show')" data-hide="@lang('set_password.hide')" aria-label="@lang('set_password.show')">
            <i class="ki-filled ki-eye"></i>
          </button>
        </div>
        @if ($field === 'password')
          @error('password')
            <span class="text-sm text-destructive">{{ $message }}</span>
          @else
            <span class="text-sm text-secondary-foreground">@lang('set_password.hint')</span>
          @enderror
        @endif
      </div>
    @endforeach

    <button type="submit" class="kt-btn kt-btn-primary flex grow justify-center">@lang('set_password.submit')</button>
  </form>
@endsection
