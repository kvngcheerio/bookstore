<?php

namespace App\Api\V1\Models;

use Exception;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use App\Api\V1\Traits\HandlesEventLogging;
use App\Api\V1\Controllers\ReaderController;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Api\V1\Traits\HandlesUsersOnlineStatus;
use App\Api\V1\Controllers\InternalUserController;
use Tymon\JWTAuth\Contracts\JWTSubject as AuthenticatableUserContract;

/**
 * @property string email
 * @property string first_name
 * @property string last_name
 * @property string phone
 * @property string password
 */
class User extends Authenticatable implements AuthenticatableUserContract
{
    use Notifiable, HasRoles, HandlesUsersOnlineStatus, HandlesEventLogging;

    protected $guard_name = 'api';


    /* custom attributes we use from time to time. Not stored in database tho. */
    /* protected $email_subject = '';
	public function getEmailSubjectAttribute()
	{
		return $this->email_subject;
	}

	public function setEmailSubjectAttribute($value)
	{
		$this->email_subject = $value;
	} */

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'slug',
        'role_id',
        'first_name',
        'last_name',
        'middle_name',
        'phone',
        'is_active',
        'is_online',
        'is_reader',
        'picture_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'roles',
        'is_reader'
    ];

    protected $appends = ['role', 'picture'];

    /**
     * Automatically creates hash for the user password.
     *
     * @param  string $value
     *
     * @return void
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    /**
     * Automatically InitialCaps the user's first name.
     *
     * @param  string $value
     *
     * @return void
     */
    public function setFirstNameAttribute($value)
    {
        $this->attributes['first_name'] = ucfirst($value);
    }

    /**
     * Automatically InitialCaps the user's last name.
     *
     * @param  string $value
     *
     * @return void
     */
    public function setLastNameAttribute($value)
    {
        $this->attributes['last_name'] = ucfirst($value);
    }

    //relationships will be defined below
    public function role()
    {
        return $this->belongsTo('App\Api\V1\Models\Role', 'role_id');
    }

    public function internal_user()
    {
        return $this->hasOne('App\Api\V1\Models\InternalUser', 'user_id');
    }
    public function reader()
    {
        return $this->hasOne('App\Api\V1\Models\Reader', 'user_id');
    }
    public function reviews()
    {
        return $this->hasMany('App\Api\V1\Models\BookReview', 'user_id');
    }
  
 

  
    //based on https://github.com/tymondesigns/jwt-auth/issues/260#issuecomment-143683226
    public function getJWTIdentifier()
    {
        return $this->getKey();
        //return $this->slug;
    }

    public function getJWTCustomClaims()
    {
        return [
            'user' => [
                'username' => $this->username,
                'is_active' => $this->is_active,
                'roles' => $this->getRoleNames(),
                'permissions' => $this->getAllPermissions()->pluck('name')
            ]
        ];
    }

    public function getRoleAttribute()
    {
        return $this->getRoleNames();
    }

    public function getPictureAttribute()
    {
        return Picture::where('id', $this->picture_id)->pluck('mime_type', 'seo_filename');
    }

 

    public function isSuperAdmin()
    {

        return $this->hasRole('super_admin');

    }

    public function cancelledAccount()
    {
        return $this->delete_request;
    }
}
