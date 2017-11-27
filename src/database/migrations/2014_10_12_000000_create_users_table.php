<?php

use Illuminate\Support\Facades\Schema;
use Jenssegers\Mongodb\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->enum('role', ['user', 'admin', 'superadmin'])->default('user');
            $table->enum('status', ['disabled', 'enabled'])->default('disabled');
            $table->string('status_validation_token')->default('');
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //dropIfExists fails with mongodb driver
        //Schema::dropIfExists('users');
        
        Schema::drop('users');
    }
}
