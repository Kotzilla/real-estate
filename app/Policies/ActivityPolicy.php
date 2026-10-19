<?php

namespace App\Policies;

use App\Models\User;

/**
 * Activity Policy
 *
 * The activity log (spatie/laravel-activitylog's Activity model, registered in AppServiceProvider) shows every user's
 * sign-ins and changes with IP addresses, so it hangs off one permission: activity.view (admins by default).
 *
 * @package App\Policies
 */
class ActivityPolicy
{
    /**
     * Whether the user may open the activity log
     *
     * @param  User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('activity.view');
    }
}
