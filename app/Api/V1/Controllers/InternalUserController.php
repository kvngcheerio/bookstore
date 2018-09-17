<?php

namespace App\Api\V1\Controllers;

use Carbon\Carbon;
use Dingo\Api\Exception\StoreResourceFailedException;
use App\Api\V1\Models\AdminCreatedUser;
use App\Api\V1\Models\InternalUser;
use App\Api\V1\Models\PasswordReset;
use App\Api\V1\Models\Picture;
use App\Api\V1\Models\Role;
use App\Api\V1\Models\User;
use App\Api\V1\Requests\AdminCreateUser;
use App\Api\V1\Requests\AdminEditUserRequest;
use App\Api\V1\Requests\InternalUserEditRequest;
use App\Events\AdminUpdateUserDetail;
use App\Events\SendPasswordResetLinkEmail;
use Tymon\JWTAuth\JWTAuth;

use Validator;

class InternalUserController extends Controller
{

    /**
     * Gets authenticated user's detail for inclusion in JWTAuth token
     *
     * @param User $user
     * @param JWTAuth $JWT_auth
     * @internal param $id
     *
     */
    public function show(JWTAuth $JWT_auth)
    {
        $user = UserController::getAuthUser($JWT_auth);
        if (!$user || !($internalUser = InternalUser::where('user_id', $user->id)->first())) {
            return $this->response->error('Internal user authentication invalid', 404);
        }

        $detail = $internalUser->toArray();

        $job_titles = InternalUserStatus::all(['id', 'name', 'description']);

        return $this->response->array([
            'detail' => $detail,
            'job_titles' => $job_titles
        ]);
    }

    public function showAdminCreate()
    {
        //gets all pre-data for signup
        $roles = Role::all(['name']);
        $job_titles = InternalUserStatus::all(['id', 'name', 'description']);

        return $this->response->array([
            'roles' => $roles,
            'job_titles' => $job_titles
        ]);
    }

    /**
     * @param InternalUserEditRequest $request
     * @param JWTAuth $JWT_auth
     * @return \Dingo\Api\Http\Response|void
     */
    public function update(InternalUserEditRequest $request, JWTAuth $JWT_auth)
    {
        $user = UserController::getAuthUser($JWT_auth);
        if (!$user || !($internalUser = InternalUser::where('user_id', $user->id)->first())) {
            return $this->response->error('Internal User authentication invalid', 404);
        }

        if ($user->email != $request->email) {
            if (User::where('email', $request->email)->where('id', '!=', $user->id)->first()) {
                return $this->response->error('The email supplied has been taken, please choose another', 406);
            }
        }

        if ($user->phone != $request->phone) {
            if (User::where('phone', $request->phone)->where('id', '!=', $user->id)->first()) {
                return $this->response->error('The phone number supplied has been taken.', 406);
            }
        }

        $userUpdated = $user->update($request->only(['email', 'first_name', 'last_name', 'middle_name', 'phone']));

    
        $data = $request->only('employed_date','job_title');

        $picture_id = 0;

        if ($user->picture_id != null) {
            $picture_id = $user->picture_id;
        }

        if ($request->hasFile('logo')) {
            $image = $request->file('logo');
            $name = time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('/logos');
            $image->move($destinationPath, $name);

            $picture = Picture::updateOrCreate(['id' => $picture_id], [
                'seo_filename' => $name,
                'mime_type' => $image->getClientOriginalExtension()
            ]);

            $data['picture_id'] = $picture->id;
        }

        $internalUserUpdated = $internalUser->update($data);

        if ($userUpdated && $internalUserUpdated) {
            return $this->response->accepted(null, ['status' => "updated"]);
        }

        return $this->response->error('Unable to update all data', 200);
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
        $details = InternalUser::where('user_id', $user->id)->first();

        if (!$user || !$details) {
            return $this->response->errorNotFound('The user\'s details are missing.');
        }

        if (!$admin->isSuperAdmin()) {
            return $this->response->errorUnauthorized('Permission not granted for this action.');
        }

        $validator = Validator::make($request->all(), [
            'employed_date' => 'numeric|min:1950',
            'job_title' => 'required|string',
            'role' => 'required',
        ], [
            'employed_date.numeric' => 'Year of employment must be in digits',
            'employed_date.min' => 'Year of employment cannot be earlier than 1950',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'errors' => $validator->errors(),
                    'message' => '422 Unprocessable Entity'
                ]
            ], 422);
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

        $data = $request->only('employed_date', 'job_title');

        if ($request->hasFile('logo')) {
        }

        $internal_details_updated = $details->update($data);

        if ($user_updated && $internal_details_updated) {
            event(new AdminUpdateUserDetail($admin, $user));
            return $this->response->accepted(null, ['Status' => 'Update successful', 'status_code' => 202]);
        }

        return $this->response->error('Unable to update all data', 200);
    }

    public function adminStore(AdminCreateUser $request, JWTAuth $JWTAuth)
    {
        $validator = Validator::make($request->all(), [
            'job_title' => 'required|string',
            'employed_date' => 'numeric|min:1950',
            'role' => 'required'
        ], [
            'employed_date.numeric' => 'Year of employment must be in digits',
            'employed_date.min' => 'Year of employment cannot be earlier than 1950',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'errors' => $validator->errors(),
                    'message' => '422 Unprocessable Entity'
                ]
            ], 422);
        }

        $role_ = Role::findByName($request->role, 'api');

        $data = $request->only(['username', 'email', 'first_name', 'last_name', 'middle_name', 'phone']);

        $data['password'] = $request->username . Carbon::now(1);
        $data['is_active'] = 1;

        $auth_user = UserController::getAuthUser($JWTAuth);
        $data['created_by'] = $auth_user->id;

        $user = new User($data);

        //save $user;
        if (!$user->save()) {
            throw new StoreResourceFailedException(500);
        }

        AdminCreatedUser::create(['user_id' => $user->id]);
        //assign the user role
        $user->assignRole($role_);

        $data = $request->only('employed_date', 'job_title');
        $data['user_id'] = $user->id;

        $details = new InternalUser($data);

        if (!$details->save()) {
            $user->delete();
            throw new StoreResourceFailedException(500);
        }
        //valid user
        $reset_token = $user->username . strtotime(Carbon::now(1));
        $reset_token = md5($reset_token);
        $reset_link = $request->frontend_url . '/' . $reset_token;

        PasswordReset::create([
            'email' => $user->email,
            'token' => $reset_token
        ]);
        //mail the reset link to user
        $user->reset_token = $reset_token;

        event(new SendPasswordResetLinkEmail($user, $reset_link));

        return response()->json(['status' => 'ok'], 201);
    }
}
