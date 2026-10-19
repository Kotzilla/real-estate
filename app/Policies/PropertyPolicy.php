<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;

/**
 * Property Policy
 *
 * Listings: properties.view to see them, properties.manage to add and edit them.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @package App\Policies
 */
class PropertyPolicy
{
    /**
     * Whether the user may open the list
     *
     * @param  User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('properties.view');
    }

    /**
     * Whether the user may open one
     *
     * @param  User  $user
     * @param  Property  $property
     * @return bool
     */
    public function view(User $user, Property $property): bool
    {
        return $user->can('properties.view');
    }

    /**
     * Whether the user may add one
     *
     * @param  User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('properties.manage');
    }

    /**
     * Whether the user may edit one
     *
     * @param  User  $user
     * @param  Property  $property
     * @return bool
     */
    public function update(User $user, Property $property): bool
    {
        return $user->can('properties.manage');
    }

    /**
     * Whether the user may delete one
     *
     * @param  User  $user
     * @param  Property  $property
     * @return bool
     */
    public function delete(User $user, Property $property): bool
    {
        return $user->can('properties.manage');
    }
}
