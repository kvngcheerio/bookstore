<?php

namespace App\Api\V1\Controllers;

use Dingo\Api\Http\Response;
use Illuminate\Http\Request;
use App\Api\V1\Models\Role;
use Illuminate\Support\Facades\Validator;
use App\Api\V1\Models\Permission;
use App\Api\V1\Requests\RoleRequest;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Exception\DeleteResourceFailedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Http\Controllers\RoleController as BaseRoleController;
use Exception;

class RoleController extends BaseRoleController
{
    /**
     * Display a listing of roles.
     *
     * @param App\Api\V1\Models\Role $role
     * @param App\Api\V1\Models\Permission $permission
     *
     * @return json
     */
    public function index(Role $role, Permission $permission, Request $request)
    {
        $search = '';
        if ($request->input('search') != null) {
            $search = $request->input('search');
        }
        $roles = $role->where('name', 'like', '%' . $search . '%')
                        //->makeHidden('pivot')
            ->with('permissions');
        
        //date range
        $roles = Role::searchByDate($request, $roles);

        $roles = $roles->get();

        $permissions = $permission->all();

        return $this->response->array(compact('roles', 'permissions'));
    }

    /**
     * Store a newly created role in storage.
     *
     * @param  \App\Api\V1\Requests\RoleRequest  $request
     * @param  \App\Api\V1\Models\Role  $role
     * @param  \App\Api\V1\Models\Permission  $permission
     *
     * @return json
     */
    public function store(RoleRequest $request, Role $role, Permission $permission)
    {
        $this->validate($request, ['name' => 'required|unique:roles']);

        if ($role = $role->create($request->only('name'))) {
            $permissions = $request->get('permissions', []);

            //verify permissions to make sure selected permissions are in db
            if (!$permission->areValidPermissions($permissions)) {
                throw new StoreResourceFailedException('We don\'t recognize some of the permissions you set');
            }
            if ($role->syncPermissions($permissions)) {
                return new Response(['status' => 'Role created'], 201);
            }
            throw new StoreResourceFailedException('Permissions couldn\'t be assigned');
        }
        throw new StoreResourceFailedException('Role couldn\'t be stored');
    }

    /**
     * Update the specified role by id in storage.
     * also sync the permissions in the request.
     * $request->permissions will be an array of permissions
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Api\V1\Models\Role  $role
     * @param  \App\Api\V1\Models\Permission  $permission
     * @param  string  $role_name
     * @return json
     */
    public function update(Request $request, Permission $permission, $role_name)
    {
        $this->validate($request, ['name' => 'required']);

        try {
            $role = Role::findByName($role_name);
        } catch (Exception $e) {
            throw new NotFoundHttpException('Role not found');
        }
        // admin role has everything
        /* if ($role->name === 'admin') {
            $role->syncPermissions(Permission::all());
            return new Response(['status'=>  'Permissions updated'], 201);
        } */
        //some roles can't be updated
        if ($role->isSpecialRole()) {
            if ($role->name === 'super_admin') {
                $role->syncPermissions(Permission::all());
            }
            throw new StoreResourceFailedException('This role can\'t be updated');
        }
        $permissions = $request->get('permissions', []);
        /* $permisssions = $permission->pluck('name')->toArray();
        return $this->response->array(array_intersect($permissions, $permisssions)); */

        //verify permissions to make sure selected permissions are in db
        if (!$permission->areValidPermissions($permissions)) {
            throw new StoreResourceFailedException('We don\'t recognize some of the permissions you set');
        }
        //if role name has been editted
        if ($role->name != $request->name) {
            //add another validation to make sure editted name doesn't
            //clash with other role names
            $array = [
                'name' => $request->name
            ];
            $validator = Validator::make($array, [
                'name' => 'unique:roles'
            ]);
            if ($validator->fails()) {
                return new Response([
                    'error' => [
                        'message' => 'validation failed',
                        'status_code' => 401,
                        'errors' => $validator->errors()
                    ]
                ], 401);
            }
        }
        //some role names cannot be changed
        if (!$role->canNotBeDeleted()) {
            //save the modified role name
            $role->name = $request->name;
        }
        if ($role->save()) {
            //save the modified permissions for the role
            $role->syncPermissions($permissions);
            return new Response(['status' => 'Roles & Permissions updated'], 201);
        }

        throw new StoreResourceFailedException('Role update failed');
    }

    /**
     * Display the specified role.
     *
     * @param  \App\Api\V1\Models\Role  $role
     * @param  string $role_name
     *
     * @return json|Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function show(Role $role, $role_name)
    {
        try {
            if ($role = $role->findByName($role_name)) {
                $permissions = $role->permissions;
                
                //return $this->response->array(compact('role', 'permissions'));
                return $role;
            }
        } catch (Exception $e) {
            throw new NotFoundHttpException('Role not found');
        }
    }
    /**
     * Remove the specified role from storage.
     *
     * @param  \App\Api\V1\Models\Role  $role
     * @param  int $id
     * @return json
     */
    public function destroy(Role $role, $role_name)
    {
        try {
            if (!$role = $role->findByName($role_name)) {
                throw new DeleteResourceFailedException('Role not found');
            }
        } catch (Exception $e) {
            throw new DeleteResourceFailedException('Role not found');
        }
        //some roles can't be deleted
        if ($role->canNotBeDeleted()) {
            throw new DeleteResourceFailedException('This role can\'t be deleted');
        }
        //delete role
        if ($role->delete()) {
            return new Response(['status' => 'Role deleted'], 201);
        }
        
        //Role not deleted
        throw new DeleteResourceFailedException('Delete request failed');
    }
}
