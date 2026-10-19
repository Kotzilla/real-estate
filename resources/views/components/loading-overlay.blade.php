{{--
  Covers the page while a slow Livewire request runs (reports over a year of orders), as office's analytics page:
  the modal backdrop with a "Loading…" box. Put it inside the component's root; `target` limits it to some actions /
  properties (e.g. not the search box, which keeps its own small spinner).
  <x-loading-overlay />  or  <x-loading-overlay target="tab,days,branch" />
  wire:loading.flex, not .delay.flex: Livewire only hides the plain variants until a request starts.
--}}
@props(['target' => null])
<div wire:loading.flex @if ($target) wire:target="{{ $target }}" @endif class="kt-modal-backdrop z-[100] items-center justify-center" style="margin: 0">
  <div class="flex items-center gap-3 rounded-lg border border-border bg-background px-5 py-3 text-sm font-medium shadow-2xl">
    <span class="size-5 animate-spin rounded-full border-2 border-primary border-t-transparent"></span>
    @lang('common.loading')
  </div>
</div>
