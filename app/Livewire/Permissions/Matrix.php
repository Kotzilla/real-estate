<?php

namespace App\Livewire\Permissions;

use App\Enums\UserRole;
use App\Models\User;
use App\Traits\LogActivity;
use Database\Seeders\PermissionSeeder;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Permissions Matrix Livewire Component
 *
 * Settings → Permissions: which role holds which permission, roles across, permissions grouped by module down
 * (settings, properties, people, contracts, activity). A switch gives or takes a permission from a role at once
 * (spatie/laravel-permission, cache reset by the package). Admins always hold every permission, so their column is
 * locked: nobody can lock themselves out. Opened with settings.manage (App\Policies\UserPolicy::viewAny).
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @package App\Livewire
 */
class Matrix extends Component
{
    use LogActivity;

    /**
     * Give a permission to a role, or take it away
     *
     * @param  string  $role
     * @param  string  $permission
     * @return void
     */
    public function toggle(string $role, string $permission): void
    {
        Gate::authorize('viewAny', User::class);

        if ($role === UserRole::Admin->value || ! in_array($role, UserRole::values(), true)) {
            return;
        }

        $model = Role::findByName($role);
        $grant = ! $model->hasPermissionTo($permission);
        $grant ? $model->givePermissionTo($permission) : $model->revokePermissionTo($permission);
        $this->log($model, $grant ? 'permission-grant' : 'permission-revoke', ['permission' => $permission]);

        $this->dispatch('toast', type: 'success', text: __($grant ? 'permissions.granted' : 'permissions.revoked', [
            'permission' => __("permissions.names.$permission.label"),
            'role' => UserRole::from($role)->label(),
        ]));
    }

    /**
     * The permissions grouped by module, in PermissionSeeder::DEFAULTS order (any others last)
     *
     * @return array<string, list<string>>
     */
    protected function modules(): array
    {
        $order = array_flip(array_keys(PermissionSeeder::DEFAULTS));
        $names = Permission::query()->where('guard_name', 'web')->pluck('name')
            ->sortBy(fn (string $name) => $order[$name] ?? PHP_INT_MAX);

        return $names->groupBy(fn (string $name) => strtok($name, '.'))->map->values()->map->all()->all();
    }

    /**
     * Render the Livewire component
     *
     * @return View
     */
    public function render(): View
    {
        Gate::authorize('viewAny', User::class);

        return view('livewire.permissions.matrix', [
            'modules' => $this->modules(),
            'roles' => UserRole::cases(),
            'granted' => Role::with('permissions')->get()->mapWithKeys(fn (Role $role) => [
                $role->name => $role->permissions->pluck('name')->all(),
            ])->all(),
        ]);
    }
}
