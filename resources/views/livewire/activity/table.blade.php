<div>
  <div class="kt-card kt-card-grid min-w-full">
    <div class="kt-card-header flex-wrap gap-3">
      <h3 class="kt-card-title flex items-center gap-2">
        <i class="ki-filled ki-time text-lg text-primary"></i>
        @lang('activity.title')
        <span class="kt-badge kt-badge-sm kt-badge-secondary">{{ number_format($activities->total()) }}</span>
      </h3>

      <div class="kt-card-toolbar flex flex-wrap items-center gap-2.5">
        {{-- Action filter: every code logged so far --}}
        <div wire:ignore x-data="ktSelect('action', { live: true })"
          x-effect="if ($refs.select && $refs.select.value !== $wire.action) { $refs.select.value = $wire.action; instance?.update?.(); }"
          class="w-48">
          <select x-ref="select" class="kt-select w-full" aria-label="@lang('activity.action')">
            <option value="all">@lang('activity.all_actions')</option>
            @foreach ($actions as $option)
              <option value="{{ $option }}" @selected($action === $option)>{{ $labels[$option] ?? $option }}</option>
            @endforeach
          </select>
        </div>

        <x-search-box wire:model.live.debounce.250ms="q" :value="$q" :placeholder="__('activity.search_placeholder')" class="w-64 max-w-full" />
        <x-reset-filters :show="$this->isFiltered()" />
      </div>
    </div>

    <div class="kt-card-table">
      <div class="kt-scrollable-x-auto">
        <table class="kt-table kt-table-border">
          <thead>
            <tr>
              <th>@lang('activity.when')</th>
              <th>@lang('activity.user')</th>
              <th>@lang('activity.action')</th>
              <th>@lang('activity.record')</th>
              <th>@lang('activity.changes')</th>
              <th>@lang('activity.from')</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($activities as $activity)
              @php
                $subject = $activity->subject;
                $changes = $activity->attribute_changes?->get('attributes') ?? [];
                $old = $activity->attribute_changes?->get('old') ?? [];
                $props = $activity->properties ?? collect();
                $extra = $props->except(['ip', 'browser', 'agent']);
              @endphp
              <tr wire:key="activity-row-{{ $activity->id }}">
                <td class="whitespace-nowrap text-sm">
                  {{ $activity->created_at->timezone('Asia/Bangkok')->format('d/m/Y') }}
                  <span class="block text-2xs text-muted-foreground">{{ $activity->created_at->timezone('Asia/Bangkok')->format('H:i:s') }}</span>
                </td>

                <td class="text-sm">
                  @if ($activity->causer)
                    <span class="font-medium text-foreground">{{ $activity->causer->name }}</span>
                    <span class="block text-2xs text-muted-foreground">{{ $activity->causer->username }}</span>
                  @else
                    <span class="text-muted-foreground">—</span>
                  @endif
                </td>

                <td>
                  <span class="kt-badge kt-badge-sm kt-badge-outline whitespace-nowrap" title="{{ $activity->description }}">{{ $labels[$activity->description] ?? $activity->description }}</span>
                </td>

                {{-- The record acted on: its kind, then its code or name, else its id --}}
                <td class="text-sm">
                  @if ($activity->subject_type)
                    <span class="text-foreground">{{ $subject?->code ?? $subject?->name ?? $subject?->first_name ?? '#'.$activity->subject_id }}</span>
                    <span class="block text-2xs text-muted-foreground">{{ class_basename(\Illuminate\Database\Eloquent\Relations\Relation::getMorphedModel($activity->subject_type) ?? $activity->subject_type) }}</span>
                  @else
                    <span class="text-muted-foreground">—</span>
                  @endif
                </td>

                {{-- An update's old → new values, then the action's own details (amount added, role...) --}}
                <td class="text-2xs">
                  @foreach ($changes as $field => $value)
                    <div class="whitespace-nowrap" title="{{ json_encode($old[$field] ?? null, JSON_UNESCAPED_UNICODE) }} → {{ json_encode($value, JSON_UNESCAPED_UNICODE) }}">
                      <span class="text-muted-foreground">{{ $field }}:</span>
                      <span class="text-muted-foreground line-through">{{ Str::limit(json_encode($old[$field] ?? null, JSON_UNESCAPED_UNICODE), 30) }}</span>
                      → <span class="text-foreground">{{ Str::limit(json_encode($value, JSON_UNESCAPED_UNICODE), 30) }}</span>
                    </div>
                  @endforeach
                  @foreach ($extra as $field => $value)
                    <div class="whitespace-nowrap">
                      <span class="text-muted-foreground">{{ $field }}:</span>
                      <span class="text-foreground">{{ Str::limit(is_scalar($value) ? (string) $value : json_encode($value, JSON_UNESCAPED_UNICODE), 30) }}</span>
                    </div>
                  @endforeach
                </td>

                <td class="whitespace-nowrap text-sm" title="{{ $props->get('browser') }}">
                  {{ $props->get('ip') ?? '—' }}
                  <span class="block text-2xs text-muted-foreground">{{ $props->get('agent') }}</span>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center py-6 text-muted-foreground">@lang('activity.empty')</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="kt-card-footer flex flex-wrap items-center justify-between gap-3">
        <div class="text-sm text-muted-foreground">{{ __('common.showing_of', [
          'from' => $activities->firstItem() ?? 0,
          'to' => $activities->lastItem() ?? 0,
          'total' => $activities->total(),
        ]) }}</div>
        {{ $activities->onEachSide(1)->links() }}
      </div>
    </div>
  </div>
</div>
