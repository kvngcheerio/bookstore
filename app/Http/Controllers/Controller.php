<?php

namespace App\Http\Controllers;

use Dingo\Api\Routing\Helpers;
use Illuminate\Support\Facades\Response;
use App\Api\V1\Controllers\UserController;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Api\V1\Traits\Authorizable;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Dingo\Api\Exception\ValidationHttpException;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, Helpers;

    public function __construct()
    {

    }

    /**
     * overrides the default throwValidationException method in ValidatesRequests trait,
     * so that we'll get valid error messages response, else dingo doesn't display the error bag
     */
    protected function throwValidationException(\Illuminate\Http\Request $request, $validator)
    {
        throw new ValidationHttpException($validator->errors());
    }
}
