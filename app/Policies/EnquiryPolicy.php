<?php

namespace App\Policies;

use App\Models\Enquiry;
use App\Models\User;

/**
 * Enquiry Policy
 *
 * Viewing requests from the public site: enquiries.view to see them and their notes, enquiries.manage to change their
 * status and add notes.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @package App\Policies
 */
class EnquiryPolicy
{
    /**
     * Whether the user may open the list
     *
     * @param  User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('enquiries.view');
    }

    /**
     * Whether the user may open one, with its notes
     *
     * @param  User  $user
     * @param  Enquiry  $enquiry
     * @return bool
     */
    public function view(User $user, Enquiry $enquiry): bool
    {
        return $user->can('enquiries.view');
    }

    /**
     * Whether the user may change its status and add notes
     *
     * @param  User  $user
     * @param  Enquiry  $enquiry
     * @return bool
     */
    public function update(User $user, Enquiry $enquiry): bool
    {
        return $user->can('enquiries.manage');
    }
}
