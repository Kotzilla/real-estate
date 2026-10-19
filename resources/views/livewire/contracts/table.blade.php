<div>
  <div class="kt-card kt-card-grid min-w-full">
    <div class="kt-card-header flex-wrap gap-3">
      <h3 class="kt-card-title flex items-center gap-2">
        <i class="ki-filled ki-document text-lg text-primary"></i>
        @lang('contracts.title')
        <span class="kt-badge kt-badge-sm kt-badge-secondary">{{ number_format($contracts->total()) }}</span>
      </h3>

      <div class="kt-card-toolbar flex flex-wrap items-center gap-2.5">
        @foreach ([
          'type' => [__('contracts.table.all_types'), \App\Enums\ContractType::cases()],
          'status' => [__('contracts.table.all_statuses'), \App\Enums\ContractStatus::cases()],
        ] as $filter => [$all, $cases])
          <div wire:ignore x-data="ktSelect('{{ $filter }}', { live: true })"
            x-effect="if ($refs.select && $refs.select.value !== $wire.{{ $filter }}) { $refs.select.value = $wire.{{ $filter }}; instance?.update?.(); }"
            class="w-40">
            <select x-ref="select" class="kt-select w-full" aria-label="@lang('contracts.table.filter_'.$filter)">
              <option value="all">{{ $all }}</option>
              @foreach ($cases as $case)
                <option value="{{ $case->value }}" @selected($this->{$filter} === $case->value)>{{ $case->label() }}</option>
              @endforeach
            </select>
          </div>
        @endforeach

        <x-search-box wire:model.live.debounce.250ms="q" :value="$q" :placeholder="__('contracts.table.search_placeholder')" class="w-64 max-w-full" />
        <x-reset-filters :show="$this->isFiltered()" />

        @can('create', \App\Models\Contract::class)
          <a href="{{ route('backend.contracts.create') }}" class="kt-btn kt-btn-sm kt-btn-primary gap-1.5 shrink-0">
            <i class="ki-filled ki-plus"></i>
            <span>@lang('contracts.table.add')</span>
          </a>
        @endcan
      </div>
    </div>

    <div class="kt-card-table">
      <div class="kt-scrollable-x-auto">
        <table class="kt-table kt-table-border">
          <thead>
            <tr>
              <x-sort-th field="signed" :sort="$sort" :dir="$dir">@lang('contracts.table.contract')</x-sort-th>
              <th>@lang('contracts.table.property')</th>
              <th>@lang('contracts.table.customer')</th>
              <th>@lang('contracts.table.type')</th>
              <th class="text-end">@lang('contracts.table.price')</th>
              <x-sort-th field="end" :sort="$sort" :dir="$dir">@lang('contracts.table.period')</x-sort-th>
              <x-sort-th field="commission" class="text-end" :sort="$sort" :dir="$dir">@lang('contracts.table.commission')</x-sort-th>
              <th>@lang('contracts.table.status')</th>
              <th class="w-[120px] text-end">@lang('contracts.table.actions')</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($contracts as $contract)
              <tr wire:key="contract-row-{{ $contract->id }}">
                <td>
                  <div class="flex flex-col">
                    <a href="{{ route('backend.contracts.show', $contract) }}" class="font-mono font-medium text-foreground hover:text-primary">{{ $contract->code }}</a>
                    <span class="text-2xs text-muted-foreground">{{ $contract->signed_on->format('d/m/Y') }}</span>
                  </div>
                </td>

                <td class="text-sm">
                  <a href="{{ route('backend.properties.show', $contract->property) }}" class="hover:text-primary">{{ $contract->property->title() }}</a>
                  <span class="block font-mono text-2xs text-muted-foreground">{{ $contract->property->code }}</span>
                </td>

                <td class="text-sm">
                  <a href="{{ route('backend.people.show', $contract->customer) }}" class="hover:text-primary">{{ $contract->customer->fullName() }}</a>
                </td>

                <td><span class="kt-badge kt-badge-sm {{ $contract->type->badgeClass() }}">{{ $contract->type->label() }}</span></td>

                <td class="whitespace-nowrap text-end text-sm">
                  {{ number_format((float) $contract->price) }}
                  @if ($contract->isRent())
                    <span class="text-2xs text-muted-foreground">@lang('properties.per_month')</span>
                    <span class="block text-2xs text-muted-foreground">@lang('contracts.months_count', ['count' => $contract->months])</span>
                  @endif
                </td>

                <td class="whitespace-nowrap text-sm">
                  {{ $contract->start_on->format('d/m/Y') }} – {{ $contract->end_on?->format('d/m/Y') ?? '…' }}
                </td>

                <td class="text-end text-sm">{{ number_format((float) $contract->commission) }}</td>

                <td><span class="kt-badge kt-badge-sm {{ $contract->status->badgeClass() }}">{{ $contract->status->label() }}</span></td>

                <td class="whitespace-nowrap text-end">
                  <a href="{{ route('backend.contracts.print', [$contract, 'en']) }}" target="_blank" class="kt-btn kt-btn-sm kt-btn-ghost px-1.5" title="@lang('contracts.print_en')">EN</a>
                  <a href="{{ route('backend.contracts.print', [$contract, 'th']) }}" target="_blank" class="kt-btn kt-btn-sm kt-btn-ghost px-1.5" title="@lang('contracts.print_th')">TH</a>
                  @can('update', $contract)
                    <a href="{{ route('backend.contracts.edit', $contract) }}" class="kt-btn kt-btn-icon kt-btn-sm kt-btn-ghost" title="@lang('contracts.table.edit')">
                      <i class="ki-filled ki-pencil text-base"></i>
                    </a>
                  @endcan
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="9" class="py-6 text-center text-muted-foreground">@lang('contracts.table.empty')</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="kt-card-footer flex flex-wrap items-center justify-between gap-3">
        <div class="text-sm text-muted-foreground">{{ __('common.showing_of', [
          'from' => $contracts->firstItem() ?? 0,
          'to' => $contracts->lastItem() ?? 0,
          'total' => $contracts->total(),
        ]) }}</div>
        {{ $contracts->onEachSide(1)->links() }}
      </div>
    </div>
  </div>
</div>
