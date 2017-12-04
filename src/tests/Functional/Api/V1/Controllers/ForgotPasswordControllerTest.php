<?php

namespace App\Functional\Api\V1\Controllers;

use App\User;
use App\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * @group forgotPassword
 * Tests the api password recovery handling requests
 */
class ForgotPasswordControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        $user = new User([
            'name' => 'Test',
            'email' => 'test@email.com',
            'password' => '123456',
            'role' => User::ROLE_USER,
            'status' => User::STATUS_ENABLED,
        ]);

        $user->save();
    }

    public function testForgotPasswordRecoverySuccessfully()
    {
        $this->post('api/auth/recovery', [
            'email' => 'test@email.com'
        ], [
            'Accept' => $this->apiAcceptHeader
        ])->assertJson([
            'status' => 'ok'
        ])->isOk();
    }

    public function testForgotPasswordRecoveryReturnsUserNotFoundError()
    {
        $this->post('api/auth/recovery', [
            'email' => 'unknown@email.com'
        ], [
            'Accept' => $this->apiAcceptHeader
        ])->assertJsonStructure([
            'error'
        ])->assertStatus(404);
    }

    public function testForgotPasswordRecoveryReturnsValidationErrors()
    {
        $this->post('api/auth/recovery', [
            'email' => 'i am not an email'
        ], [
            'Accept' => $this->apiAcceptHeader
        ])->assertJsonStructure([
            'error'
        ])->assertStatus(422);
    }
}
