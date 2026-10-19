<div class="kt-card kt-card-grid min-w-full">
  <div class="kt-card-header">
    <div class="flex flex-col gap-1">
      <h3 class="kt-card-title flex items-center gap-2">
        <i class="ki-filled ki-shield-tick text-lg text-primary"></i>
        @lang('permissions.title')
      </h3>
      <span class="text-sm text-muted-foreground">@lang('permissions.intro')</span>
    </div>
  </div>

  <div class="kt-card-table">
    <div class="kt-scrollable-x-auto">
      <table class="kt-table kt-table-border">
        <thead>
          <tr>
            <th class="min-w-[18rem]">@lang('permissions.permission')</th>
            @foreach ($roles as $role)
              <th class="w-32 text-center">
                <span class="kt-badge kt-badge-sm {{ $role->badgeClass() }}">{{ $role->label() }}</span>
              </th>
            @endforeach
          </tr>
        </thead>
        <tbody>
          @foreach ($modules as $module => $permissions)
            {{-- Module heading row --}}
            <tr class="bg-accent/40" wire:key="module-{{ $module }}">
              <td colspan="{{ count($roles) + 1 }}" class="text-sm font-semibold text-foreground">
                <span class="flex items-center gap-2">
                  <i class="ki-filled ki-{{ __("permissions.modules.$module.icon") }} text-base text-primary"></i>
                  @lang("permissions.modules.$module.label")
                </span>
              </td>
            </tr>

            @foreach ($permissions as $permission)
              <tr wire:key="permission-{{ $permission }}">
                <td>
                  <div class="flex flex-col">
                    <span class="text-sm font-medium text-foreground">@lang("permissions.names.$permission.label")</span>
                    <span class="text-xs text-muted-foreground">@lang("permissions.names.$permission.hint")</span>
                  </div>
                </td>
                @foreach ($roles as $role)
                  {{-- Admins hold everything: locked, so nobody can lock themselves out --}}
                  <td class="text-center">
                    @php $isAdmin = $role === \App\Enums\UserRole::Admin; @endphp
                    <input type="checkbox" class="kt-switch kt-switch-sm"
                      aria-label="{{ $role->label() }}: @lang("permissions.names.$permission.label")"
                      @checked($isAdmin || in_array($permission, $granted[$role->value] ?? [], true))
                      @if ($isAdmin) disabled title="@lang('permissions.admin_locked')"
                      @else wire:click="toggle('{{ $role->value }}', '{{ $permission }}')" wire:loading.attr="disabled" @endif>
                  </td>
                @endforeach
              </tr>
            @endforeach
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
