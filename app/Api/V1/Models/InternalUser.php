<?php

namespace App\Api\V1\Models;

use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Api\V1\Traits\SearchableByDateRange;

class InternalUser extends Model
{
    use SearchableByDateRange;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'job_title', 'employed_date'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['created_at', 'updated_at'];


    protected $appends = [
         'username', 'email', 'first_name', 'last_name',
        'middle_name', 'phone', 'is_active', 'is_online', 'picture', 'role'
    ];

    private $auth_details = false;

    public static function boot()
    {
        parent::boot();

    
    }

    //relationships
    public function user()
    {
        return $this->belongsTo('App\Api\V1\Models\User');
    }
   
    public function userObject()
    {
        return User::where('id', $this->user_id)->first();
    }

    private function getAuth()
    {
        if (!$this->auth_details) {
            $this->auth_details = User::where('id', $this->user_id)->first()->toArray();
        }
        return $this->auth_details;
    }

  
    public function getEmailAttribute()
    {
        return $this->getAuth()['email'];
    }

    public function getFirstNameAttribute()
    {
        return $this->getAuth()['first_name'];
    }

    public function getLastNameAttribute()
    {
        return $this->getAuth()['last_name'];
    }

    public function getMiddleNameAttribute()
    {
        return $this->getAuth()['middle_name'];
    }

    public function getPhoneAttribute()
    {
        return $this->getAuth()['phone'];
    }

    public function getIsActiveAttribute()
    {
        return $this->getAuth()['is_active'];
    }

    public function getIsOnlineAttribute()
    {
        return $this->getAuth()['is_online'];
    }

    public function getUsernameAttribute()
    {
        return $this->getAuth()['username'];
    }

    public function getPictureAttribute()
    {
        return Picture::where('id', $this->getAuth()['picture_id'])->first();
    }

    public function getRoleAttribute()
    {
        return $this->getAuth()['role'];
    }
}
