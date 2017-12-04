<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;

use App\User;
use App\Task;

use Illuminate\Support\Facades\Log;

class TaskPolicy extends BasePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can do anything with tasks
     *
     * @param  \App\User  $user
     * @param  string  $ability
     * @return mixed
     */
    public function before($user, $ability)
    {
        if($user->isSuperAdmin()) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the task.
     *
     * @param  \App\User  $user
     * @param  \App\Task  $task
     * @return mixed
     */
    public function view(User $user, Task $task)
    {
        return $this->hierarchicallyAllowed($user, $task->user);
    }

    /**
     * Determine whether the user can create tasks.
     *
     * @param  \App\User  $user
     * @param  string $task_user_id
     * @return mixed
     */
    public function store(User $user, $task_user_id)
    {
        $task_user_id = $task_user_id;

        // Do not allow to create a task without user
        if(!$task_user_id) {
            return false;
        }

        // Anyone can add a task to itself
        if($user->id === $task_user_id) {
            return true;
        }

        // Allow if current user has greater permission level than the task owner
        $taskUser = User::find($task_user_id);
        if($taskUser instanceof User && $this->hierarchicallyAllowed($user, $taskUser)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the task.
     *
     * @param  \App\User  $user
     * @param  \App\Task  $task
     * @param  string  $new_user_id
     * @return mixed
     */
    public function update(User $user, Task $task, $new_user_id)
    {
        // Anyone can update its tasks
        if($user->id === $task->user->id) {
            // But only Admins can change Task User
            if(!empty($new_user_id) && $user->id != $new_user_id && !$user->isAdmin()) {
                return false;
            }

            return true;
        }

        // Allow if current user has greater permission level than the task owner
        if($this->hierarchicallyAllowed($user, $task->user)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the task.
     *
     * @param  \App\User  $user
     * @param  \App\Task  $task
     * @return mixed
     */
    public function delete(User $user, Task $task)
    {
        // Anyone can delete its tasks
        if($user->id === $task->user->id) {
            return true;
        }

        // Allow if current user has greater permission level than the task owner
        if($this->hierarchicallyAllowed($user, $task->user)){
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can list requested tasks.
     *
     * @param  \App\User  $user
     * @param  \App\Task  $params
     * @return mixed
     */
    public function list(User $user, Array $params)
    {
        // If a user was specified check permissions,
        // else it will be defaulted to itself at the controller
        if(isset($params['user_id'])
            && $params['user_id'] !== '') {

            $userToAccess = User::find($params['user_id']);

            if($userToAccess instanceof User && $this->hierarchicallyAllowed($user, $userToAccess)) {
                return true;
            }

            return false;
        }

        return true;
    }
}