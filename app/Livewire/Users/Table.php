<?php

namespace App\Livewire\Users;

use App\Enums\UserRole;
use App\Models\User;
use App\Traits\LogActivity;
use App\Traits\ResetsFilters;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Users Table Livewire Component
 *
 * The back office's staff list: every account that can sign in here, with their role, phone and last sign-in.
 * Archived accounts are soft-deleted (old receipts still name their cashier), so they stay on the same table behind a
 * tab. Gated on App\Policies\UserPolicy (settings.manage) on every entry point, not only the route.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @package App\Livewire
 */
class Table extends Component
{
    use LogActivity, ResetsFilters, WithPagination;

    // search, filters and sort cleared by the Reset button (ResetsFilters)
    protected const FILTERS = ['q', 'status', 'roleFilter'];

    public string $status = 'active';

    public string $q = '';

    public string $roleFilter = 'all';

    public bool $showEditModal = false;

    public EditUserForm $editForm;

    public bool $showInviteModal = false;

    public InviteUserForm $inviteForm;

    /**
     * URL query string parameters
     *
     * @var array<string, array<string, string>>
     */
    protected $queryString = [
        'status' => ['except' => 'active'],
        'q' => ['except' => ''],
        'roleFilter' => ['except' => 'all'],
    ];

    /**
     * Mount the component
     *
     * @param  string  $status
     * @return void
     */
    public function mount(string $status = 'active'): void
    {
        $this->status = in_array($status, ['active', 'archived'], true) ? $status : 'active';
    }

    /**
     * Switch between Active and Archived accounts
     *
     * @param  string  $status
     * @return void
     */
    public function setStatus(string $status): void
    {
        if (in_array($status, ['active', 'archived'], true)) {
            $this->status = $status;
            $this->resetPage();
        }
    }

    /**
     * Update the search query and reset pagination
     *
     * @return void
     */
    public function updatedQ(): void
    {
        $this->resetPage();
    }

    /**
     * Update the role filter and reset pagination
     *
     * @return void
     */
    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Archive (soft-delete) an account
     *
     * @param  int  $userId
     * @return void
     */
    public function archiveUser(int $userId): void
    {
        $this->authorize('viewAny', User::class);

        if ($userId === (int) Auth::id()) {
            $this->dispatch('toast', type: 'error', text: __('users.table.cannot_archive_self'));

            return;
        }

        $user = User::findOrFail($userId);

        if ($this->isTheLastAdmin($user)) {
            $this->dispatch('toast', type: 'error', text: __('users.table.cannot_archive_last_admin'));

            return;
        }

        $user->delete();
        $this->log($user, 'user-archive');
        $this->dispatch('toast', type: 'success', text: __('users.table.archived_success'));
    }

    /**
     * Restore an archived (soft-deleted) account
     *
     * @param  int  $userId
     * @return void
     */
    public function restoreUser(int $userId): void
    {
        $this->authorize('viewAny', User::class);

        $user = User::onlyTrashed()->findOrFail($userId);
        $user->restore();
        $this->log($user, 'user-restore');
        $this->dispatch('toast', type: 'success', text: __('users.table.restored_success'));
    }

    /**
     * Open the edit account modal
     *
     * @param  int  $userId
     * @return void
     */
    public function openEditUser(int $userId): void
    {
        $this->authorize('viewAny', User::class);

        $this->editForm->load(User::findOrFail($userId));
        $this->showEditModal = true;
    }

    /**
     * Close the edit account modal
     *
     * @return void
     */
    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->resetValidation();
    }

    /**
     * Save the account's details
     *
     * @return void
     */
    public function saveUser(): void
    {
        $this->authorize('viewAny', User::class);

        $this->editForm->validate();

        $user = User::findOrFail($this->editForm->userId);

        if ($user->isAdmin() && $this->editForm->role !== UserRole::Admin->value && $this->isTheLastAdmin($user)) {
            $this->addError('editForm.role', __('users.edit.cannot_demote_last_admin'));

            return;
        }

        $user->update([
            'name' => $this->editForm->name,
            'username' => $this->editForm->username,
            'email' => $this->editForm->email,
            'phone' => $this->editForm->phone !== '' ? $this->editForm->phone : null,
        ]);
        $user->syncRoles([$this->editForm->role]);
        $this->log($user, 'user-update', ['role' => $this->editForm->role]);

        $this->showEditModal = false;
        $this->dispatch('toast', type: 'success', text: __('users.table.updated_success'));
    }

    /**
     * Open the invite modal
     *
     * @return void
     */
    public function openInviteModal(): void
    {
        $this->authorize('viewAny', User::class);

        $this->inviteForm->clear();
        $this->showInviteModal = true;
    }

    /**
     * Close the invite modal
     *
     * @return void
     */
    public function closeInviteModal(): void
    {
        $this->showInviteModal = false;
        $this->resetValidation();
    }

    /**
     * Create the account and email the set-password link
     *
     * @return void
     */
    public function sendInvite(): void
    {
        $this->authorize('viewAny', User::class);

        $this->inviteForm->validate();

        // A random password nobody knows: the account opens with the emailed link only.
        $user = User::create([
            'name' => $this->inviteForm->name,
            'username' => $this->inviteForm->username,
            'email' => $this->inviteForm->email,
            'password' => Str::random(40),
            'phone' => $this->inviteForm->phone !== '' ? $this->inviteForm->phone : null,
        ]);
        $user->syncRoles([$this->inviteForm->role]);
        $this->log($user, 'user-invite', ['role' => $this->inviteForm->role]);

        $status = Password::sendResetLink(['email' => $user->email]);

        $this->showInviteModal = false;

        $this->dispatch('toast',
            type: $status === Password::RESET_LINK_SENT ? 'success' : 'warning',
            text: $status === Password::RESET_LINK_SENT ? __('users.table.invited_success') : __('users.table.invite_email_failed', ['reason' => __($status)]),
        );
    }

    /**
     * The accounts on the current tab, matching the search and role filter
     *
     * @return LengthAwarePaginator<int, User>
     */
    protected function getUsers(): LengthAwarePaginator
    {
        $query = $this->status === 'archived' ? User::onlyTrashed() : User::withoutTrashed();

        return $query->when($this->q !== '', function (Builder $b) {
                $term = '%'.$this->q.'%';
                $b->where(function (Builder $sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('username', 'like', $term);
                });
            })
            ->when($this->roleFilter !== 'all', fn (Builder $b) => $b->whereHas('roles', fn (Builder $r) => $r->where('name', $this->roleFilter)))
            ->orderBy('name')
            ->paginate(15);
    }

    /**
     * Whether the user is an admin and the only one left (the company would lock everyone out)
     *
     * @param  User  $user
     * @return bool
     */
    protected function isTheLastAdmin(User $user): bool
    {
        return $user->isAdmin() && User::role(UserRole::Admin->value)->count() <= 1;
    }

    /**
     * Render the Livewire component
     *
     * @return View
     */
    public function render(): View
    {
        Gate::authorize('viewAny', User::class);

        $users = $this->getUsers();

        return view('livewire.users.table', [
            'users' => $users,
            'lastSeen' => User::lastSeen($users->pluck('id')->all()),
            'activeCount' => User::withoutTrashed()->count(),
            'archivedCount' => User::onlyTrashed()->count(),
        ]);
    }

    /**
     * Pagination view used by this table
     *
     * @return string
     */
    public function paginationView(): string
    {
        return 'vendor.pagination.kt-livewire';
    }
}
