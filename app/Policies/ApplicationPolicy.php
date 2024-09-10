<?php

// app/Policies/ApplicationPolicy.php
namespace App\Policies;

use App\Models\Application;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ApplicationPolicy
{
    use HandlesAuthorization;

    public function delete(User $user, Application $application)
    {
        return $user->id === $application->user_id || $user->hasRole('admin') || $user->hasRole('super_admin');
    }
}
