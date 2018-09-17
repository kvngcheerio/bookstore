<?php

namespace App\Api\V1\Controllers;

use Dingo\Api\Http\Response;
use Illuminate\Http\Request;
use App\Api\V1\Models\Role;
use App\Api\V1\Models\Permission;
use App\Api\V1\Requests\PermissionRequest;
use Dingo\Api\Exception\DeleteResourceFailedException;
use App\Http\Controllers\PermissionController as BasePermissionController;

class PermissionController extends BasePermissionController
{
    /**
     * get all permissions.
     *
     * @return json
     */
    public function index()
    {
        return Permission::all();
    }

    /**
     * Store a newly created permission in storage.
     *
     * @param  App\Api\V1\Requests\PermissionRequest $request
     * @return json
     */
    public function store(PermissionRequest $request)
    {
        $this->validate($request, [
            'permission_name' => 'required|string'
        ]);

        $permissions = $this->generatePermissions($request->permission_name);
        
        // create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission ]);
        }
        // sync role for super_admin
        if ($role = Role::where('name', 'super_admin')->first()) {
            $role->syncPermissions(Permission::all());
        }
        return new Response(['status'=>  'Permissions ' . implode(', ', $permissions) . ' created.' ], 201);
    }

    /**
     * Remove the specified permission from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return json
     */
    public function destroy(Request $request)
    {
        $this->validate($request, [
            'permission_name' => 'required|string'
        ]);

        $permission_name = $request->permission_name;
        $permissions = $this->generatePermissions($permission_name);

        // remove permission
        if (Permission::where('name', 'LIKE', '%'. $this->getNameArgument($permission_name))->delete()) {
            // sync role for admin
            if ($role = Role::where('name', 'admin')->first()) {
                $role->syncPermissions(Permission::all());
            }
            return new Response(['status'=>  'Permissions ' . implode(', ', $permissions) . ' deleted.' ], 201);
        }
        
        throw new DeleteResourceFailedException('No permissions for ' . $this->getNameArgument($permission_name) .' found!');
    }

    /**
    * protected function to generate permissions based on name. eg user generates view_users, add_users etc
    *
    * @param string $permission_name
    *
    * @return array
     */
    protected function generatePermissions(string $permission_name)
    {
        $abilities = ['view', 'add', 'edit', 'delete'];
        $name = $this->getNameArgument($permission_name);

        return array_map(function ($val) use ($name) {
            return $val . '_'. $name;
        }, $abilities);
    }
    /**
    * protected function to generate plural lower case of permission name
    *
    * @param string $permission_name
    *
    * @return string
     */
    protected function getNameArgument(string $permission_name)
    {
        return strtolower(str_plural($permission_name));
    }
}
