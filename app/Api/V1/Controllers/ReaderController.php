<?php

namespace App\Api\V1\Controllers;

use Carbon\Carbon;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Exception\UpdateResourceFailedException;
use App\Api\V1\Models\Address;
use App\Api\V1\Models\AdminCreatedUser;
use App\Api\V1\Models\PasswordReset;
use App\Api\V1\Models\Picture;
use App\Api\V1\Models\Role;
use App\Api\V1\Models\User;
use App\Api\V1\Models\Reader;
use App\Api\V1\Requests\AdminCreateUser;
use App\Api\V1\Requests\AdminEditUserRequest;
use App\Api\V1\Requests\ReaderEditRequest;
use App\Events\AdminUpdateUserDetail;
use App\Events\SendPasswordResetLinkEmail;
use Tymon\JWTAuth\JWTAuth;
use Validator;

class ReaderController extends Controller
{

    public function show(JWTAuth $JWT_auth)
    {
        $user = UserController::getAuthUser($JWT_auth);
        if (!$user || !($reader = Reader::where('user_id', $user->id)->first())) {
            return $this->response->error('Reader authentication invalid', 404);
        }

        $detail = $reader->toArray();

        $logo = Picture::where('id', $reader->picture_id)->first();
        if ($logo) {
            $detail['logo'] = $logo->toArray();
        }


        return $this->response->array(['detail' => $detail]);
    }


    public function update(ReaderEditRequest $request, JWTAuth $JWT_auth)
    {
        $user = UserController::getAuthUser($JWT_auth);
        if (!$user || !($reader = Reader::where('user_id', $user->id)->first())) {
            return $this->response->error('Reader authentication invalid', 404);
        }

        if ($user->email != $request->email) {
            if (User::where('email', $request->email)->where('id', '!=', $user->id)->first()) {
                return $this->response->error('The email supplied has been taken.', 406);
            }
        }

        if ($user->phone != $request->phone) {
            if (User::where('phone', $request->phone)->where('id', '!=', $user->id)->first()) {
                return $this->response->error('The phone number supplied has been taken.', 406);
            }
        }


        $user_updated = $user->update($request->only(['email', 'first_name', 'last_name', 'middle_name', 'phone']));
        

        $reader_updated = $reader->update($data);

        if ($user_updated && $reader_updated ) {
            return response()->json(['status' => 'Update successful'], 202);                    
        }

        throw new UpdateResourceFailedException('Unable to update profile');
    }

    public function adminUpdate(JWTAuth $JWT_auth, AdminEditUserRequest $request, $id)
    {
        $admin = UserController::getAuthUser($JWT_auth);
        if (!$admin) {
            return $this->response->errorUnauthorized("Access denied.");
        }

        $adminDetails = UserController::getAdminDetails($admin);
        if (!$adminDetails) {
            return $this->response->errorNotFound('Please check your authentication.');
        }

        $user = User::where('id', $id)->first();
        $details = Reader::where('user_id', $user->id)->first();

        if (!$user || !$details) {
            return $this->response->errorNotFound('The reader\'s details are missing.');
        }
       


        if ($user->email != $request->email) {
            if (User::where('email', $request->email)->where('id', '!=', $user->id)->first()) {
                return $this->response->error('The email supplied has been taken.', 406);
            }
        }

        if ($user->phone != $request->phone) {
            if (User::where('phone', $request->phone)->where('id', '!=', $user->id)->first()) {
                return $this->response->error('The phone number supplied has been taken.', 406);
            }
        }

    

        $user_updated = $user->update($request->only(['email', 'first_name', 'last_name', 'middle_name', 'phone']));

        ;

        if ($request->hasFile('logo')) {
        }

      
        if ($user_updated) {
            event(new AdminUpdateUserDetail($admin, $user));

           
            return response()->json(['status' => 'Update successful'], 202);                                
        }

        return $this->response->error('Unable to update all data', 200);
    }

    public function adminStore(AdminCreateUser $request, JWTAuth $JWTAuth)
    {
      
        $data = $request->only(['username', 'email', 'first_name', 'last_name', 'middle_name', 'phone']);

        $data['password'] = $request->username . Carbon::now(1);

        $data['is_reader'] = 1;
        $data['is_active'] = 1;

        $user = new User($data);

        try {
            if ($auth_user = UserController::getAuthUser($JWTAuth)) {
                $user->created_by = $auth_user->id;
            }
        } catch (\Exception $e) {
        }

        //save $user;
        if (!$user->save()) {
            throw new StoreResourceFailedException(500);
        }

        AdminCreatedUser::create(['user_id' => $user->id]);
        //assign the user role
        $user->assignRole(Role::findByName('reader', 'api'));


        $data['user_id'] = $user->id;

        $details = new Reader($data);

        if (!$details->save()) {
            $user->delete();
            throw new StoreResourceFailedException(500);
        }
//        valid user
        $reset_token = $user->username . strtotime(Carbon::now(1));
        $reset_token = md5($reset_token);
        $reset_link = $request->frontend_url . '/' . $reset_token;

        PasswordReset::create([
            'email' => $user->email,
            'token' => $reset_token
        ]);
//        mail the reset link to user
        event(new SendPasswordResetLinkEmail($user, $reset_link));

        return response()->json(['status' => 'ok'], 201);
    }
}
