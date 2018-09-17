<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Models\User;
use App\Events\WelcomeEmailSent;
use App\Mail\ForgotPasswordMail;
use App\Api\V1\Models\PasswordReset;
use App\Http\Controllers\Controller;
//use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Mail\Mailer as Mail;
use App\Mail\RequestPasswordResetLinkMail;
use App\Events\SendPasswordResetLinkEmail;
use App\Api\V1\Requests\ForgotPasswordRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ForgotPasswordController extends Controller
{
    public function sendResetEmail(ForgotPasswordRequest $request, Mail $mail, PasswordReset $passwordReset)
    {
        $user = User::where('email', '=', $request->get('email'))->first();
        //check email in user db
        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }
        //valid user
        $reset_token = $user->username . strtotime(\Carbon\Carbon::now(1));
        $reset_token = md5($reset_token);
        $reset_link = $request->frontend_url . '/' . $reset_token;
        

        $passwordReset->create([
            'email' => $user->email,
            'token' => $reset_token
        ]);
        //mail the reset link to user
        event(new SendPasswordResetLinkEmail($user, $reset_link));

        return response()->json(['status' => 'ok'], 200);
    }
}
