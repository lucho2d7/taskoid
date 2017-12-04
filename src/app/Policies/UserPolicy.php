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
        // SuperAdmin can do anything but delete itself or add another superadmin
        if($user->isSuperAdmin() && !in_array($ability, ['delete', 'store', 'update'])) {
            return true;
        }

        // Regular users cannot operate on Users
        if($user->isUser()) {
            return false;
        }
    }

    /**
     * Determine whether the user can view the user.
     *
     * @param  \App\User  $user
     * @param  \App\User  $userToAccess
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
        // Avoid creation of superadmin, we only want one
        if($role == User::ROLE_SUPERADMIN) {
            return false;
        }

        if($user->isSuperAdmin()) {
            return true;
        }

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
        // Avoid promotion to superadmin, we only want one
        if($newrole == User::ROLE_SUPERADMIN) {
            return false;
        }

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
     * @param  \App\User  $userToAccess
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
