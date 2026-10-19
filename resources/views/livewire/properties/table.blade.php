<div>
  <div class="kt-card kt-card-grid min-w-full">
    <div class="kt-card-header flex-wrap gap-3">
      <h3 class="kt-card-title flex items-center gap-2">
        <i class="ki-filled ki-home-3 text-lg text-primary"></i>
        @lang('properties.offers.'.$offer)
        <span class="kt-badge kt-badge-sm kt-badge-secondary">{{ number_format($properties->total()) }}</span>
      </h3>

      <div class="kt-card-toolbar flex flex-wrap items-center gap-2.5">
        @foreach ([
          'type' => [__('properties.table.all_types'), collect(\App\Enums\PropertyType::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])],
          'status' => [__('properties.table.all_statuses'), collect(\App\Enums\PropertyStatus::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])],
          'province' => [__('properties.table.all_provinces'), $provinces->mapWithKeys(fn ($p) => [(string) $p->id => $p->label()])],
        ] as $filter => [$all, $options])
          <div wire:ignore x-data="ktSelect('{{ $filter }}', { live: true{{ $filter === 'province' ? ', enableSearch: true' : '' }} })"
            x-effect="if ($refs.select && $refs.select.value !== $wire.{{ $filter }}) { $refs.select.value = $wire.{{ $filter }}; instance?.update?.(); }"
            class="w-40">
            <select x-ref="select" class="kt-select w-full" aria-label="@lang('properties.table.filter_'.$filter)">
              <option value="all">{{ $all }}</option>
              @foreach ($options as $value => $label)
                <option value="{{ $value }}" @selected($this->{$filter} === (string) $value)>{{ $label }}</option>
              @endforeach
            </select>
          </div>
        @endforeach

        <x-search-box wire:model.live.debounce.250ms="q" :value="$q" :placeholder="__('properties.table.search_placeholder')" class="w-64 max-w-full" />
        <x-reset-filters :show="$this->isFiltered()" />

        @can('create', \App\Models\Property::class)
          <a href="{{ route('backend.properties.create') }}" class="kt-btn kt-btn-sm kt-btn-primary gap-1.5 shrink-0">
            <i class="ki-filled ki-plus"></i>
            <span>@lang('properties.table.add')</span>
          </a>
        @endcan
      </div>
    </div>

    <div class="kt-card-table">
      <div class="kt-scrollable-x-auto">
        <table class="kt-table kt-table-border">
          <thead>
            <tr>
              <x-sort-th field="code" :sort="$sort" :dir="$dir">@lang('properties.table.property')</x-sort-th>
              <th>@lang('properties.table.type')</th>
              <th>@lang('properties.table.location')</th>
              <th class="text-center">@lang('properties.table.rooms')</th>
              <x-sort-th field="sale" class="text-end" :sort="$sort" :dir="$dir">@lang('properties.table.sale_price')</x-sort-th>
              <x-sort-th field="rent" class="text-end" :sort="$sort" :dir="$dir">@lang('properties.table.rent_price')</x-sort-th>
              <th>@lang('properties.table.owner')</th>
              <th>@lang('properties.table.status')</th>
              <th class="w-[90px] text-end">@lang('properties.table.actions')</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($properties as $property)
              <tr wire:key="property-row-{{ $property->id }}">
                {{-- Cover photo, name (opens the listing), code under it --}}
                <td>
                  <a href="{{ route('backend.properties.show', $property) }}" class="flex items-center gap-3 hover:text-primary">
                    @if ($property->coverPhoto)
                      <img src="{{ $property->coverPhoto->src('sm') }}" alt="" loading="lazy" class="size-11 shrink-0 rounded-md object-cover">
                    @else
                      <span class="flex size-11 shrink-0 items-center justify-center rounded-md bg-muted text-muted-foreground"><i class="ki-filled ki-picture text-lg"></i></span>
                    @endif
                    <span class="flex min-w-0 flex-col">
                      <span class="font-medium text-foreground">{{ $property->title() }}</span>
                      <span class="font-mono text-2xs text-muted-foreground">{{ $property->code }}</span>
                    </span>
                  </a>
                </td>

                <td><span class="kt-badge kt-badge-sm {{ $property->type->badgeClass() }}">{{ $property->type->label() }}</span></td>

                <td class="text-sm">
                  {{ $property->location ?: $property->district?->label() ?: '—' }}
                  <span class="block text-2xs text-muted-foreground">{{ $property->province?->label() }}</span>
                </td>

                <td class="whitespace-nowrap text-center text-sm text-secondary-foreground">
                  @if ($property->bedrooms || $property->bathrooms)
                    <span title="@lang('properties.form.bedrooms')"><i class="ki-filled ki-home-1"></i> {{ $property->bedrooms }}</span>
                    <span class="ms-2" title="@lang('properties.form.bathrooms')"><i class="ki-filled ki-drop"></i> {{ $property->bathrooms }}</span>
                  @else
                    —
                  @endif
                </td>

                <td class="text-end text-sm">{{ $property->is_sale ? number_format((float) $property->sale_price) : '—' }}</td>
                <td class="text-end text-sm">
                  @if ($property->is_rent)
                    {{ number_format((float) $property->rent_price) }}<span class="text-2xs text-muted-foreground">@lang('properties.per_month')</span>
                  @else
                    —
                  @endif
                </td>

                <td class="text-sm">
                  @if ($property->owner)
                    <a href="{{ route('backend.people.show', $property->owner) }}" class="hover:text-primary">{{ $property->owner->fullName() }}</a>
                  @else
                    <span class="text-muted-foreground">—</span>
                  @endif
                </td>

                <td><span class="kt-badge kt-badge-sm {{ $property->status->badgeClass() }}">{{ $property->status->label() }}</span></td>

                <td class="whitespace-nowrap text-end">
                  <a href="{{ route('backend.properties.show', $property) }}" class="kt-btn kt-btn-icon kt-btn-sm kt-btn-ghost" title="@lang('properties.table.view')">
                    <i class="ki-filled ki-eye text-base"></i>
                  </a>
                  @can('update', $property)
                    <a href="{{ route('backend.properties.edit', $property) }}" class="kt-btn kt-btn-icon kt-btn-sm kt-btn-ghost" title="@lang('properties.table.edit')">
                      <i class="ki-filled ki-pencil text-base"></i>
                    </a>
                  @endcan
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="9" class="py-6 text-center text-muted-foreground">@lang('properties.table.empty')</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="kt-card-footer flex flex-wrap items-center justify-between gap-3">
        <div class="text-sm text-muted-foreground">{{ __('common.showing_of', [
          'from' => $properties->firstItem() ?? 0,
          'to' => $properties->lastItem() ?? 0,
          'total' => $properties->total(),
        ]) }}</div>
        {{ $properties->onEachSide(1)->links() }}
      </div>
    </div>
  </div>
</div>
