{{-- wire:ignore.self: keep KTUI's own attributes/classes on the modal across Livewire re-renders. --}}
<div class="kt-modal" data-kt-modal="true" id="search_modal" wire:ignore.self>
  <div class="kt-modal-content top-[15%] max-w-[600px]">
    <div class="kt-modal-header flex items-center gap-2 px-5 py-4">
      <i class="ki-filled ki-magnifier shrink-0 text-xl text-muted-foreground"></i>
      <input class="kt-input kt-input-ghost grow" type="text" id="search_keyword" autocomplete="off"
        wire:model.live.debounce.300ms="keyword" placeholder="@lang('search.placeholder')">

      <div wire:loading class="shrink-0">
        <span class="inline-block size-4 animate-spin rounded-full border-2 border-primary border-t-transparent" role="status"></span>
      </div>

      @if ($keyword !== '')
        <button type="button" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost shrink-0" wire:click="clear">
          <i class="ki-filled ki-cross"></i>
        </button>
      @endif

      <button type="button" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-modal-dismiss="true">
        <i class="ki-filled ki-cross-circle"></i>
      </button>
    </div>

    <div class="kt-modal-body px-5 pb-5 pt-3">
      @if (! $ready)
        <p class="py-9 text-center text-sm text-secondary-foreground">@lang('search.hint')</p>
      @elseif ($total === 0)
        <p class="py-9 text-center text-sm text-secondary-foreground">@lang('search.no_results')</p>
      @else
        {{-- Tabs: one per entity the user may search, each with its hit count. --}}
        <div class="mb-4 flex kt-scrollable-x-auto border-b border-border">
          @foreach ($counts as $name => $count)
            <button type="button" wire:key="tab-{{ $name }}" wire:click="switchTab('{{ $name }}')"
              class="-mb-px flex shrink-0 items-center gap-2 border-b-2 px-3 py-2 text-sm font-medium {{ $active === $name ? 'border-primary text-primary' : 'border-transparent text-secondary-foreground hover:text-foreground' }}">
              @lang('search.groups.'.$name)
              <span class="kt-badge kt-badge-sm kt-badge-outline {{ $active === $name ? 'kt-badge-primary' : '' }}">{{ $count }}</span>
            </button>
          @endforeach
        </div>

        <div class="max-h-[350px] kt-scrollable-y-auto">
          @forelse ($items as $item)
            @php
              $href = match ($active) {
                'properties' => route('backend.properties.show', $item),
                'people' => route('backend.people.show', $item),
                'contracts' => route('backend.contracts.show', $item),
                'enquiries' => route('backend.enquiries.show', $item),
                default => '#',
              };
            @endphp
            <a class="flex flex-col rounded-lg px-2.5 py-2 hover:bg-accent/60" href="{{ $href }}" wire:key="{{ $active }}-{{ $item->id }}">
              @switch($active)
                @case('properties')
                  <span class="text-sm font-semibold text-mono">{{ $item->code }} · {{ app()->getLocale() === 'th' && $item->name_th ? $item->name_th : $item->name }}</span>
                  <span class="text-2sm text-muted-foreground">{{ $item->location ?: ($item->district?->name_en ?? '') }} · {{ $item->type->label() }} · {{ $item->status->label() }}</span>
                  @break
                @case('people')
                  <span class="text-sm font-semibold text-mono">{{ $item->fullName() }}</span>
                  <span class="text-2sm text-muted-foreground">{{ $item->type->label() }} · {{ $item->phone ?: $item->email }}</span>
                  @break
                @case('contracts')
                  <span class="text-sm font-semibold text-mono">{{ $item->code }}</span>
                  <span class="text-2sm text-muted-foreground">{{ $item->property?->code }} ({{ $item->property?->name }}) · {{ $item->customer?->fullName() }} · {{ $item->status->label() }}</span>
                  @break
                @case('enquiries')
                  <span class="text-sm font-semibold text-mono">{{ $item->name }}</span>
                  <span class="text-2sm text-muted-foreground">{{ $item->phone }} · {{ $item->status->label() }}</span>
                  @break
              @endswitch
            </a>
          @empty
            <p class="py-5 text-center text-sm text-secondary-foreground">@lang('search.no_results')</p>
          @endforelse
        </div>
      @endif
    </div>
  </div>
</div>
