<?php

namespace App\Functional\Api\V1\Controllers;

use Hash;
use App\User;
use App\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * @group login
 * Tests the api login handling requests
 */
class LoginControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        $user = new User([
            'name' => 'Test',
            'email' => 'test@email.com',
            'password' => '12345678',
            'role' => User::ROLE_USER,
            'status' => User::STATUS_ENABLED,
        ]);

        $user->save();
    }

    public function testLoginSuccessfully()
    {
        $this->post('api/auth/login', [
            'email' => 'test@email.com',
            'password' => '12345678'
        ], [
            'Accept' => $this->apiAcceptHeader
        ])->assertJson([
            'status' => 'ok'
        ])->assertJsonStructure([
            'status',
            'token'
        ])->isOk();
    }

    public function testLoginWithReturnsWrongCredentialsError()
    {
        $this->post('api/auth/login', [
            'email' => 'unknown@email.com',
            'password' => '123456'
        ], [
            'Accept' => $this->apiAcceptHeader
        ])->assertJsonStructure([
            'error'
        ])->assertStatus(403);
    }

    public function testLoginWithReturnsValidationError()
    {
        $this->post('api/auth/login', [
            'email' => 'test@email.com'
        ], [
            'Accept' => $this->apiAcceptHeader
        ])->assertJsonStructure([
            'error'
        ])->assertStatus(422);
    }
}
