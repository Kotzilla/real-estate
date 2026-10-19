<?php

namespace App\Policies;

use App\Models\Contract;
use App\Models\User;

/**
 * Contract Policy
 *
 * Leases and sales: contracts.view to see them, contracts.manage to add and edit them.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @package App\Policies
 */
class ContractPolicy
{
    /**
     * Whether the user may open the list
     *
     * @param  User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('contracts.view');
    }

    /**
     * Whether the user may open one
     *
     * @param  User  $user
     * @param  Contract  $contract
     * @return bool
     */
    public function view(User $user, Contract $contract): bool
    {
        return $user->can('contracts.view');
    }

    /**
     * Whether the user may add one
     *
     * @param  User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('contracts.manage');
    }

    /**
     * Whether the user may edit one
     *
     * @param  User  $user
     * @param  Contract  $contract
     * @return bool
     */
    public function update(User $user, Contract $contract): bool
    {
        return $user->can('contracts.manage');
    }

    /**
     * Whether the user may delete one
     *
     * @param  User  $user
     * @param  Contract  $contract
     * @return bool
     */
    public function delete(User $user, Contract $contract): bool
    {
        return $user->can('contracts.manage');
    }
}
