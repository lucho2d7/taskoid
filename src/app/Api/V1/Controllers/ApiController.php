<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use Dingo\Api\Exception\ValidationHttpException;

abstract class ApiController extends Controller
{
  protected function throwValidationException(\Illuminate\Http\Request $request, $validator) {
    throw new ValidationHttpException($validator->errors());
  }
}