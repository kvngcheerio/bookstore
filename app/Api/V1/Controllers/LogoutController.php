<?php

namespace App\Api\V1\Controllers;

use Tymon\JWTAuth\JWTAuth;
use App\Events\Logout;
use App\Api\V1\Models\User;
use App\Api\V1\Models\JwtToken;

class LogoutController extends \App\Http\Controllers\Controller
{
    /**
    * Logout user by invalidating their token
    *
    * @param Request $request
    * @return json reponse
    */
    public function LogoutUser(User $user, JWTAuth $JWTAuth)
    {
        //get authenticated user to user for event
        $user = UserController::getAuthUser($JWTAuth);

        //invalidate token
        $JWTAuth->invalidate($JWTAuth->getToken());
        
        //fire logged out event
        event(new Logout($user));

        
        return $this->response->array([
            'status' => 'ok',
            'message' => 'logged out'
        ]);
    }
}
