{{-- Reset button for a list's card toolbar: clears the search, filters and sort (ResetsFilters trait). Always there, so
     it can be found; disabled while the list is already clean.
     <x-reset-filters :show="$this->isFiltered()" /> --}}
@props(['show' => false])
<button type="button" wire:click="resetFilters" @disabled(! $show) class="kt-btn kt-btn-sm kt-btn-outline gap-1.5 shrink-0 disabled:opacity-50 disabled:cursor-not-allowed" title="@lang('common.reset')">
  <i class="ki-filled ki-arrows-circle text-sm"></i>
  <span>@lang('common.reset')</span>
</button>