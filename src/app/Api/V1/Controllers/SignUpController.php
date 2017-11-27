<?php

namespace App\Api\V1\Controllers;

use Exception;
use Config;
use App\User;
use Tymon\JWTAuth\JWTAuth;
use App\Http\Controllers\Controller;
use App\Api\V1\Requests\SignUpRequest;
use App\Api\V1\Requests\SignUpVerificationRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Illuminate\Hashing\BcryptHasher;
/**
 * Signup resource representation.
 *
 * @Resource("Auth Signup", uri="/auth/signup")
 */
class SignUpController extends ApiController
{
    /**
     * Signup to the system as user.
     *
     * @Post("/")
     * @Versions({"v1"})
     * @Parameters({
     *      @Parameter("name", type="string", description="User full name.", required=true),
     *      @Parameter("email", type="string", description="User email.", required=true),
     *      @Parameter("password", type="string", description="User password.", required=true),
     * })
     * @Response(200, body={"status":"ok"})
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Tymon\JWTAuth\JWTAuth  $JWTAuth
     * @return \Illuminate\Http\Response
     */
    public function signUp(SignUpRequest $request, JWTAuth $JWTAuth)
    {
        $user = new User($request->all());
        $user->status = User::STATUS_DISABLED;
        $token = $this->createNewToken();

        $hasher = new BcryptHasher();
        $user->status_validation_token = $hasher->make($token);;

        if(!$user->save()) {
            throw new HttpException(500);
        }

        try {
            $user->sendSignupVerificationNotification($token);
        }
        catch(Exception $e) {
            throw new HttpException(500);
        }

        if(!Config::get('boilerplate.sign_up.release_token')) {
            return response()->json([
                'status' => 'ok'
            ], 201);
        }

        $token = $JWTAuth->fromUser($user);
        return response()->json([
            'status' => 'ok',
            'token' => $token
        ], 201);
    }

    /**
     * Signup verification for a new user.
     *
     * @Post("/")
     * @Versions({"v1"})
     * @Parameters({
     *      @Parameter("name", type="string", description="User full name.", required=true),
     *      @Parameter("email", type="string", description="User email.", required=true),
     *      @Parameter("password", type="string", description="User password.", required=true),
     * })
     * @Response(200, body={"status":"ok"})
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Tymon\JWTAuth\JWTAuth  $JWTAuth
     * @return \Illuminate\Http\Response
     */
    public function signUpVerification(SignUpVerificationRequest $request, JWTAuth $JWTAuth)
    {
        $credentials = $request->only(['email', 'password']);
        $credentials['status'] = User::STATUS_DISABLED;

        try {
            $token = $JWTAuth->attempt($credentials);

            if(!$token) {
                //throw new AccessDeniedHttpException('Invalid email or password');
                throw new HttpException(401, 'Invalid email or password');
            }

        } catch (JWTException $e) {
            throw new HttpException(500);
        }

        $user = User::where('email', $request->input('email'))->get()->first();

        $hasher = new BcryptHasher();

        if(!$hasher->check($request['token'], $user->status_validation_token)) {
            throw new HttpException(401, 'Invalid verification token');
        }

        $user->status_validation_token = '';
        $user->status = User::STATUS_ENABLED;
        $user->save();

        if(!Config::get('boilerplate.sign_up_verification.release_token')) {
            return response()->json([
                'status' => 'ok'
            ], 201);
        }

        //$token = $JWTAuth->fromUser($user);
        return response()->json([
            'status' => 'ok',
            'token' => $token
        ], 201);
    }

    /**
     * Create a new token for the user.
     *
     * @return string
     */
    public function createNewToken()
    {
        $app= App::getFacadeRoot();
        $key = $app['config']['app.key'];

        if (Str::startsWith($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        return hash_hmac('sha256', Str::random(40), $key);
    }
}
