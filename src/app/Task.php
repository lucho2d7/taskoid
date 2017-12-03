<?php

namespace App;

use Moloquent;
use Carbon\Carbon;
use MongoDB\BSON\UTCDateTime;

class Task extends Moloquent
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'description', 'due_date', 'completed',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        //'created_at', 'updated_at',
        'user_role'
    ];

    /**
     * Date attributes for mongodb models
     *
     * @var array
     */
    protected $dates = [
        'created_at', 'updated_at', 'due_date',
    ];

    /**
     * Default values for attributes
     * @var  array an array with attribute as key and default as value
     */
    protected $attributes = [
        'completed' => false,
    ];

    /**
     * Casts for attributes
     * @var  array an array with attribute as key and type as value
     */
    protected $casts = [
        'completed' => 'boolean',
    ];

    /**
     * Set completed as boolean, cast for proper MongoDB storage as boolean
     *
     * @param  boolean  $completed
     */
    public function setCompletedAttribute($completed)
    {
        $this->attributes['completed'] = (boolean)$completed;
    }

    /**
     * Set user_id and also set user_role
     *
     * @param  boolean  $completed
     */
    public function setUserIdAttribute($user_id)
    {
        $this->attributes['user_id'] = $user_id;

        $owner = User::find($user_id);

        $this->attributes['user_role'] = $owner->role;
    }

    /**
     * Get user that owns this task
     *
     * @return App\User
     */
    public function user()
    {
        return $this->belongsTo('App\User');
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
        if($user_id) {
            return $query->where('user_id', $user_id);
        }

        return $query;
    }

    /**
     * Partial title match query scope
     *
     * @param  Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $title
     * @return Illuminate\Database\Eloquent\Builder
     */
    public function scopeTitlePartial($query, $title)
    {
        if($title) {
            return $query->where('title', 'regexp', "/$title/i");
        }

        return $query;
    }

    /**
     * Partial description match query scope
     *
     * @param  Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $description
     * @return Illuminate\Database\Eloquent\Builder
     */
    public function scopeDescriptionPartial($query, $description)
    {
        if($description) {
            return $query->where('description', 'regexp', "/$description/i");
        }

        return $query;
    }

    /**
     * Completed match query scope
     *
     * @param  Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $completed
     * @return Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query, $applyFilter, $completed)
    {
        if($applyFilter) {
            return $query->where('completed', (boolean)$completed);
        }

        return $query;
    }

    /**
     * Due date from query scope
     *
     * @param  Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $datetime
     * @return Illuminate\Database\Eloquent\Builder
     */
    public function scopeDueDateFrom($query, $datetime)
    {
        if($datetime) {
            $cd = new Carbon($datetime);

            return $query->where('due_date', '>=', new UTCDateTime($cd->timestamp * 1000));
        }

        return $query;
    }

    /**
     * Due date to query scope
     *
     * @param  Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $datetime
     * @return Illuminate\Database\Eloquent\Builder
     */
    public function scopeDueDateTo($query, $datetime)
    {
        if($datetime) {
            $cd = new Carbon($datetime);

            return $query->where('due_date', '<=', new UTCDateTime($cd->timestamp * 1000));
        }

        return $query;
    }

    /**
     * Created At date from query scope
     *
     * @param  Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $datetime
     * @return Illuminate\Database\Eloquent\Builder
     */
    public function scopeCreatedAtFrom($query, $datetime)
    {
        if($datetime) {
            $cd = new Carbon($datetime);

            return $query->where('created_at', '>=', new UTCDateTime($cd->timestamp * 1000));
        }

        return $query;
    }

    /**
     * Created At date to query scope
     *
     * @param  Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $datetime
     * @return Illuminate\Database\Eloquent\Builder
     */
    public function scopeCreatedAtTo($query, $datetime)
    {
        if($datetime) {
            $cd = new Carbon($datetime);

            return $query->where('created_at', '<=', new UTCDateTime($cd->timestamp * 1000));
        }

        return $query;
    }

    /**
     * Updated At date from query scope
     *
     * @param  Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $datetime
     * @return Illuminate\Database\Eloquent\Builder
     */
    public function scopeUpdatedAtFrom($query, $datetime)
    {
        if($datetime) {
            $cd = new Carbon($datetime);

            return $query->where('updated_at', '>=', new UTCDateTime($cd->timestamp * 1000));
        }

        return $query;
    }

    /**
     * Updated At date to query scope
     *
     * @param  Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $datetime
     * @return Illuminate\Database\Eloquent\Builder
     */
    public function scopeUpdatedAtTo($query, $datetime)
    {
        if($datetime) {
            $cd = new Carbon($datetime);

            return $query->where('updated_at', '<=', new UTCDateTime($cd->timestamp * 1000));
        }

        return $query;
    }

    /**
     * User owner role
     *
     * @param  Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $$role
     * @return Illuminate\Database\Eloquent\Builder
     */
    public function scopeUserRole($query, $role)
    {
        if($role) {
            return $query->where('user_role', '=', $role);
        }

        return $query;
    }
}
