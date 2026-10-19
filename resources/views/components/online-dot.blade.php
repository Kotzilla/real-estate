{{-- Green dot on a user's avatar while they are online. Put it inside the avatar, which needs `relative`.
     Usage: <x-online-dot :seen="$lastSeen[$user->id] ?? null" />  (User::lastSeen) --}}
@props(['seen' => null])
@if ($seen && $seen->gte(now()->subMinutes(\App\Models\User::ONLINE_MINUTES)))
  <span class="absolute size-3 rounded-full border-2 bg-green-500" style="right: -2px; bottom: -2px; border-color: var(--background)" title="@lang('users.table.online')"></span>
@endif