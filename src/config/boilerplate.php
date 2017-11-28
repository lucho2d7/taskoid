<?php

return [

    'sign_up' => [
        'release_token' => env('SIGN_UP_RELEASE_TOKEN'),
        'return_verification_token' => env('SIGN_UP_RETURN_ACCOUNT_VERIFICATION_TOKEN'),
        'validation_rules' => [
            'name' => 'required|min:2|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|max:255|confirmed'
        ]
    ],

    'sign_up_verification' => [
        'release_token' => env('SIGN_UP_VERIFICATION_RELEASE_TOKEN'),
        'validation_rules' => [
            'email' => 'required|email',
            'password' => 'required'
        ]
    ],

    'login' => [
        'validation_rules' => [
            'email' => 'required|email',
            'password' => 'required'
        ]
    ],

    'forgot_password' => [
        'return_password_recovery_token' => env('FORGOT_PASSWORD_RETURN_RESET_TOKEN', false),
        'validation_rules' => [
            'email' => 'required|email'
        ]
    ],

    'reset_password' => [
        'release_token' => env('PASSWORD_RESET_RELEASE_TOKEN', false),
        'validation_rules' => [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|max:255|confirmed'
        ]
    ]

];
