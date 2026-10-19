<?php

namespace App\Policies;

use App\Models\Person;
use App\Models\User;

/**
 * Person Policy
 *
 * Owners, customers and outside agents: people.view to see them, people.manage to add and edit them.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @package App\Policies
 */
class PersonPolicy
{
    /**
     * Whether the user may open the list
     *
     * @param  User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('people.view');
    }

    /**
     * Whether the user may open one
     *
     * @param  User  $user
     * @param  Person  $person
     * @return bool
     */
    public function view(User $user, Person $person): bool
    {
        return $user->can('people.view');
    }

    /**
     * Whether the user may add one
     *
     * @param  User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('people.manage');
    }

    /**
     * Whether the user may edit one
     *
     * @param  User  $user
     * @param  Person  $person
     * @return bool
     */
    public function update(User $user, Person $person): bool
    {
        return $user->can('people.manage');
    }

    /**
     * Whether the user may delete one
     *
     * @param  User  $user
     * @param  Person  $person
     * @return bool
     */
    public function delete(User $user, Person $person): bool
    {
        return $user->can('people.manage');
    }
}
