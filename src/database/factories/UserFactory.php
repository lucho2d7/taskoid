<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/
use App\User;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = 'secret',
        'remember_token' => str_random(10),
        'role' => User::ROLE_USER,
        'status' => User::STATUS_ENABLED,
    ];
});

$factory->state(App\User::class, User::ROLE_ADMIN, function ($faker) {
    return [
        'role' => User::ROLE_ADMIN,
    ];
});

$factory->state(App\User::class, User::STATUS_DISABLED, function ($faker) {
    return [
        'status' => User::STATUS_DISABLED,
    ];
});