<?php

namespace App\Api\V1\Controllers;

use Tymon\JWTAuth\JWTAuth;
use Dingo\Api\Http\Response;
use App\Api\V1\Models\User;
use Illuminate\Support\Facades\Config;
use App\Events\ActivateUserAccount;
use App\Http\Controllers\Controller;
use App\Api\V1\Models\AccountActivation;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AccountActivationController extends Controller
{
    /**
    * Activate user account
    * @param string $activation_token
    *
    * @return json response
    *
    * @SWG\Get(
        *   path="/auth/activate/{activation_token}",
        *   tags={"activation"},
        *   summary="Activate user account",
        *   description="",
        *   operationId="index",
        *   produces={"application/json"},
        *   @SWG\Parameter(
        *     name="activation_token",
        *     in="path",
        *     description="the activation token that was sent to the user",
        *     required=true,
        *     type="string"
        *   ),
        *   @SWG\Response(response=201, description="Account activated",
        *       @SWG\Schema(
        *         @SWG\Property(
        *              property="status",
        *              type="string",
        *              description="Account activated"
        *          )
        * )),
        *   @SWG\Response(response=400, description="Invalid activation link"),
        * )
        */
    public function getAccountActivation($activation_token)
    {
        $accountActivation = AccountActivation::where('token', $activation_token)->first();

        //is activation_token in db and does user exist
        if ($accountActivation && $user = User::where('email', $accountActivation->email)->first()) {
            //activation_token exists, activate the user,
            $user->is_active = 1;
            $user->save();

            AccountActivation::where('email', $accountActivation->email)->delete();
            //fire account activation event
            event(new ActivateUserAccount($user));

            return $this->response->created(null, ['status' => 'Account activated']);
        }
        //token invalid
        return $this->response->errorBadRequest("Invalid activation link");
    }
}
