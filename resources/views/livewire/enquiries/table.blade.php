<div>
  <div class="kt-card kt-card-grid min-w-full">
    <div class="kt-card-header flex-wrap gap-3">
      <h3 class="kt-card-title flex items-center gap-2">
        <i class="ki-filled ki-calendar-tick text-lg text-primary"></i>
        @lang('enquiries.title')
        <span class="kt-badge kt-badge-sm kt-badge-secondary">{{ number_format($enquiries->total()) }}</span>
      </h3>

      <div class="kt-card-toolbar flex flex-wrap items-center gap-2.5">
        <div wire:ignore x-data="ktSelect('status', { live: true })"
          x-effect="if ($refs.select && $refs.select.value !== $wire.status) { $refs.select.value = $wire.status; instance?.update?.(); }"
          class="w-40">
          <select x-ref="select" class="kt-select w-full" aria-label="@lang('enquiries.filter')">
            <option value="all">@lang('enquiries.all_statuses')</option>
            @foreach (\App\Enums\EnquiryStatus::cases() as $case)
              <option value="{{ $case->value }}" @selected($status === $case->value)>{{ $case->label() }}</option>
            @endforeach
          </select>
        </div>

        <x-search-box wire:model.live.debounce.250ms="q" :value="$q" :placeholder="__('enquiries.search_placeholder')" class="w-64 max-w-full" />
        <x-reset-filters :show="$this->isFiltered()" />
      </div>
    </div>

    <div class="kt-card-table">
      <div class="kt-scrollable-x-auto">
        <table class="kt-table kt-table-border">
          <thead>
            <tr>
              <th>@lang('enquiries.received')</th>
              <th>@lang('enquiries.visitor')</th>
              <th>@lang('enquiries.property')</th>
              <th>@lang('enquiries.message')</th>
              <th>@lang('enquiries.status')</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($enquiries as $enquiry)
              <tr wire:key="enquiry-row-{{ $enquiry->id }}">
                <td class="whitespace-nowrap text-sm">
                  {{ $enquiry->created_at->format('d/m/Y H:i') }}
                  <span class="block text-2xs text-muted-foreground">{{ $enquiry->created_at->diffForHumans() }}</span>
                </td>

                <td class="text-sm">
                  <a href="{{ route('backend.enquiries.show', $enquiry) }}" class="font-medium text-foreground hover:text-primary">{{ $enquiry->name }}</a>
                  <a href="tel:{{ $enquiry->phone }}" class="block font-mono text-2xs text-muted-foreground hover:text-primary">{{ $enquiry->phone }}</a>
                </td>

                <td class="text-sm">
                  @if ($enquiry->enquirable instanceof \App\Models\Property)
                    <a href="{{ route('backend.properties.show', $enquiry->enquirable) }}" class="hover:text-primary">{{ $enquiry->enquirable->title() }}</a>
                    <span class="block font-mono text-2xs text-muted-foreground">{{ $enquiry->enquirable->code }}</span>
                  @else
                    <span class="text-muted-foreground">—</span>
                  @endif
                </td>

                <td class="max-w-[360px] text-sm">
                  {{ \Illuminate\Support\Str::limit($enquiry->message, 120) }}
                  @if ($enquiry->comments_count)
                    <span class="mt-1 flex items-center gap-1 text-2xs text-muted-foreground"><i class="ki-filled ki-messages"></i> @lang('enquiries.notes_count', ['count' => $enquiry->comments_count])</span>
                  @endif
                </td>

                <td><span class="kt-badge kt-badge-sm {{ $enquiry->status->badgeClass() }}">{{ $enquiry->status->label() }}</span></td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="py-6 text-center text-muted-foreground">@lang('enquiries.empty')</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="kt-card-footer flex flex-wrap items-center justify-between gap-3">
        <div class="text-sm text-muted-foreground">{{ __('common.showing_of', [
          'from' => $enquiries->firstItem() ?? 0,
          'to' => $enquiries->lastItem() ?? 0,
          'total' => $enquiries->total(),
        ]) }}</div>
        {{ $enquiries->onEachSide(1)->links() }}
      </div>
    </div>
  </div>
</div>
