<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Jenssegers\Mongodb\Eloquent\Builder;
use App\User;
use DB;
use Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('validrole', function ($attribute, $value, $parameters, $validator) {
            return User::isValidRole($value);
        });
        Validator::extend('validstatus', function ($attribute, $value, $parameters, $validator) {
            return User::isValidStatus($value);
        });

        Validator::extend('validuserid', function ($attribute, $value, $parameters, $validator) {
            return is_object(User::find($value));
        });

        if(env('DB_DEBUG')) {
            DB::connection()->enableQueryLog();
            DB::listen(function ($query) {
                // $query->sql
                // $query->bindings
                // $query->time
                Log::debug($query->sql);
            });
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Bugfix for php artisan db:seed
        // ref https://github.com/jenssegers/laravel-mongodb/issues/1191
        Builder::macro('getName', function() {
            return 'mongodb';
        });
    }
}
