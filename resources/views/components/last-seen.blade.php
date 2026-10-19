{{-- "Last seen" cell: Online, "3 hours ago" (exact time on hover) or — without a live session.
     Usage: <td class="text-sm"><x-last-seen :seen="$lastSeen[$user->id] ?? null" /></td>  (User::lastSeen) --}}
@props(['seen' => null])
@if (! $seen)
  <span class="text-muted-foreground">—</span>
@elseif ($seen->gte(now()->subMinutes(\App\Models\User::ONLINE_MINUTES)))
  <span class="inline-flex items-center gap-1.5 font-medium text-profit"><span class="size-2 rounded-full bg-green-500"></span>@lang('users.table.online')</span>
@else
  <span class="text-secondary-foreground" title="{{ $seen->translatedFormat('d M Y H:i') }}">{{ $seen->diffForHumans() }}</span>
@endif