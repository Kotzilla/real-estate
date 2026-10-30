{{-- The root element IS the drawer and this component is mounted under <body> (layouts/app), so KTUI never moves it out
     of Livewire. wire:ignore.self keeps KTUI's own classes/attributes on it across re-renders. --}}
<div wire:ignore.self wire:poll.60s
  class="kt-drawer kt-drawer-end card bottom-5 end-5 top-5 hidden w-[450px] max-w-[90%] flex-col rounded-xl border border-border"
  data-kt-drawer="true" id="notifications_drawer">

  <div class="flex items-center justify-between gap-2.5 border-b border-b-border px-5 py-3 text-sm font-semibold text-mono">
    <span class="flex items-center gap-2">
      <i class="ki-filled ki-notification-on text-primary text-base"></i>
      <span>@lang('notifications.title')</span>
      @if ($unreadCount > 0)
        <span class="kt-badge kt-badge-light kt-badge-primary kt-badge-sm">{{ $unreadCount }}</span>
      @endif
    </span>
    <button type="button" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-drawer-dismiss="true">
      <i class="ki-filled ki-cross"></i>
    </button>
  </div>

  {{-- Tabs: All, Enquiries, Contracts --}}
  <div class="flex items-center border-b border-border px-5 gap-4">
    <button type="button" wire:click="setTab('all')"
      class="-mb-px flex items-center gap-1.5 border-b-2 py-3 text-xs font-semibold uppercase tracking-wider transition-colors {{ $tab === 'all' ? 'border-primary text-primary' : 'border-transparent text-secondary-foreground hover:text-foreground' }}">
      <span>@lang('notifications.tabs.all')</span>
      @if ($counts['all'] > 0)
        <span class="kt-badge kt-badge-xs {{ $unreadCounts['all'] > 0 ? 'kt-badge-light kt-badge-primary' : 'kt-badge-outline' }}">
          {{ $unreadCounts['all'] > 0 ? $unreadCounts['all'] : $counts['all'] }}
        </span>
      @endif
    </button>

    <button type="button" wire:click="setTab('enquiries')"
      class="-mb-px flex items-center gap-1.5 border-b-2 py-3 text-xs font-semibold uppercase tracking-wider transition-colors {{ $tab === 'enquiries' ? 'border-primary text-primary' : 'border-transparent text-secondary-foreground hover:text-foreground' }}">
      <i class="ki-filled ki-sms text-sm"></i>
      <span>@lang('notifications.tabs.enquiries')</span>
      @if ($counts['enquiries'] > 0)
        <span class="kt-badge kt-badge-xs {{ $unreadCounts['enquiries'] > 0 ? 'kt-badge-light kt-badge-primary' : 'kt-badge-outline' }}">
          {{ $unreadCounts['enquiries'] > 0 ? $unreadCounts['enquiries'] : $counts['enquiries'] }}
        </span>
      @endif
    </button>

    <button type="button" wire:click="setTab('contracts')"
      class="-mb-px flex items-center gap-1.5 border-b-2 py-3 text-xs font-semibold uppercase tracking-wider transition-colors {{ $tab === 'contracts' ? 'border-primary text-primary' : 'border-transparent text-secondary-foreground hover:text-foreground' }}">
      <i class="ki-filled ki-document text-sm"></i>
      <span>@lang('notifications.tabs.contracts')</span>
      @if ($counts['contracts'] > 0)
        <span class="kt-badge kt-badge-xs {{ $unreadCounts['contracts'] > 0 ? 'kt-badge-light kt-badge-warning' : 'kt-badge-outline' }}">
          {{ $unreadCounts['contracts'] > 0 ? $unreadCounts['contracts'] : $counts['contracts'] }}
        </span>
      @endif
    </button>
  </div>

  <div class="grow kt-scrollable-y-auto divide-y divide-border">
    @forelse ($notifications as $notification)
      @php
        $cat = $notification->data['category'] ?? null;
        $isEnquiry = $cat === 'enquiries' || str_contains(strtolower($notification->type), 'enquiry') || str_contains(strtolower($notification->data['message'] ?? ''), 'enquiry');
        $isContract = $cat === 'contracts' || str_contains(strtolower($notification->type), 'contract') || str_contains(strtolower($notification->data['message'] ?? ''), 'contract');
      @endphp
      <div class="flex items-start gap-3 px-5 py-3.5 transition-colors {{ $notification->read_at ? 'hover:bg-accent/40' : 'bg-primary/5 hover:bg-primary/10' }}"
        wire:key="notification-{{ $notification->id }}">
        {{-- Category Icon --}}
        <div class="flex size-8 shrink-0 items-center justify-center rounded-lg mt-0.5 {{ $isEnquiry ? 'bg-primary/10 text-primary' : ($isContract ? 'bg-warning/10 text-warning' : 'bg-secondary text-secondary-foreground') }}">
          @if ($isEnquiry)
            <i class="ki-filled ki-sms text-base"></i>
          @elseif ($isContract)
            <i class="ki-filled ki-document text-base"></i>
          @else
            <i class="ki-filled ki-notification text-base"></i>
          @endif
        </div>

        <div class="grow min-w-0">
          <div class="text-sm text-foreground leading-snug">
            @if (! empty($notification->data['url']))
              <a class="hover:text-primary font-medium" href="{{ $notification->data['url'] }}">{{ $notification->data['message'] ?? \Illuminate\Support\Str::headline(class_basename($notification->type)) }}</a>
            @else
              <span class="font-medium">{{ $notification->data['message'] ?? \Illuminate\Support\Str::headline(class_basename($notification->type)) }}</span>
            @endif
          </div>
          <span class="text-2xs text-secondary-foreground mt-1 block">{{ $notification->created_at->diffForHumans() }}</span>
        </div>

        @unless ($notification->read_at)
          <button type="button" class="kt-btn kt-btn-icon kt-btn-ghost kt-btn-xs shrink-0 text-muted-foreground hover:text-primary"
            title="@lang('notifications.mark_read')" wire:click="markAsRead('{{ $notification->id }}')">
            <i class="ki-filled ki-check text-sm"></i>
          </button>
        @endunless
      </div>
    @empty
      <div class="flex flex-col items-center justify-center py-16 px-4 text-center">
        <div class="flex size-12 items-center justify-center rounded-full bg-secondary mb-3 text-secondary-foreground">
          <i class="ki-filled ki-notification-status text-xl"></i>
        </div>
        <span class="text-sm font-medium text-foreground">@lang('notifications.empty')</span>
      </div>
    @endforelse
  </div>

  @if ($unreadCount > 0)
    <div class="border-t border-t-border p-3">
      <button type="button" class="kt-btn kt-btn-sm kt-btn-ghost w-full justify-center gap-1.5" wire:click="markAllAsRead">
        <i class="ki-filled ki-double-check text-sm"></i>
        <span>@lang('notifications.mark_all_read')</span>
      </button>
    </div>
  @endif
</div>
