<?php

namespace App\Policies;

use App\Models\User;

/**
 * User Policy
 *
 * Staff accounts are head-office data (unlike stock and orders, which carry branch_id), so the whole Users page hangs
 * off one permission: settings.manage. Shop staff and managers never get it — an admin grants it deliberately.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @package App\Policies
 */
class UserPolicy
{
    /**
     * Whether the user may open the Users page (list, invite, archive)
     *
     * @param  User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('settings.manage');
    }

    /**
     * Whether the user may change someone's name, sign-in details, role or branch
     *
     * @param  User  $user
     * @param  User  $model
     * @return bool
     */
    public function update(User $user, User $model): bool
    {
        return $user->can('settings.manage');
    }
}
