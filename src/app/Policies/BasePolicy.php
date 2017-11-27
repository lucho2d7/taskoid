<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BasePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the logged user has greater privileges than the user to be accessed
     *
     * @param  \App\User  $user
     * @param  \App\User  $userToAccess
     * @return mixed
     */
    protected function userHasGreaterPrivileges($user, $userToAccess)
    {
        if($user->isSuperAdmin() && !$userToAccess->isSuperAdmin()) {
            return true;
        }

        if($user->isAdmin()) {
            return $userToAccess->isUser();
        }

        return false;
    }

    /**
     * Determine whether the logged user has hierarchical permission to access the user
     * either by having greater privilleges or being itself.
     *
     * @param  \App\User  $user
     * @param  \App\User  $userToAccess
     * @return mixed
     */
    protected function hierarchicallyAllowed($user, $userToAccess)
    {
        if($this->userHasGreaterPrivileges($user, $userToAccess)) {
            return true;
        }

        if($user->id === $userToAccess->id) {
            return true;
        }

        return false;
    }
}