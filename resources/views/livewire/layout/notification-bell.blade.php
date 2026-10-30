<div wire:poll.60s>
  <button type="button" aria-label="@lang('notifications.title')"
    class="kt-btn kt-btn-ghost kt-btn-icon hover:bg-primary/10 hover:[&_i]:text-primary relative size-9 rounded-full"
    data-kt-drawer-toggle="#notifications_drawer">
    <i class="ki-filled ki-notification-status text-lg"></i>
    @if ($unreadCount > 0)
      <span class="absolute end-1 top-1 size-[7px] rounded-full bg-destructive"></span>
    @endif
  </button>
</div>
