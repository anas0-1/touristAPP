<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Activity;
use Illuminate\Auth\Access\HandlesAuthorization;

class ActivityPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can update the activity.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Activity  $activity
     * @return bool
     */
    public function update(User $user, Activity $activity)
    {
        // Get the activity's owner
        $owner = $activity->program->user;

        // User can only update their own activity
        if ($user->id === $owner->id && $user->hasRole('user')) {
            return true;
        }

        // Admin can update their own and users' activities
        if ($user->hasRole('admin') && ($user->id === $owner->id || $owner->hasRole('user'))) {
            return true;
        }

        // Super admin can update any activity (admin's or user's)
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return false; // Default deny
    }

    /**
     * Determine if the user can delete the activity.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Activity  $activity
     * @return bool
     */
    public function delete(User $user, Activity $activity)
    {
        // Get the activity's owner
        $owner = $activity->program->user;

        // User can only delete their own activity
        if ($user->id === $owner->id && $user->hasRole('user')) {
            return true;
        }

        // Admin can delete their own and users' activities
        if ($user->hasRole('admin') && ($user->id === $owner->id || $owner->hasRole('user'))) {
            return true;
        }

        // Super admin can delete any activity (admin's or user's)
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return false; 
    }
}
