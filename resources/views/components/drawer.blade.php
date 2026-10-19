{{--
  Side drawer shell (the notification-drawer look) for a Livewire drawer component: backdrop, panel, header with icon
  and title, close button and Esc all call the component's $close method. Livewire shows/hides it (render it inside
  an @if); no KTUI drawer JS. Content scrolls; the optional footer slot stays at the bottom.
  <x-drawer icon="ki-parcel" :title="$product->name">…<x-slot:footer>…</x-slot:footer></x-drawer>
--}}
@props(['icon', 'title', 'close' => 'close'])
<div x-data @keydown.escape.window="$wire.{{ $close }}()">
  <div class="kt-modal-backdrop z-50" wire:click="{{ $close }}"></div>

  <div {{ $attributes->class('fixed bottom-5 end-5 top-5 z-50 flex w-[450px] max-w-[90%] flex-col rounded-xl border border-border bg-background shadow-2xl') }}
    role="dialog" aria-modal="true" aria-label="{{ $title }}">
    <div class="flex items-center justify-between gap-2.5 border-b border-border px-5 py-3 text-sm font-semibold text-mono">
      <span class="flex min-w-0 items-center gap-2">
        <i class="ki-filled {{ $icon }} text-base text-primary"></i>
        <span class="truncate">{{ $title }}</span>
      </span>
      <button type="button" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" wire:click="{{ $close }}" aria-label="@lang('common.close')">
        <i class="ki-filled ki-cross"></i>
      </button>
    </div>

    <div class="grow kt-scrollable-y-auto">
      {{ $slot }}
    </div>

    @isset($footer)
      <div class="border-t border-border px-5 py-3">
        {{ $footer }}
      </div>
    @endisset
  </div>
</div>
