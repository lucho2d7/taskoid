<?php

namespace App\Functional\Api\V1\Controllers;

use DB;
use Config;
use App\User;
use App\TestCase;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Hashing\BcryptHasher;

/**
 * @group resetPassword
 * Tests the api password reset handling requests
 */
class ResetPasswordControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        $user = new User([
            'name' => 'Test User',
            'email' => 'test@email.com',
            'password' => '123456',
            'role' => User::ROLE_USER,
            'status' => User::STATUS_ENABLED,
        ]);
        $user->save();
        $hash = new BcryptHasher();
        DB::table('password_resets')->insert([
            'email' => 'test@email.com',
            'token' => $hash->make('my_super_secret_code'),
            'created_at' => Carbon::now()
        ]);
    }

    public function testResetSuccessfully()
    {
        $this->post('api/auth/reset', [
            'email' => 'test@email.com',
            'token' => 'my_super_secret_code',
            'password' => 'mynewpass',
            'password_confirmation' => 'mynewpass'
        ], [
            'Accept' => $this->apiAcceptHeader
        ])->assertJson([
            'status' => 'ok'
        ])->isOk();
    }

    public function testResetSuccessfullyWithTokenRelease()
    {
        Config::set('boilerplate.reset_password.release_token', true);

        $this->post('api/auth/reset', [
            'email' => 'test@email.com',
            'token' => 'my_super_secret_code',
            'password' => 'mynewpass',
            'password_confirmation' => 'mynewpass'
        ], [
            'Accept' => $this->apiAcceptHeader
        ])->assertJsonStructure([
            'status',
            'token'
        ])->assertJson([
            'status' => 'ok'
        ])->isOk();
    }

    public function testResetReturnsProcessError()
    {
        $this->post('api/auth/reset', [
            'email' => 'unknown@email.com',
            'token' => 'this_code_is_invalid',
            'password' => 'mynewpass',
            'password_confirmation' => 'mynewpass'
        ], [
            'Accept' => $this->apiAcceptHeader
        ])->assertJsonStructure([
            'error'
        ])->assertStatus(422);
    }

    public function testResetReturnsValidationError()
    {
        $this->post('api/auth/reset', [
            'email' => 'test@email.com',
            'token' => 'my_super_secret_code',
            'password' => 'mynewpass'
        ], [
            'Accept' => $this->apiAcceptHeader
        ])->assertJsonStructure([
            'error'
        ])->assertStatus(422);
    }
}
