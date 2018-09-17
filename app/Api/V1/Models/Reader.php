<?php

namespace App\Api\V1\Models;

use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;
use App\Api\V1\Traits\SearchableByDateRange;

class Reader extends Model
{
    use Filterable, SearchableByDateRange;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'picture_id',
        'admin_comment',
        'comment_by'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['comment_by', 'picture_id', 'created_at', 'updated_at'];

    protected $appends = [
        'picture', 'username', 'email', 'first_name', 'last_name', 'phone',
        'is_active', 'is_online',  'middle_name'
    ];

    private $auth_details = false;


    public function user()
    {
        return $this->belongsTo('App\Api\V1\Models\User');
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
        return Picture::where('id', $this->picture_id)->pluck('mime_type', 'seo_filename');
    }

  
   
}
