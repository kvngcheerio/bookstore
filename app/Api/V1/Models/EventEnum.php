<?php

namespace App\Api\V1\Models;

use Illuminate\Database\Eloquent\Model;

class EventEnum extends Model
{
    //
    protected $table = 'event_enums';

    protected $fillable = ['name'];

    public function events()
    {
        return $this->hasMany('App\Api\V1\Models\Event', 'event_enum_id');
    }
}
