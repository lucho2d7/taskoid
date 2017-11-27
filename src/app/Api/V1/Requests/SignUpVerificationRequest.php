<?php

namespace App\Api\V1\Requests;

use Config;
use Dingo\Api\Http\FormRequest;

class SignUpVerificationRequest extends FormRequest
{
    public function rules()
    {
        return Config::get('boilerplate.sign_up_verification.validation_rules');
    }

    public function authorize()
    {
        return true;
    }
}
