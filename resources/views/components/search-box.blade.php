{{--
  Search box for list pages: searches as you type (use with wire:model.live.debounce.250ms) and shows an ✕ button
  whenever there is text, which clears the box and re-runs the search at once.
  Usage: <x-search-box wire:model.live.debounce.250ms="q" :value="$q" placeholder="…" class="w-64" />  (class sets the width; default grow)
--}}
@props(['value' => '', 'placeholder' => null])
@php
  $model = $attributes->whereStartsWith('wire:model')->first();
  if (is_string($placeholder) && preg_match("/^@lang\(['\"](.+)['\"]\)$/", $placeholder, $m)) {
    $placeholder = __($m[1]);
  }
  $resolvedPlaceholder = $placeholder ?? __('common.search_placeholder');
@endphp

{{-- x-init: the ✕ also follows the Livewire property, so a server-side reset (the filter card's Reset) hides it. --}}
<div class="search-box relative {{ $attributes->get('class', 'grow') }}" x-data="{ has: @js($value !== '') }"
  @if ($model) x-init="$watch('$wire.{{ $model }}', (v) => has = (v ?? '') !== '')" @endif>
  {{-- type="text", not "search": browsers add their own ✕ to type=search, which would double up. --}}
  <input {{ $attributes->whereStartsWith('wire:') }} x-ref="box" x-on:input="has = $event.target.value !== ''"
    class="kt-input w-full ps-9 pe-9" type="text" inputmode="search" autocomplete="off" placeholder="{{ $resolvedPlaceholder }}">

  {{-- After the input so it paints on top of it. The span is what is positioned: Keenicons sets position:relative on .ki-*, which would override absolute on the icon itself. --}}
  <span class="pointer-events-none absolute start-3 top-1/2 flex -translate-y-1/2 text-muted-foreground">
    <i class="ki-filled ki-magnifier"></i>
  </span>

  <button type="button" x-show="has" x-cloak aria-label="@lang('common.clear')"
    class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost absolute end-1.5 top-1/2 -translate-y-1/2"
    x-on:click="$refs.box.value = ''; has = false; $refs.box.dispatchEvent(new Event('input', { bubbles: true })); $refs.box.focus()">
    <i class="ki-filled ki-cross"></i>
  </button>
</div>