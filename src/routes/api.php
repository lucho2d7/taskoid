<?php

use Dingo\Api\Routing\Router;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;

//////////////////////////////
// Rethrow Dingo API auth exceptions for compatibility with Laravel Authorization Policy exception chain
$this->app['api.exception']->register(function (Illuminate\Auth\Access\AuthorizationException $exception) {
    throw new Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException($exception->getMessage(), $exception);
});

$this->app['api.exception']->register(function (ModelNotFoundException $exception) {
    throw new Symfony\Component\HttpKernel\Exception\NotFoundHttpException("not found", $exception);
});

$this->app['api.exception']->register(function (QueryException $exception) {
    $message = env('API_DEBUG') ? $exception->getMessage() : '';
    throw new Symfony\Component\HttpKernel\Exception\HttpException(500, $message);
});

$this->app['api.exception']->register(function (ErrorException $exception) {
    $message = env('API_DEBUG') ? $exception->getMessage() : '';
    throw new Symfony\Component\HttpKernel\Exception\HttpException(500, $message);
});

$this->app['api.exception']->register(function (BadMethodCallException $exception) {
    $message = env('API_DEBUG') ? $exception->getMessage() : '';
    throw new Symfony\Component\HttpKernel\Exception\HttpException(500, $message);
});
// end rethrow Dingo API auth exceptions
//////////////////////////////

/** @var Router $api */
$api = app(Router::class);

$api->version('v1', function (Router $api) {

    $api->group(['prefix' => 'auth'], function(Router $api) {
        $api->post('signup', 'App\\Api\\V1\\Controllers\\SignUpController@signUp');
        $api->post('signup_verification', 'App\\Api\\V1\\Controllers\\SignUpController@signUpVerification');
        $api->post('login', 'App\\Api\\V1\\Controllers\\LoginController@login');

        $api->post('recovery', 'App\\Api\\V1\\Controllers\\ForgotPasswordController@sendResetEmail');
        $api->post('reset', 'App\\Api\\V1\\Controllers\\ResetPasswordController@resetPassword');
    });

    $api->group(['middleware' => ['jwt.auth', 'bindings', 'throttle:60,1']], function(Router $api) {
        $api->group(['prefix' => 'auth'], function(Router $api) {
            $api->get('logout', 'App\\Api\\V1\\Controllers\\LoginController@logout');
        });

        $api->get('tasks', 'App\\Api\\V1\\Controllers\\TaskController@index');
        $api->post('tasks', 'App\\Api\\V1\\Controllers\\TaskController@store');
        $api->get('tasks/{task}', 'App\\Api\\V1\\Controllers\\TaskController@view');
        $api->put('tasks/{task}', 'App\\Api\\V1\\Controllers\\TaskController@update');
        $api->delete('tasks/{task}', 'App\\Api\\V1\\Controllers\\TaskController@delete');

        $api->get('users', 'App\\Api\\V1\\Controllers\\UserController@index');
        $api->post('users', 'App\\Api\\V1\\Controllers\\UserController@store');
        $api->get('users/{user}', 'App\\Api\\V1\\Controllers\\UserController@view');
        $api->put('users/{user}', 'App\\Api\\V1\\Controllers\\UserController@update');
        $api->delete('users/{user}', 'App\\Api\\V1\\Controllers\\UserController@delete');

        $api->get('refresh', [
            'middleware' => 'jwt.refresh',
            function() {
                return response()->json([
                    'message' => 'By accessing this endpoint, you can refresh your access token at each request. Check out this response headers.'
                ]);
            }
        ]);
    });
});
