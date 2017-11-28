<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Task;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $user = new User();
        $user->name = "Admin";
        $user->email = "admin@localhost.dev";
        $user->password = 'admin';
        $user->role = User::ROLE_SUPERADMIN;
        $user->status = User::STATUS_ENABLED;
        $user->save();

        $user = new User();
        $user->name = "John Doe";
        $user->email = "john@localhost.dev";
        $user->password = 'john';
        $user->role = User::ROLE_ADMIN;
        $user->status = User::STATUS_ENABLED;
        $user->save();

        $user = new User();
        $user->name = "Robert Downey";
        $user->email = "robert@localhost.dev";
        $user->password = 'RobertRobert';
        $user->role = User::ROLE_ADMIN;
        $user->status = User::STATUS_ENABLED;
        $user->save();

        $user = new User();
        $user->name = "Patrick Grant";
        $user->email = "patric@localhost.dev";
        $user->password = 'patric';
        $user->role = User::ROLE_USER;
        $user->status = User::STATUS_ENABLED;
        $user->save();

        $user = new User();
        $user->name = "Eric Bell";
        $user->email = "eric@localhost.dev";
        $user->password = 'eric';
        $user->role = User::ROLE_USER;
        $user->status = User::STATUS_ENABLED;
        $user->save();
        $user->tasks()->saveMany(factory(Task::class, 5)->make());

        $user = new User();
        $user->name = "Dave Flett";
        $user->email = "dave@localhost.dev";
        $user->password = 'dave';
        $user->role = User::ROLE_USER;
        $user->status = User::STATUS_ENABLED;
        $user->save();
        $user->tasks()->saveMany(factory(Task::class, 50)->make());

        factory(User::class, 5)->create()->each(function ($u) {
            $u->tasks()->saveMany(factory(Task::class, 50)->make());
        });

        factory(User::class, 2)->states(User::ROLE_ADMIN)->create()->each(function ($u) {
            $u->tasks()->saveMany(factory(Task::class, 50)->make());
        });

        factory(User::class, 5)->states(User::STATUS_DISABLED)->create()->each(function ($u) {
            $u->tasks()->saveMany(factory(Task::class, 50)->make());
        });
    }
}
