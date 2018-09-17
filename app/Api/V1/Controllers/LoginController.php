<?php

namespace App\Api\V1\Controllers;

use Tymon\JWTAuth\JWTAuth;
use Dingo\Api\Http\Response;
use App\Events\Login;
use App\Api\V1\Models\User;
use App\Events\LoginFailed;
use Illuminate\Support\Facades\File;
use App\Events\LoginNotActive;
use App\Api\V1\Models\JwtToken;
use Illuminate\Support\Facades\Validator;
use App\Events\UserLoginIsLocked;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Controllers\Controller;
use App\Api\V1\Requests\LoginRequest;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class LoginController extends Controller
{
    public function login(LoginRequest $request, JWTAuth $JWTAuth)
    {
        $credentials = $request->only(['username', 'password']);

        try {
            $token = $JWTAuth->attempt($credentials);

            if (!$token) {
                event(new LoginFailed($request->ip()));
                throw new AccessDeniedHttpException('Access denied, invalid credential.');
            }
        } catch (JWTException $e) {
            return $this->response->errorBadRequest($e->getMessage());
        }

        //user authenticated
        //check if they are active
        $user = User::where('username', $credentials['username'])->firstOrFail();

        if ($user->is_active == 0) {
            event(new LoginNotActive($user));
            throw new AccessDeniedHttpException('inactive_user');
        }
    
        //check if user cancelled their account
        if ($user->cancelledAccount()) {
            event(new LoginFailed($request->ip()));
            throw new AccessDeniedHttpException('Access denied. You have either cancelled or deleted your account.');
        }

        //fire login event
        event(new Login($user));


        //return response with token
        return $this->response->array([
            'token' => $token
        ]);
    }
}
