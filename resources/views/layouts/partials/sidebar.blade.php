@php
  // The policies decide these, so the menu and the routes always agree
  $user = auth()->user();
  $canManageUsers = (bool) $user?->can('viewAny', \App\Models\User::class);
  $canViewProperties = (bool) $user?->can('viewAny', \App\Models\Property::class);
  $canViewPeople = (bool) $user?->can('viewAny', \App\Models\Person::class);
  $canViewContracts = (bool) $user?->can('viewAny', \App\Models\Contract::class);
  $canViewEnquiries = (bool) $user?->can('viewAny', \App\Models\Enquiry::class);
  $canViewActivity = (bool) $user?->can('viewAny', \Spatie\Activitylog\Models\Activity::class);

  $isArchivedUsers = request()->routeIs('backend.users.archived') || request('status') === 'archived';
  $offer = request()->routeIs('backend.properties.index') ? request('offer') : null;
  $personType = request()->routeIs('backend.people.index') ? request('type') : null;

  $mainItems = [
    [__('Dashboard'), 'element-11', route('backend.dashboard'), request()->routeIs('backend.dashboard'), null],
  ];

  if ($canViewProperties) {
    $mainItems[] = [__('Properties'), 'home-3', route('backend.properties.index'), request()->routeIs('backend.properties.*'), [
      [__('All properties'), route('backend.properties.index'), request()->routeIs('backend.properties.*') && ! $offer],
      [__('For sale'), route('backend.properties.index', ['offer' => 'sale']), $offer === 'sale'],
      [__('For rent'), route('backend.properties.index', ['offer' => 'rent']), $offer === 'rent'],
    ]];
  }

  if ($canViewContracts) {
    $mainItems[] = [__('Contracts'), 'document', route('backend.contracts.index'), request()->routeIs('backend.contracts.*'), null];
  }

  if ($canViewEnquiries) {
    // the open ones (nobody has picked them up yet), so staff see new requests from any page
    $newEnquiries = \App\Models\Enquiry::query()->where('status', \App\Enums\EnquiryStatus::Open->value)->count();
    $mainItems[] = [__('Enquiries').($newEnquiries ? " ($newEnquiries)" : ''), 'calendar-tick', route('backend.enquiries.index'), request()->routeIs('backend.enquiries.*'), null];
  }

  if ($canViewPeople) {
    $mainItems[] = [__('Contacts'), 'people', route('backend.people.index'), request()->routeIs('backend.people.*'), [
      [__('Everyone'), route('backend.people.index'), request()->routeIs('backend.people.*') && ! $personType],
      [__('Owners'), route('backend.people.index', ['type' => 'owner']), $personType === 'owner'],
      [__('Customers'), route('backend.people.index', ['type' => 'customer']), $personType === 'customer'],
      [__('Outside agents'), route('backend.people.index', ['type' => 'agent']), $personType === 'agent'],
    ]];
  }

  if ($canViewActivity) {
    $mainItems[] = [__('Activity log'), 'time', route('backend.activity.index'), request()->routeIs('backend.activity.*'), null];
  }

  // Sidebar menu. A group = heading ('' for none) => items; an item = [label, keenicon, url, active, submenu?],
  // a submenu item = [label, url, active]. Add pages here.
  $menu = [
    '' => $mainItems,
  ];

  if ($canManageUsers) {
    $menu[__('Settings')] = [
      [
        __('Users'),
        'users',
        route('backend.users.index'),
        request()->routeIs('backend.users.*'),
        [
          [__('Active'), route('backend.users.index', ['status' => 'active']), request()->routeIs('backend.users.*') && ! $isArchivedUsers],
          [__('Archive'), route('backend.users.archived'), $isArchivedUsers],
        ],
      ],
      [__('Permissions'), 'shield-tick', route('backend.permissions.index'), request()->routeIs('backend.permissions.*'), null],
    ];
  }
@endphp

<div class="kt-sidebar dark bg-background border-e border-e-border fixed top-0 bottom-0 z-20 hidden lg:flex flex-col items-stretch shrink-0 [--kt-drawer-enable:true] lg:[--kt-drawer-enable:false]"
  data-kt-drawer="true" data-kt-drawer-class="kt-drawer kt-drawer-start top-0 bottom-0" id="sidebar">
  <div class="kt-sidebar-header relative hidden shrink-0 items-center justify-between px-3 lg:flex lg:px-6" id="sidebar_header">
    <a href="{{ route('backend.dashboard') }}" class="text-mono font-semibold flex items-center gap-2.5">
      <span class="brand-lime flex size-9 shrink-0 items-center justify-center rounded-full"><img src="{{ asset('assets/img/logo.png') }}" alt="{{ config('app.name') }}" class="size-5 object-contain"></span>
      <span class="default-logo text-lg">{{ config('app.name') }}</span>
    </a>
    <button
      class="kt-btn kt-btn-outline kt-btn-icon absolute start-full top-2/4 size-[30px] -translate-x-2/4 -translate-y-2/4 rtl:translate-x-2/4"
      data-kt-toggle="body" data-kt-toggle-class="kt-sidebar-collapse" id="sidebar_toggle">
      <i class="ki-filled ki-black-left-line kt-toggle-active:rotate-180 transition-all duration-300 rtl:rotate-180"></i>
    </button>
  </div>

  <div class="kt-sidebar-content flex shrink-0 grow py-5 pe-2" id="sidebar_content">
    <div class="kt-scrollable-y-hover flex shrink-0 grow pe-1 ps-2 lg:pe-3 lg:ps-5" data-kt-scrollable="true"
      data-kt-scrollable-dependencies="#sidebar_header" data-kt-scrollable-height="auto"
      data-kt-scrollable-offset="0px" data-kt-scrollable-wrappers="#sidebar_content" id="sidebar_scrollable">
      <div class="kt-menu flex grow flex-col gap-1" data-kt-menu="true" data-kt-menu-accordion-expand-all="false" id="sidebar_menu">
        @foreach ($menu as $heading => $items)
          @if ($heading)
            <div class="kt-menu-item pt-2.25 pb-px">
              <span class="kt-menu-heading pe-[10px] ps-[10px] text-xs font-medium uppercase text-muted-foreground">{{ $heading }}</span>
            </div>
          @endif

          @foreach ($items as [$label, $icon, $href, $active, $submenu])
            @if ($submenu)
              <div class="kt-menu-item {{ $active ? 'here show' : '' }}" x-data="{ open: @js($active) }" :class="{ 'here': open, 'show': open }">
                <div class="kt-menu-link flex grow cursor-pointer items-center gap-[10px] border border-transparent py-[6px] pe-[10px] ps-[10px]"
                  @click="open = !open" @keydown.enter.prevent="open = !open" @keydown.space.prevent="open = !open"
                  tabindex="0" role="button" :aria-expanded="open.toString()">
                  <span class="kt-menu-icon w-[20px] items-start text-muted-foreground">
                    <i class="ki-filled ki-{{ $icon }} text-lg"></i>
                  </span>
                  <span class="kt-menu-title kt-menu-item-active:text-primary kt-menu-link-hover:!text-primary text-sm font-medium text-foreground">
                    {{ $label }}
                  </span>
                  <span class="kt-menu-arrow me-[-10px] ms-1 w-[20px] shrink-0 justify-end text-muted-foreground">
                    {{-- > closed, v open --}}
                    <i class="ki-filled ki-right text-[11px] transition-transform duration-200" :class="open && 'rotate-90'"></i>
                  </span>
                </div>
                <div class="kt-menu-accordion relative gap-1 ps-[10px] before:absolute before:bottom-0 before:start-[20px] before:top-0 before:border-s before:border-border"
                  x-show="open" {{ $active ? '' : 'x-cloak' }}>
                  @foreach ($submenu as [$subLabel, $url, $current])
                    <div class="kt-menu-item {{ $current ? 'active' : '' }}">
                      <a class="kt-menu-link kt-menu-item-active:bg-accent/60 kt-menu-item-active:rounded-lg hover:bg-accent/60 grow items-center gap-[14px] border border-transparent py-[8px] pe-[10px] ps-[10px] hover:rounded-lg"
                        href="{{ $url }}" tabindex="0">
                        <span class="kt-menu-bullet kt-menu-item-active:before:bg-primary kt-menu-item-hover:before:bg-primary relative -start-[3px] flex w-[6px] before:absolute before:top-0 before:size-[6px] before:-translate-y-1/2 before:rounded-full rtl:start-0 rtl:before:translate-x-1/2"></span>
                        <span class="kt-menu-title text-2sm kt-menu-item-active:text-primary kt-menu-item-active:font-semibold kt-menu-link-hover:!text-primary font-normal text-foreground">
                          {{ $subLabel }}
                        </span>
                      </a>
                    </div>
                  @endforeach
                </div>
              </div>
            @else
              <div class="kt-menu-item {{ $active ? 'active' : '' }}">
                <a class="kt-menu-link kt-menu-item-active:bg-accent/60 kt-menu-item-active:rounded-lg hover:bg-accent/60 grow items-center gap-[10px] border border-transparent py-[6px] pe-[10px] ps-[10px] hover:rounded-lg"
                  href="{{ $href }}" tabindex="0">
                  <span class="kt-menu-icon w-[20px] items-start text-muted-foreground">
                    <i class="ki-filled ki-{{ $icon }} text-lg"></i>
                  </span>
                  <span class="kt-menu-title kt-menu-item-active:text-primary kt-menu-item-active:font-semibold kt-menu-link-hover:!text-primary text-sm font-medium text-foreground">
                    {{ $label }}
                  </span>
                </a>
              </div>
            @endif
          @endforeach
        @endforeach
      </div>
    </div>
  </div>
</div>
