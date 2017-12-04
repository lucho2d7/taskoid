<?php

namespace App\Api\V1\Controllers;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Tymon\JWTAuth\JWTAuth;
use App\Http\Controllers\Controller;
use App\Api\V1\Requests\LoginRequest;
use App\Api\V1\Requests\LogoutRequest;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use App\User;

/**
 * Login resource representation.
 *
 * @Resource("Auth Login", uri="/auth/login")
 */
class LoginController extends ApiController
{
    /**
     * Login
     *
     * @Post("/")
     * @Versions({"v1"})
     * @Parameters({
     *      @Parameter("email", type="string", description="User email.", required=true),
     *      @Parameter("password", type="string", description="User password.", required=true),
     * })
     * @Response(200, body={"status":"ok","_id":"2","role":"user","name":"John Doe","token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOiI1YTI1ZDJlNzcwZjFiODAwMDgxZGYzZTQiLCJpc3MiOiJodHRwOi8vbG9jYWxob3N0OjgwODEvYXBpL2F1dGgvbG9naW4iLCJpYXQiOjE1MTI0MjgyODEsImV4cCI6MTUxMjQzMTg4MSwibmJmIjoxNTEyNDI4MjgxLCJqdGkiOiIxblFzUGlwWmZQVDRLVkVvIn0.Jf-suzGfgnYeEFTmKhAHLNUoBkwQ5X0a8_V-PuSKy4E"})
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Tymon\JWTAuth\JWTAuth  $JWTAuth
     * @return \Illuminate\Http\Response
     */
    public function login(LoginRequest $request, JWTAuth $JWTAuth)
    {
        $credentials = $request->only(['email', 'password']);
        $credentials['status'] = User::STATUS_ENABLED;

        try {
            $token = $JWTAuth->attempt($credentials);

            if(!$token) {
                throw new AccessDeniedHttpException();
            }

        } catch (JWTException $e) {
            throw new HttpException(500);
        }

        $currentUser = User::where('email', $request->input('email'))->get()->first();

        return response()
            ->json([
                'status' => 'ok',
                '_id' => $currentUser->_id,
                'role' => $currentUser->role,
                'name' => $currentUser->name,
                'token' => $token
            ]);
    }

    /**
     * Logout
     *
     * @Post("/")
     * @Versions({"v1"})
     * @Parameters({
     * })
     * @Response(200, body={"status":"ok"})
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Tymon\JWTAuth\JWTAuth  $JWTAuth
     * @return \Illuminate\Http\Response
     */
    public function logout(LogoutRequest $request, JWTAuth $JWTAuth)
    {
        $JWTAuth->parseToken()->invalidate();

        return response()
            ->json([
                'status' => 'ok'
            ]);
    }
}