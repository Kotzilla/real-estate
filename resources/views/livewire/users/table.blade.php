<div>
  <div class="kt-card kt-card-grid min-w-full">
    <div class="kt-card-header flex-wrap gap-3">
      <div class="flex items-center gap-3 flex-wrap">
        <h3 class="kt-card-title flex items-center gap-2">
          <i class="ki-filled ki-users text-lg text-primary"></i>
          @lang('users.title')
        </h3>

        {{-- Tab Switcher (Active vs Archive) --}}
        <div class="flex items-center gap-1.5 ms-2 p-1 rounded-lg bg-accent/50 border border-border">
          <button type="button" wire:click="setStatus('active')"
            class="kt-btn kt-btn-sm {{ $status === 'active' ? 'kt-btn-primary shadow-xs' : 'kt-btn-ghost hover:bg-background/80' }} gap-1.5">
            <i class="ki-filled ki-check-circle"></i>
            @lang('users.tabs.active')
            <span class="kt-badge kt-badge-sm {{ $status === 'active' ? 'bg-white text-primary font-bold shadow-xs' : 'kt-badge-secondary' }}">
              {{ $activeCount }}
            </span>
          </button>
          <button type="button" wire:click="setStatus('archived')"
            class="kt-btn kt-btn-sm {{ $status === 'archived' ? 'kt-btn-primary shadow-xs' : 'kt-btn-ghost hover:bg-background/80' }} gap-1.5">
            <i class="ki-filled ki-archive"></i>
            @lang('users.tabs.archived')
            <span class="kt-badge kt-badge-sm {{ $status === 'archived' ? 'bg-white text-primary font-bold shadow-xs' : 'kt-badge-secondary' }}">
              {{ $archivedCount }}
            </span>
          </button>
        </div>
      </div>

      <div class="kt-card-toolbar flex flex-wrap items-center gap-2.5">
        {{-- Role Filter (spatie/permission holds the role, so the filter goes through the relation) --}}
        <div wire:ignore x-data="ktSelect('roleFilter', { live: true })"
          x-effect="if ($refs.select && $refs.select.value !== $wire.roleFilter) { $refs.select.value = $wire.roleFilter; instance?.update?.(); }"
          class="w-44">
          <select x-ref="select" class="kt-select w-full" aria-label="@lang('users.table.filter_role')">
            <option value="all">@lang('users.table.all_roles')</option>
            @foreach (\App\Enums\UserRole::cases() as $case)
              <option value="{{ $case->value }}" @selected($roleFilter === $case->value)>
                {{ $case->label() }}
              </option>
            @endforeach
          </select>
        </div>

        {{-- Search Box --}}
        <x-search-box wire:model.live.debounce.250ms="q" :value="$q" :placeholder="__('users.table.search_placeholder')" class="w-64 max-w-full" />
        <x-reset-filters :show="$this->isFiltered()" />

        {{-- Invite Button --}}
        @if ($status === 'active')
          <button type="button" class="kt-btn kt-btn-sm kt-btn-primary gap-1.5 shrink-0" wire:click="openInviteModal">
            <i class="ki-filled ki-plus"></i>
            <span>@lang('users.table.invite_user')</span>
          </button>
        @endif
      </div>
    </div>

    {{-- Users Table --}}
    <div class="kt-card-table">
      <div class="kt-scrollable-x-auto">
        <table class="kt-table kt-table-border" data-kt-datatable-table="true">
          <thead>
            <tr>
              <th>@lang('users.table.user')</th>
              <th>@lang('users.table.role')</th>
              <th>@lang('users.table.phone')</th>
              <th>@lang('users.table.last_seen')</th>
              <th>@lang('users.table.last_login')</th>
              <th>
                @if ($status === 'archived')
                  @lang('users.table.archived_at')
                @else
                  @lang('users.table.joined')
                @endif
              </th>
              <th class="text-end">@lang('users.table.actions')</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($users as $u)
              <tr wire:key="user-row-{{ $u->id }}">
                {{-- User --}}
                <td>
                  <div class="flex items-center gap-3">
                    <div class="relative flex size-9 shrink-0 items-center justify-center rounded-full border border-primary/20 bg-primary/10 text-sm font-semibold text-primary">
                      {{ $u->initials() }}
                      <x-online-dot :seen="$lastSeen[$u->id] ?? null" />
                    </div>
                    <div class="flex min-w-0 flex-col">
                      <span class="truncate font-medium text-foreground">
                        {{ $u->name }}
                        @if ($u->id === auth()->id())
                          <span class="text-2xs ms-1 rounded-full bg-primary px-1.5 py-0.5 text-primary-foreground">@lang('common.you')</span>
                        @endif
                      </span>
                      <span class="truncate text-sm text-muted-foreground">{{ $u->signInAs() ?: '—' }}</span>
                    </div>
                  </div>
                </td>

                {{-- Role Badge --}}
                <td>
                  @php($role = \App\Enums\UserRole::tryFrom($u->getRoleNames()->first() ?? ''))
                  @if ($role)
                    <span class="kt-badge kt-badge-sm {{ $role->badgeClass() }}">{{ $role->label() }}</span>
                  @else
                    <span class="text-sm text-muted-foreground">—</span>
                  @endif
                </td>

                {{-- Phone: shown on listings and contracts the agent handles --}}
                <td class="whitespace-nowrap text-sm">{{ $u->phone ?: '—' }}</td>

                {{-- Last seen: the newest session's last request (an open tab counts, it re-renders every minute) --}}
                <td class="text-sm"><x-last-seen :seen="$lastSeen[$u->id] ?? null" /></td>

                {{-- Last sign-in --}}
                <td class="text-sm text-muted-foreground">
                  @if ($u->last_login_at)
                    <span title="{{ $u->last_login_at->diffForHumans() }}">{{ $u->last_login_at->translatedFormat('d M Y H:i') }}</span>
                  @else
                    —
                  @endif
                </td>

                {{-- Joined / archived on --}}
                <td class="text-sm text-muted-foreground">
                  @if ($status === 'archived' && $u->deleted_at)
                    {{ $u->deleted_at->translatedFormat('d M Y') }}
                  @else
                    {{ $u->created_at->translatedFormat('d M Y') }}
                  @endif
                </td>

                {{-- Actions --}}
                <td class="text-end">
                  <div class="flex items-center justify-end gap-1">
                    @if ($status === 'active')
                      {{-- Edit --}}
                      <button type="button" wire:click="openEditUser({{ $u->id }})"
                        class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost text-secondary-foreground hover:text-primary"
                        title="@lang('users.table.edit_user')">
                        <i class="ki-filled ki-pencil text-base"></i>
                      </button>

                      {{-- Archive (soft-delete: old receipts still name their cashier) --}}
                      @if ($u->id === auth()->id())
                        <button type="button" disabled
                          class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost cursor-not-allowed text-secondary-foreground opacity-40"
                          title="@lang('users.table.cannot_archive_self')">
                          <i class="ki-filled ki-archive text-base"></i>
                        </button>
                      @else
                        <button type="button" wire:click="archiveUser({{ $u->id }})"
                          wire:confirm="@lang('users.table.archive_user') {{ $u->name }}?"
                          class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost text-secondary-foreground hover:bg-danger/10 hover:text-destructive"
                          title="@lang('users.table.archive_user')">
                          <i class="ki-filled ki-archive text-base"></i>
                        </button>
                      @endif
                    @else
                      {{-- Restore --}}
                      <button type="button" wire:click="restoreUser({{ $u->id }})"
                        class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost text-secondary-foreground hover:bg-success/10 hover:text-success"
                        title="@lang('users.table.restore_user')">
                        <i class="ki-filled ki-arrows-circle text-base"></i>
                      </button>
                    @endif
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="py-10 text-center text-sm text-muted-foreground">
                  <div class="flex flex-col items-center justify-center gap-2">
                    <i class="ki-filled {{ $status === 'archived' ? 'ki-archive' : 'ki-users' }} text-3xl text-muted-foreground/40"></i>
                    <span>
                      @if ($status === 'archived')
                        @lang('users.table.no_archived_users')
                      @else
                        @lang('users.table.no_active_users')
                      @endif
                    </span>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Pagination Footer --}}
      @if ($users->hasPages())
        <div class="kt-card-footer flex items-center justify-between border-t border-border p-4">
          <div class="text-sm text-muted-foreground">
            @lang('common.showing', ['from' => $users->firstItem(), 'to' => $users->lastItem(), 'total' => $users->total()])
          </div>
          <div>
            {{ $users->links() }}
          </div>
        </div>
      @endif
    </div>
  </div>

  {{-- Edit User Modal --}}
  @if ($showEditModal)
    <div x-data @keydown.escape.window="$wire.closeEditModal()">
      {{-- Backdrop --}}
      <div class="kt-modal-backdrop fixed inset-0 z-50 bg-black/40 backdrop-blur-xs" wire:click="closeEditModal"></div>

      {{-- Modal Dialog --}}
      <div class="pointer-events-none fixed inset-0 z-50 flex items-center justify-center p-4 kt-scrollable-y-auto">
        <div class="kt-modal-content pointer-events-auto w-full max-w-[480px] overflow-hidden rounded-xl border border-border bg-white text-foreground shadow-2xl dark:bg-background">
          {{-- Header --}}
          <div class="kt-modal-header flex shrink-0 items-center justify-between border-b border-border px-5 py-4">
            <h3 class="kt-modal-title flex items-center gap-2 text-base font-semibold text-mono">
              <i class="ki-filled ki-pencil text-lg text-primary"></i>
              <span>@lang('users.edit.title')</span>
            </h3>
            <button type="button" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" wire:click="closeEditModal">
              <i class="ki-filled ki-cross"></i>
            </button>
          </div>

          {{-- Body / Form --}}
          <form wire:submit="saveUser" class="flex min-h-0 flex-col">
            <div class="kt-modal-body space-y-4 p-5">
              <div>
                <label class="mb-1 block text-sm font-semibold text-foreground required">@lang('users.form.name_label')</label>
                <input type="text" wire:model="editForm.name" class="kt-input w-full" @error('editForm.name') aria-invalid="true" @enderror>
                @error('editForm.name') <span class="mt-1 block text-sm text-destructive">{{ $message }}</span> @enderror
              </div>

              <div>
                <label class="mb-1 block text-sm font-semibold text-foreground required">@lang('users.form.username_label')</label>
                <input type="text" wire:model="editForm.username" class="kt-input w-full" autocomplete="off" @error('editForm.username') aria-invalid="true" @enderror>
                <span class="mt-1 block text-2xs text-muted-foreground">@lang('users.form.username_hint')</span>
                @error('editForm.username') <span class="mt-1 block text-sm text-destructive">{{ $message }}</span> @enderror
              </div>

              <div>
                <label class="mb-1 block text-sm font-semibold text-foreground required">@lang('users.form.email_label')</label>
                <input type="email" wire:model="editForm.email" class="kt-input w-full" @error('editForm.email') aria-invalid="true" @enderror>
                @error('editForm.email') <span class="mt-1 block text-sm text-destructive">{{ $message }}</span> @enderror
              </div>

              <div>
                <label class="mb-1 block text-sm font-semibold text-foreground required">@lang('users.form.role_label')</label>
                <div wire:ignore x-data="ktSelect('editForm.role', { live: true })">
                  <select x-ref="select" class="kt-select w-full">
                    @foreach (\App\Enums\UserRole::cases() as $roleCase)
                      <option value="{{ $roleCase->value }}" @selected($editForm->role === $roleCase->value)>{{ $roleCase->label() }}</option>
                    @endforeach
                  </select>
                </div>
                @error('editForm.role') <span class="mt-1 block text-sm text-destructive">{{ $message }}</span> @enderror
              </div>

              <div>
                <label class="mb-1 block text-sm font-semibold text-foreground">@lang('users.form.phone_label')</label>
                <input type="tel" wire:model="editForm.phone" class="kt-input w-full" inputmode="tel" maxlength="20" @error('editForm.phone') aria-invalid="true" @enderror>
                @error('editForm.phone') <span class="mt-1 block text-sm text-destructive">{{ $message }}</span> @enderror
              </div>
            </div>

            {{-- Footer --}}
            <div class="kt-modal-footer flex shrink-0 items-center justify-end gap-2.5 border-t border-border bg-accent/20 px-5 py-3.5">
              <button type="button" class="kt-btn kt-btn-sm kt-btn-outline" wire:click="closeEditModal">
                @lang('users.form.cancel')
              </button>
              <button type="submit" class="kt-btn kt-btn-sm kt-btn-primary">
                @lang('users.edit.save')
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @endif

  {{-- Invite User Modal --}}
  @if ($showInviteModal)
    <div x-data @keydown.escape.window="$wire.closeInviteModal()">
      {{-- Backdrop --}}
      <div class="kt-modal-backdrop fixed inset-0 z-50 bg-black/40 backdrop-blur-xs" wire:click="closeInviteModal"></div>

      {{-- Modal Dialog --}}
      <div class="pointer-events-none fixed inset-0 z-50 flex items-center justify-center p-4 kt-scrollable-y-auto">
        <div class="kt-modal-content pointer-events-auto w-full max-w-[480px] overflow-hidden rounded-xl border border-border bg-white text-foreground shadow-2xl dark:bg-background">
          {{-- Header --}}
          <div class="kt-modal-header flex shrink-0 items-center justify-between border-b border-border px-5 py-4">
            <h3 class="kt-modal-title flex items-center gap-2 text-base font-semibold text-mono">
              <i class="ki-filled ki-plus-circle text-lg text-primary"></i>
              <span>@lang('users.invite.title')</span>
            </h3>
            <button type="button" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" wire:click="closeInviteModal">
              <i class="ki-filled ki-cross"></i>
            </button>
          </div>

          {{-- Body / Form --}}
          <form wire:submit="sendInvite" class="flex min-h-0 flex-col">
            <div class="kt-modal-body space-y-4 p-5">
              <p class="text-sm text-muted-foreground">@lang('users.invite.intro')</p>

              <div>
                <label class="mb-1 block text-sm font-semibold text-foreground required">@lang('users.form.name_label')</label>
                <input type="text" wire:model="inviteForm.name" class="kt-input w-full" placeholder="@lang('users.form.name_placeholder')" @error('inviteForm.name') aria-invalid="true" @enderror>
                @error('inviteForm.name') <span class="mt-1 block text-sm text-destructive">{{ $message }}</span> @enderror
              </div>

              <div>
                <label class="mb-1 block text-sm font-semibold text-foreground required">@lang('users.form.username_label')</label>
                <input type="text" wire:model="inviteForm.username" class="kt-input w-full" autocomplete="off" @error('inviteForm.username') aria-invalid="true" @enderror>
                <span class="mt-1 block text-2xs text-muted-foreground">@lang('users.form.username_hint')</span>
                @error('inviteForm.username') <span class="mt-1 block text-sm text-destructive">{{ $message }}</span> @enderror
              </div>

              <div>
                <label class="mb-1 block text-sm font-semibold text-foreground required">@lang('users.form.email_label')</label>
                <input type="email" wire:model="inviteForm.email" class="kt-input w-full" @error('inviteForm.email') aria-invalid="true" @enderror>
                <span class="mt-1 block text-2xs text-muted-foreground">@lang('users.form.email_hint')</span>
                @error('inviteForm.email') <span class="mt-1 block text-sm text-destructive">{{ $message }}</span> @enderror
              </div>

              <div>
                <label class="mb-1 block text-sm font-semibold text-foreground required">@lang('users.form.role_label')</label>
                <div wire:ignore x-data="ktSelect('inviteForm.role', { live: true })">
                  <select x-ref="select" class="kt-select w-full">
                    @foreach (\App\Enums\UserRole::cases() as $roleCase)
                      <option value="{{ $roleCase->value }}" @selected($inviteForm->role === $roleCase->value)>{{ $roleCase->label() }}</option>
                    @endforeach
                  </select>
                </div>
                @error('inviteForm.role') <span class="mt-1 block text-sm text-destructive">{{ $message }}</span> @enderror
              </div>

              <div>
                <label class="mb-1 block text-sm font-semibold text-foreground">@lang('users.form.phone_label')</label>
                <input type="tel" wire:model="inviteForm.phone" class="kt-input w-full" inputmode="tel" maxlength="20" @error('inviteForm.phone') aria-invalid="true" @enderror>
                @error('inviteForm.phone') <span class="mt-1 block text-sm text-destructive">{{ $message }}</span> @enderror
              </div>
            </div>

            {{-- Footer --}}
            <div class="kt-modal-footer flex shrink-0 items-center justify-end gap-2.5 border-t border-border bg-accent/20 px-5 py-3.5">
              <button type="button" class="kt-btn kt-btn-sm kt-btn-outline" wire:click="closeInviteModal">
                @lang('users.form.cancel')
              </button>
              <button type="submit" class="kt-btn kt-btn-sm kt-btn-primary">
                @lang('users.invite.send')
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @endif
</div>
