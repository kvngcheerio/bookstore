<?php

namespace App\Api\V1\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends \Spatie\Permission\Models\Permission
{
    protected static $permissions = [
//        note: 0=>super_admin, 1=>admin, 2=>internal_user, 3=>reader
        'view_settings' => [0],
        'add_settings' => [0],
        'edit_settings' => [0],
        'delete_settings' => [0],


//        about roles and permissions
        'view_roles' => [0, 1], 'view_permissions' => [0, 1],
        'add_roles' => [0, 1], 'add_permissions' => [],
        'edit_roles' => [0, 1], 'edit_permissions' => [],
        'delete_roles' => [0, 1], 'delete_permissions' => [],

//        manage events
        'view_events' => [0],
        'add_events' => [0],
        'edit_events' => [0],
        'delete_events' => [0],

            


//        manage books review
        'view_books_review' => [0, 1, 2, 3],
        'add_books_review' => [0, 1, 2],
        'edit_books_review' => [0, 1, 2],
        'delete_books_review' => [0, 1, 2],

//        manage books
'view_books' => [0, 1, 2, 3],
'add_books' => [0, 1],
'edit_books' => [0, 1],
'delete_books' => [0, 1],      

//        user management
        "view_internal_users" => [0, 1],
        "view_readers" => [0, 1, 2],
        "delete_users" => [0, 1],
        "change_others_password" => [0, 1],
        "create_internal_users" => [0, 1],
        "create_readers" => [0],
        "update_readers" => [0],
        "update_internal_users" => [0, 1],

        "view_books_category" => [0, 1, 3],
        "update_books_category" => [0, 1],
        "create_books_category" => [0, 1],
        "delete_books_category" => [0, 1],

        "update_job_titles" => [0, 1, 2],
        "create_job_titles" => [0, 1, 2],
        "delete_job_titles" => [0, 1, 2],

       
        //books search
        "view_search" => [0, 1, 2,3],

      
          //pictures
        'add_pictures' => [0],
        'edit_pictures' => [0],
        'delete_pictures' => [0],
        'view_pictures' => [0],

    ];

    public static function defaultPermissions()
    {
        return self::getPerms(0);
    }

    public static function getAdminPermissions()
    {
        return self::getPerms(1);
    }

    public static function getInternalUserPermissions()
    {
        return self::getPerms(2);
    }

    public static function getReaderPermissions()
    {
        return self::getPerms(3);
    }

    /**
     * @return array
     */
    private static function getPerms($id)
    {
        $perms = [];
        foreach (self::$permissions as $key => $arr) {
            if (in_array($id, $arr)) {
                $perms[] = $key;
            }
        }
        return $perms;
    }

    //make sure the permissions sent are available in the db
    public function areValidPermissions(array $permissions = []) : bool
    {
        return $permissions === array_intersect($permissions, $this->pluck('name')->toArray());
    }
}
