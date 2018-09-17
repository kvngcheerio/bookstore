<?php

namespace App\Api\V1\Controllers;

use Config;
use Validator;
use Carbon\Carbon;
use Tymon\JWTAuth\JWTAuth;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use App\Api\V1\Models\User;
use App\Api\V1\Models\Role;
use App\Api\V1\Models\Reader;
use App\Api\V1\Models\Picture;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Api\V1\Models\InternalUser;
use App\Api\V1\Models\PasswordReset;
use App\Events\AdminUpdateUserDetail;
use App\Events\AccountDeletionRequest;
use App\Events\AdminUpdateUserPassword;
use App\Api\V1\Requests\AdminCreateUser;
use App\Api\V1\Models\InternalUserStatus;
use App\Exceptions\UserNotFoundException;
use App\Events\SendPasswordResetLinkEmail;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Exception\DeleteResourceFailedException;
use Dingo\Api\Exception\UpdateResourceFailedException;
use App\Api\V1\Requests\AdminEditUserRequest;
use App\Api\V1\Requests\ChangePasswordRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UserController extends Controller
{

   

    public static function getAuthUser(JWTAuth $jwt_auth)
    {
        if ($jwt_auth) {
            return $jwt_auth->parseToken()->toUser();
        }

        return false;
    }

    /**
     * index method to get return all users
     *
     * @return json $users
     */
    public function index()
    {

    }
    /**
     * get all users
     *
     * @return User
     */
    public function getAllNoPaginate()
    {
        $users = User::all();
        return $users->count() ? $users : [];
    }

   

    public function getReaders(Request $request)
    {

        $search = null;
        if ($request->input('search') != null) {
            $search = $request->input('search');
        }
        $users = Reader::query();
        if (!is_null($search)) {
            $users = $users->where(function ($query) use ($search) {
                $query->Where('user_id', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($newQuery) use ($search) {
                        $newQuery->where('email', 'like', '%' . $search . '%')
                            ->orWhere('first_name', 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%');
                    });
            });
        }

        //date range
        $users = Reader::searchByDate($request, $users);

        $users = $users->latest();


        if ($users->count() > 0) {
            $users = $users->paginate();
            return $users;
        }

        return [];
    }


    public function getInternalUsers(JWTAuth $auth, Request $request)
    {
        $admin = UserController::getAuthUser($auth);

        $det = $this->_getAdminDetails($admin);
        if (!$det) {
            $this->response->errorForbidden("Access denied for logged in user. Please login as an admin.");
        }

        $search = null;
        if ($request->input('search') != null) {
            $search = $request->input('search');
        }
        $users = InternalUser::query();
        if (!is_null($search)) {
            $users = $users->where(function ($query) use ($search) {
                $query->where('job_title', 'like', '%' . $search . '%');
            });
        }

        //date range        
        $users = InternalUser::searchByDate($request, $users);

        if ($admin->isSuperAdmin()) {
            if ($users = $users->latest()->paginate()) {
                return $users;
            }
        } 

        return [];
    }

    public function getUserByUsername($username)
    {
        if (User::where('username', $username)->first()) {
            return $this->response->accepted(null, ['status' => 'user exists']);
        }
        return $this->response->errorNotFound("User not fond");
    }

    public function getUserByEmail($email)
    {
        if (User::where('email', $email)->first()) {
            return $this->response->accepted(null, ['status' => 'user exists']);
        }
        return $this->response->errorNotFound("User not fond");
    }

    /**
     * @param $id
     * @param null $user
     */
    public function getUserDetailForAdmin(JWTAuth $auth, $id, $user = null)
    {

        $admin = self::getAdminDetails(self::getAuthUser($auth));
        if (!$admin) {
            return $this->response->errorForbidden("Not enough permission.");
        }

        if ($user == null) {
            $user = User::where('id', $id)->first();
        }

        if (!$user) {
            return $this->response->error('User details not found.', 404);
        }

        if ($user->is_reader) {
            $type = Reader::where('user_id', $user->id)->first();
        } else {
            $type = self::getAdminDetails($user);
            if ($type && !$admin->userObject()->isSuperAdmin() )
                return $this->response->errorUnauthorized("You do not access enough permission to access this user's information");
        }

        if ($type) {
            $detail = $type->toArray();
        } else {
            $detail = [];
        }

        return $this->response->array($detail);
    }

    /**
     * delete user
     *
     * @return json response
     */
    public function destroy(JWTAuth $JWTAuth, $id)
    {
        $user = self::getAuthUser($JWTAuth);

        if ($user->id == $id) {
            //tell current user they can't delete themselves
            throw new DeleteResourceFailedException('You cannot delete yourself');
        }

        $user = User::where('id', $id)->first();
        if (!$user) {
            return $this->response->errorNotFound("User not found!");
        }

        $isReader = false;
        if ($user->is_reader) {
            $detl = Reader::where('user_id', $id)->first();
            $isReader = true;
        } else {
            $detl = InternalUser::where('user_id', $id)->first();
        }

        if ($user->delete()) {
            if ($detl) {
                $detl->delete();
            }
            return new Response(['status' => 'User deleted'], 201);
        }
        //user not deleted
        throw new DeleteResourceFailedException('Delete request failed');
    }

    public function changePassword(JWTAuth $JWT_auth, ChangePasswordRequest $request)
    {
        $user = UserController::getAuthUser($JWT_auth);
        if (!$user) {
            return $this->response->errorUnauthorized("User account not accessible.");
        }

        if (!password_verify($request->old_password, $user->password)) {
            return $this->response->errorForbidden("Invalid credential.");
        }

        if ($user->update(['password' => $request->password])) {

            return $this->response->accepted(null, ["status" => "Password updated successfully."]);
        }

        throw new UpdateResourceFailedException("Unable to update user's password at the moment, try again");
    }

    /**
     * @param JWTAuth $JWT_auth
     * @param Request $request
     * @param $id
     * @return Response|\Illuminate\Http\JsonResponse|void
     */
    public function adminChangePassword(JWTAuth $JWT_auth, Request $request, $id)
    {
        $admin = UserController::getAuthUser($JWT_auth);
        if (!$admin) {
            return $this->response->errorUnauthorized("Access denied.");
        }

        $validator = Validator::make($request->all(), [
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'errors' => $validator->errors(),
                    'message' => '422 Unprocessable Entity'
                ]
            ], 422);
        }
        $user = User::where('id', $id)->first();
        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }
        if ($user->update(['password' => $request->password])) {
            event(new AdminUpdateUserPassword($admin, $user));

            return $this->response->accepted(null, ["status" => "User's password updated"]);
        }

        throw new UpdateResourceFailedException("Unable to update user's password at the moment.");
    }

    /**
     * @param User $user
     * @param JWTAuth $JWTAuth
     */
    public function showAuthenticatedUser(JWTAuth $JWTAuth)
    {
        $user = UserController::getAuthUser($JWTAuth);
        if (!$user) {
            throw new UserNotFoundException();
        }
        if ($user->is_reader) {
            $type = Reader::where('user_id', $user->id)->first();
        } else {
            $type = self::getAdminDetails($user);
        }

        return $type;
    }

    



    /**
     * protected function to handle syncing permissions
     *
     */
    protected function syncPermissions(Request $request, $user)
    {
        // Get the submitted roles
        $roles = $request->get('roles', []);
        $permissions = $request->get('permissions', []);

        // Get the roles
        $roles = Role::find($roles);

        // check for current role changes
        if (!$user->hasAllRoles($roles)) {
            // reset all direct permissions for user
            $user->permissions()->sync([]);
        } else {
            // handle permissions
            $user->syncPermissions($permissions);
        }

        $user->syncRoles($roles);

        return $user;
    }

    /**
     * quick function to get user by id
     *
     * @param string $id
     *
     * @return User
     */
    protected function getUserById($id)
    {
        return User::find($id);
    }

    private function _getAdminDetails($admin)
    {
        return self::getAdminDetails($admin);
    }

    public static function getAdminDetails($admin)
    {
        if (!($admin instanceof User) || $admin->is_reader) return false;
        return InternalUser::where('user_id', $admin->id)->first();
    }

    /**
     * user cancel/delete account request
     * 
     * @param \Dingo\Api\Http\Request $request
     * @param \Tymon\JWTAuth\JWTAuth $JWTAuth
     * 
     * @return response 
     */
    public function cancelAccount(Request $request, JWTAuth $JWTAuth)
    {
        if (!$user = self::getAuthUser($JWTAuth)) {
            throw new BadRequestHttpException('User account not found');
        }
        //try to authenticate the user with provided password
        $credentials = $request->only(['password']);
        $credentials['id'] = $user->id;
        try {
            $token = $JWTAuth->attempt($credentials);

            if (!$token) {
                throw new AccessDeniedHttpException('Cancel request failed, invalid password.');
            }
        } catch (JWTException $e) {
            return $this->response->errorBadRequest($e->getMessage());
        }

        $user->delete_request = 1;
        if ($user->save()) {
            //fire event
            event(new AccountDeletionRequest($user));
            //notify admin            
           

            return response()->json(['status' => 'Your account was cancelled successfully'], 201);
        }
        //user not cancelled
        throw new DeleteResourceFailedException('Cancel request failed');
    }
}
