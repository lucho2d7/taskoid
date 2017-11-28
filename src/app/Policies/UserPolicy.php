<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;

use App\User;

class UserPolicy extends BasePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can do anything.
     *
     * @param  \App\User  $user
     * @param  string  $ability
     * @return mixed
     */
    public function before($user, $ability)
    {
        // SuperAdmin can do anything but delete itself
        if($user->isSuperAdmin() && $ability != 'delete') {
            return true;
        }
    }

    /**
     * Determine whether the user can view the user.
     *
     * @param  \App\User  $user
     * @param  \App\User  $userToView
     * @return mixed
     */
    public function view(User $user, User $userToAccess)
    {
        return $this->hierarchicallyAllowed($user, $userToAccess);
    }

    /**
     * Determine whether the user can create users.
     *
     * @param  \App\User  $user
     * @param  string $role
     * @return mixed
     */
    public function store(User $user, $role)
    {
        if(($user->isAdmin())
            && in_array($role, $user->getLowerRoles())) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the user.
     *
     * @param  \App\User  $user
     * @param  \App\User  $user
     * @param  string $newrole
     * @return mixed
     */
    public function update(User $user, User $userToAccess, $newrole)
    {
        // Prevent privilege escalation
        // Only allow asignation of lower roles
        if($this->hierarchicallyAllowed($user, $userToAccess)
            && (empty($newrole) || in_array($newrole, $user->getLowerRoles()))) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param  \App\User  $user
     * @param  \App\User  $user
     * @return mixed
     */
    public function delete(User $user, User $userToAccess)
    {
        // A user cannot delete itself
        if($user->id == $userToAccess->id) {
            return false;
        }

        // Allow deletion of lower permission level users
        if($this->hierarchicallyAllowed($user, $userToAccess)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user list requested users.
     *
     * @param  \App\User  $user
     * @param  array  $params
     * @return mixed
     */
    public function list(User $user, Array $params)
    {
        if($user->isUser()) {
            return false;
        }

        if(isset($params['role']) && !empty($params['role'])) {

            if(!User::isValidRole($params['role'])) {
                return false;
            }

            if($user->isAdmin() && !in_array($params['role'], $user->getLowerRoles())) {
                return false;
            }
        }

        if($user->isAdmin()) {
            return true;
        }

        return false;
    }
}
