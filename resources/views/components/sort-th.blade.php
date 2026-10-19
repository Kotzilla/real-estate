{{--
  Sortable table header in the theme's datatable style (stacked up/down chevrons; aria-sort darkens the active one),
  for a Livewire list with sortBy($field) and $sort / $dir properties.
  <x-sort-th field="date" :sort="$sort" :dir="$dir">@lang('contracts.table.period')</x-sort-th>
--}}
@props(['field', 'sort', 'dir'])
<th {{ $attributes }} @if ($sort === $field) aria-sort="{{ $dir }}" @endif>
  <span class="kt-table-col" wire:click="sortBy('{{ $field }}')">
    <span class="kt-table-col-label">{{ $slot }}</span>
    <span class="kt-table-col-sort"></span>
  </span>
</th>
