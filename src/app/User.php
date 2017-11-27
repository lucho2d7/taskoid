<?php

namespace App;

use Hash;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use App\Task;
use App\Mail\ResetUserPassword;
use App\Mail\SignUpVerification;

class User extends Authenticatable
{
    const ROLE_SUPERADMIN = 'superadmin';
    const ROLE_ADMIN = 'admin';
    const ROLE_USER = 'user';

    const STATUS_ENABLED = 'enabled';
    const STATUS_DISABLED = 'disabled';

    use Notifiable;
    use CanResetPassword;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'role', 'status', 'status_validation_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'created_at', 'updated_at', 'status_validation_token',
    ];

    /**
     * Send password reset email.
     *
     * @param  string  $role
     * @return boolean
     */
    public function sendPasswordResetNotification($token)
    {
        Mail::to($this->email)->send(new ResetUserPassword($token));
    }

    /**
     * Send signup verification email.
     *
     * @param  string  $role
     * @return boolean
     */
    public function sendSignupVerificationNotification($token)
    {
        Mail::to($this->email)->send(new SignUpVerification($token));
    }

    /**
     * Validates a role string.
     *
     * @param  string  $role
     * @return boolean
     */
    static public function isValidRole($role)
    {
        return in_array($role, [User::ROLE_SUPERADMIN,
                                User::ROLE_ADMIN,
                                User::ROLE_USER]);
    }

    /**
     * Validates a role string.
     *
     * @param  string  $status
     * @return boolean
     */
    static public function isValidStatus($status)
    {
        return in_array($status, [User::STATUS_DISABLED,
                                User::STATUS_ENABLED]);
    }

    /**
     * Automatically creates hash for the user password.
     *
     * @param  string  $value
     * @return void
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    /**
     * Is Super Admin
     *
     * @return boolean
     */
    public function isSuperAdmin()
    {
        return $this->role === User::ROLE_SUPERADMIN;
    }

    /**
     * Is Admin
     *
     * @return boolean
     */
    public function isAdmin()
    {
        return $this->role === User::ROLE_ADMIN;
    }

    /**
     * Is User
     *
     * @return boolean
     */
    public function isUser()
    {
        return $this->role === User::ROLE_USER;
    }

    /**
     * Get lower permission roles
     *
     * @return array
     */
    public function getLowerRoles()
    {
        $roles = [];

        if($this->isAdmin() || $this->isSuperAdmin()) {
            $roles[] = User::ROLE_USER;
        }

        if($this->isSuperAdmin()) {
            $roles[] = User::ROLE_ADMIN;
        }

        return $roles;
    }

    /**
     * Get user tasks
     *
     * @return collection
     */
    public function tasks()
    {
        return $this->hasMany('App\Task');
    }

    /**
     * User Id query scope
     *
     * @param  Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $user_id
     * @return Illuminate\Database\Eloquent\Builder
     */
    public function scopeUserId($query, $user_id)
    {
        if($user_id)
        {
            return $query->where('id', $user_id);
        }

        return $query;
    }

    /**
     * Partial name match query scope
     *
     * @param  Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $name
     * @return Illuminate\Database\Eloquent\Builder
     */
    public function scopeNamePartial($query, $name)
    {
        if($name)
        {
            return $query->where('name', 'like', '%'.$name.'%');
        }

        return $query;
    }

    /**
     * Partial email match query scope
     *
     * @param  Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $email
     * @return Illuminate\Database\Eloquent\Builder
     */
    public function scopeEmailPartial($query, $email)
    {
        if($email)
        {
            return $query->where('email', 'like', '%'.$email.'%');
        }

        return $query;
    }

    /**
     * Role query scope
     *
     * @param  Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $role
     * @return Illuminate\Database\Eloquent\Builder
     */
    public function scopeRole($query, Array $roles, User $user)
    {
        $roles = array_filter($roles);

        if(empty($roles)) {
            $roles = $user->getLowerRoles();
        }

        return $query->whereIn('role', $roles);
    }

    /**
     * Status query scope
     *
     * @param  Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $status
     * @return Illuminate\Database\Eloquent\Builder
     */
    public function scopeStatus($query, $status)
    {
        if($status)
        {
            return $query->where('status', $status);
        }

        return $query;
    }
}
