<?php

namespace App\Policies;

use App\Models\Program;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProgramPolicy
{
    use HandlesAuthorization;

    public function create(User $user) 
    {
        // If the user is an admin or super admin, allow unrestricted creation
        if ($user->hasRole('admin') || $user->hasRole('super_admin')) {
            return true;
        }
    
        // For users with 'user' role, check the program count
        if ($user->hasRole('user')) {
            $programCount = $user->programs()->count();
            return $programCount < 2;
        }
    
        // If the user doesn't have any of these roles, deny creation
        return false;
    }

    /**
     * Determine if the user can update the program.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Program  $program
     * @return bool
     */
    public function update(User $user, Program $program)
    {
        // Get the program's owner
        $owner = $program->user;

        // User can only update their own program
        if ($user->id === $owner->id && $user->hasRole('user')) {
            return true;
        }

        // Admin can update their own and users' programs
        if ($user->hasRole('admin') && ($user->id === $owner->id || $owner->hasRole('user'))) {
            return true;
        }

        // Super admin can update any program (admin's or user's)
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return false; // Default deny
    }

    /**
     * Determine if the user can delete the program.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Program  $program
     * @return bool
     */
    public function delete(User $user, Program $program)
    {
        // Get the program's owner
        $owner = $program->user;

        // User can only delete their own program
        if ($user->id === $owner->id && $user->hasRole('user')) {
            return true;
        }

        // Admin can delete their own and users' programs
        if ($user->hasRole('admin') && ($user->id === $owner->id || $owner->hasRole('user'))) {
            return true;
        }

        // Super admin can delete any program (admin's or user's)
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return false; 
    }
}
