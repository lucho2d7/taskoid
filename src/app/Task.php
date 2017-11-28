<?php

namespace App;

use Moloquent;

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
            return $query->where('title', 'like', '%'.$title.'%');
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
            return $query->where('description', 'like', '%'.$description.'%');
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
            return $query->where('due_date', '>=', $datetime);
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
            return $query->where('due_date', '<=', $datetime);
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
            return $query->where('created_at', '>=', $datetime);
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
            return $query->where('created_at', '<=', $datetime);
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
            return $query->where('updated_at', '>=', $datetime);
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
            return $query->where('updated_at', '<=', $datetime);
        }

        return $query;
    }
}
