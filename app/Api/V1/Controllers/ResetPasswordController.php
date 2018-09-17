<?php

namespace App\Api\V1\Controllers;

use Config;
use Tymon\JWTAuth\JWTAuth;
use App\Api\V1\Models\User;
use App\Api\V1\Models\JwtToken;
use App\Api\V1\Models\PasswordReset;
use App\Http\Controllers\Controller;
use App\Api\V1\Models\AdminCreatedUser;
use App\Api\V1\Requests\AdminCreateUser;
use App\Api\V1\Requests\ResetPasswordRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ResetPasswordController extends Controller
{
    /**
     * validate password request url
     *
     * @param string $reset_token
     *
     * @return \Dingo\Api\Http\Response
     */
    public function getResetPassword($reset_token)
    {
        $passwordReset = PasswordReset::where('token', $reset_token)->first();

        //is password reset token in db and does user exist
        if ($passwordReset && $passwordReset->token === $reset_token) {
            $user = User::where('email', $passwordReset->email)->first();
            if ($user) {
                if (($adminUser = AdminCreatedUser::where('user_id', $user->id)->first())) {
                    $user->update(['is_active' => 1]);
                    $adminUser->delete();
                }
                //reset_token exists, respond ok with reset_token,
                // for the user to be shown form

                return $this->response->accepted(null, ['status' => 'ok', 'reset_token' => $reset_token]);
            }
        }
        //token invalid
        throw new AccessDeniedHttpException('Invalid or expired reset link');
    }

    /**
     * reset the account when form is submitted
     *
     * @param ResetPasswordRequest $request
     * @param JWTAuth $JWTAuth
     *
     * @return json response with JWToken
     */
    public function doResetPassword(ResetPasswordRequest $request, JWTAuth $JWTAuth)
    {
        $data = $request->all();
        $passwordReset = PasswordReset::where('token', $data['reset_token'])->first();
        if ($passwordReset && $passwordReset->token === $data['reset_token'] &&
            ($user = User::where('email', $passwordReset->email)->first())
        ) {
            //reset their password
            //$password = bcrypt($data['password']);
            $user = User::find($user->id);
            $user->password = $data['password'];
            /* if ($user->is_active === 0) {
                $user->is_active = 1;
            } */
            $user->save();
            //delete the reset_token
            PasswordReset::where('token', $data['reset_token'])->delete();

            if (!Config::get('apiauth.reset_password.release_token')) {
                return $this->response->array([
                    'status' => 'password reset successfully'
                ]);
            }
            

            //$user = User::where('email', '=', $request->get('email'))->first();
            //release token for user to be used to
            //show && submit password change form
            return $this->response->array([
                'status' => 'password reset successfully'/* ,
                'token' => $JWTAuth->fromUser($user) */
            ]);
        }
        //invalid request
        return $this->response->errorBadRequest('Invalid request');
    }
}
