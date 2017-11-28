<?php

namespace App\Api\V1\Controllers;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
use App\Api\V1\Requests\ForgotPasswordRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use MongoDB\BSON\UTCDateTime;
use DateTimeZone;
use DateTime;

/**
 * Forgotten password recovery function.
 *
 * @Resource("Auth Forgot Password", uri="/auth/recovery")
 */
class ForgotPasswordController extends ApiController
{
    /**
     * Request a password reset link through email.
     *
     * @Post("/")
     * @Versions({"v1"})
     * @Parameters({
     *      @Parameter("email", type="string", description="User email.", required=true),
     * })
     * @Response(200, body={"status":"ok"})
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Tymon\JWTAuth\JWTAuth  $JWTAuth
     * @return \Illuminate\Http\Response
     */
    public function sendResetEmail(ForgotPasswordRequest $request)
    {
        $user = User::where('email', '=', $request->get('email'))->first();

        if(!$user) {
            throw new NotFoundHttpException();
        }

        $broker = $this->broker();
        $sendingResponse = $broker->sendResetLink($request->only('email'));

        if($sendingResponse !== Password::RESET_LINK_SENT) {
            throw new HttpException(500);
        }

        if(env('APP_DEBUG')) {
            // Return the token to allow automated API testing
            $response = [
                            'status' => 'ok',
                            'testing_pwd_reset_token' => session('password_recovery_token'),
                        ];

            session(['password_recovery_token' => null]);
        }
        else {
            $response = [
                            'status' => 'ok'
                        ];
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
                        return ['email' => $email, 'token' => $this->hasher->make($token), 'created_at' => new UTCDateTime(new DateTime)];
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
}
