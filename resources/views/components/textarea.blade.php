{{--
  A textarea that grows with its text, with a ring filling up to its maxlength (red, with the characters left, for the
  last 50): limitedTextarea in resources/js/limited-textarea.js. Give it a maxlength matching the request's max:.
  Every other attribute (name, id, wire:model, placeholder, aria-invalid…) goes on the textarea.
  <x-textarea name="remark" maxlength="2000">{{ old('remark') }}</x-textarea>
--}}
@php
  $max = (int) $attributes->get('maxlength', 2000);
@endphp
<div x-data="limitedTextarea({{ $max }})" class="relative">
  <textarea x-ref="ta" @input="sync" {{ $attributes->merge(['rows' => 3, 'maxlength' => $max])->class('kt-textarea auto-size w-full pe-9') }}>{{ $slot }}</textarea>
  <span class="pointer-events-none absolute end-2.5 bottom-2.5 flex items-center" title="@lang('common.chars_left')">
    <span class="me-1 text-2xs font-medium" x-show="near" x-text="remaining" :style="{ color }" x-cloak></span>
    <svg width="18" height="18" viewBox="0 0 20 20" class="-rotate-90" aria-hidden="true">
      <circle cx="10" cy="10" r="8" fill="none" stroke="var(--border)" stroke-width="2.5"></circle>
      <circle cx="10" cy="10" r="8" fill="none" stroke-width="2.5" stroke-linecap="round" :stroke="color" :stroke-dasharray="`${ratio * 50.27} 50.27`"></circle>
    </svg>
  </span>
</div>
