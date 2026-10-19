<div>
  <div class="kt-card kt-card-grid min-w-full">
    <div class="kt-card-header flex-wrap gap-3">
      <h3 class="kt-card-title flex items-center gap-2">
        <i class="ki-filled ki-people text-lg text-primary"></i>
        @lang('people.lists.'.$type)
        <span class="kt-badge kt-badge-sm kt-badge-secondary">{{ number_format($people->total()) }}</span>
      </h3>

      <div class="kt-card-toolbar flex flex-wrap items-center gap-2.5">
        <x-search-box wire:model.live.debounce.250ms="q" :value="$q" :placeholder="__('people.table.search_placeholder')" class="w-64 max-w-full" />
        <x-reset-filters :show="$this->isFiltered()" />

        @can('create', \App\Models\Person::class)
          <a href="{{ route('backend.people.create', $type !== 'all' ? ['type' => $type] : []) }}" class="kt-btn kt-btn-sm kt-btn-primary gap-1.5 shrink-0">
            <i class="ki-filled ki-plus"></i>
            <span>@lang('people.table.add')</span>
          </a>
        @endcan
      </div>
    </div>

    <div class="kt-card-table">
      <div class="kt-scrollable-x-auto">
        <table class="kt-table kt-table-border">
          <thead>
            <tr>
              <th>@lang('people.table.name')</th>
              <th>@lang('people.table.type')</th>
              <th>@lang('people.table.contact')</th>
              <th>@lang('people.table.nationality')</th>
              <th class="text-end">@lang('people.table.properties')</th>
              <th class="text-end">@lang('people.table.contracts')</th>
              <th class="w-[90px] text-end">@lang('people.table.actions')</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($people as $person)
              <tr wire:key="person-row-{{ $person->id }}">
                {{-- English name (opens the contact), Thai name under it --}}
                <td>
                  <div class="flex min-w-0 flex-col">
                    <a href="{{ route('backend.people.show', $person) }}" class="font-medium text-foreground hover:text-primary">{{ $person->fullName('en') }}</a>
                    @if ($person->first_name_th)
                      <span class="truncate text-2xs text-muted-foreground">{{ $person->fullName('th') }}</span>
                    @endif
                  </div>
                </td>

                <td><span class="kt-badge kt-badge-sm {{ $person->type->badgeClass() }}">{{ $person->type->label() }}</span></td>

                <td class="whitespace-nowrap text-sm">
                  {{ $person->phone ?: '—' }}
                  @if ($person->email)
                    <span class="block text-2xs text-muted-foreground">{{ $person->email }}</span>
                  @endif
                </td>

                <td class="text-sm">{{ $person->nationality ?: '—' }}</td>
                <td class="text-end text-sm">{{ number_format($person->properties_count) }}</td>
                <td class="text-end text-sm">{{ number_format($person->contracts_count) }}</td>

                <td class="whitespace-nowrap text-end">
                  <a href="{{ route('backend.people.show', $person) }}" class="kt-btn kt-btn-icon kt-btn-sm kt-btn-ghost" title="@lang('people.table.view')">
                    <i class="ki-filled ki-eye text-base"></i>
                  </a>
                  @can('update', $person)
                    <a href="{{ route('backend.people.edit', $person) }}" class="kt-btn kt-btn-icon kt-btn-sm kt-btn-ghost" title="@lang('people.table.edit')">
                      <i class="ki-filled ki-pencil text-base"></i>
                    </a>
                  @endcan
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="py-6 text-center text-muted-foreground">@lang('people.table.empty')</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="kt-card-footer flex flex-wrap items-center justify-between gap-3">
        <div class="text-sm text-muted-foreground">{{ __('common.showing_of', [
          'from' => $people->firstItem() ?? 0,
          'to' => $people->lastItem() ?? 0,
          'total' => $people->total(),
        ]) }}</div>
        {{ $people->onEachSide(1)->links() }}
      </div>
    </div>
  </div>
</div>
