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
use App\Task;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Task::class, function (Faker\Generator $faker) {

    $created_at = $faker->dateTimeBetween('-3 month', '+2 month');
    $due_date = $faker->dateTimeBetween($created_at, '+3 month');
    $updated_at = $faker->dateTimeBetween($created_at, $due_date);

    return [
        'title' => $faker->realText($faker->numberBetween(10, 20)),
        'description' => $faker->realText($faker->numberBetween(10, 1020)),
        'completed' => $faker->boolean(50),
        'due_date' => $due_date,
        'created_at' => $created_at,
        'updated_at' => $updated_at,
        /*'user_id' => function () {
            return factory(User::class)->create()->id;
        }*/
    ];
});