<?php

namespace App\Functional\Api\V1\Controllers;

use Config;
use App\User;
use App\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * @group signup
 * Tests the api signup handling requests
 */
class SignUpControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function testSignUpSuccessfully()
    {
        $this->post('api/auth/signup', [
            'name' => 'Test User',
            'email' => 'test@email.com',
            'password' => '12345678',
            'password_confirmation' => '12345678',
            'role' => User::ROLE_USER,
            'status' => User::STATUS_ENABLED,
        ], ['Accept' => $this->apiAcceptHeader
        ])->assertJson([
            'status' => 'ok'
        ])->assertStatus(201);
    }

    public function testSignUpSuccessfullyWithTokenRelease()
    {
        Config::set('boilerplate.sign_up.release_token', true);

        $this->post('api/auth/signup', [
            'name' => 'Test User',
            'email' => 'test@email.com',
            'password' => '12345678',
            'password_confirmation' => '12345678'
        ], ['Accept' => $this->apiAcceptHeader
        ])->assertJsonStructure([
            'token',
            'status',
        ])->assertJson([
            'status' => 'ok'
        ])->assertStatus(201);
    }

    public function testSignUpReturnsValidationError()
    {
        $this->post('api/auth/signup', [
            'name' => 'Test User',
            'email' => 'test@email.com'
        ], ['Accept' => $this->apiAcceptHeader
        ])->assertJsonStructure([
            'error'
        ])->assertStatus(422);
    }
}
