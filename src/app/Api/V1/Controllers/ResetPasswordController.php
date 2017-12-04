<?php

namespace App\Api\V1\Controllers;

use Config;
use App\User;
use Tymon\JWTAuth\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
use App\Api\V1\Requests\ResetPasswordRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;
use MongoDB\BSON\UTCDateTime;
use DateTimeZone;
use DateTime;
use Illuminate\Support\Facades\Log;

/**
 * User password reset function.
 *
 * @Resource("Auth Reset Password", uri="/auth/reset")
 */
class ResetPasswordController extends ApiController
{
    /**
     * Request a password reset.
     *
     * @Post("/")
     * @Versions({"v1"})
     * @Parameters({
     *      @Parameter("token", type="string", description="A valid password reset token.", required=true),
     *      @Parameter("email", type="string", description="The user email.", required=true),
     *      @Parameter("password", type="string", description="A new password.", required=true),
     *      @Parameter("password_confirmation", type="string", description="The new password confirmation.", required=true),
     * })
     * @Response(200, body={"status":"ok"})
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Tymon\JWTAuth\JWTAuth  $JWTAuth
     * @return \Illuminate\Http\Response
     */
    public function resetPassword(ResetPasswordRequest $request, JWTAuth $JWTAuth)
    {
        $response = $this->broker()->reset(
            $this->credentials($request), function ($user, $password) {
                $this->reset($user, $password);
            }
        );

        if($response !== Password::PASSWORD_RESET) {
            throw new HttpException(422);//Unprocessable Entity
        }

        $response = ['status' => 'ok'];

        if(Config::get('boilerplate.reset_password.release_token')) {
            $currentUser = User::email($request->input('email'))->first();
            $response['token'] = $JWTAuth->fromUser($currentUser);
        }

        return response()->json($response, 200);
    }

    /**
     * Get the broker to be used during password reset.
     *
     * TODO: remove this method once Jenssegers\Mongodb reset password issues are fixed
     * REF: https://github.com/jenssegers/laravel-mongodb/issues/1124
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function broker()
    {
        return new class(app()) extends \Jenssegers\Mongodb\Auth\PasswordBrokerManager {

            protected function createTokenRepository(array $config)
            {
                $c = $this->app['db']->connection();
                $h = $this->app['hash'];
                $t = $config['table'];
                $k = $this->app['config']['app.key'];
                $e = $config['expire'];

                return new class($c, $h, $t, $k, $e) extends \Illuminate\Auth\Passwords\DatabaseTokenRepository {

                    protected function getPayload($email, $token)
                    {
                        return ['email' => $email, 'token' => $this->hasher->make($token), 'created_at' => new UTCDateTime(time() * 1000)];
                    }

                    protected function tokenExpired($token)
                    {
                        // Convert UTCDateTime to a date string.
                        if ($token instanceof UTCDateTime) {
                            $date = $token->toDateTime();
                            $date->setTimezone(new DateTimeZone(date_default_timezone_get()));
                            $token = $date->format('Y-m-d H:i:s');
                        } elseif (is_array($token) and isset($token['date'])) {
                            $date = new DateTime($token['date'], new DateTimeZone(isset($token['timezone']) ? $token['timezone'] : 'UTC'));
                            $date->setTimezone(new DateTimeZone(date_default_timezone_get()));
                            $token = $date->format('Y-m-d H:i:s');
                        }

                        return parent::tokenExpired($token);
                    }
                };
            }
        };
    }

    /**
     * Get the password reset credentials from the request.
     *
     * @param  ResetPasswordRequest  $request
     * @return array
     */
    protected function credentials(ResetPasswordRequest $request)
    {
        return $request->only(
            'email', 'password', 'password_confirmation', 'token'
        );
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $password
     * @return void
     */
    protected function reset($user, $password)
    {
        $user->password = $password;
        $user->save();
    }
}
