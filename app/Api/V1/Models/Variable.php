<?php

namespace App\Api\V1\Models;

use Illuminate\Database\Eloquent\Model;

class Variable extends Model
{
    protected $table = 'variables';
        
    protected $fillable = ['name', 'description'];

    public $timestamps = false;

    public function message_templates()
    {
        return $this->belongsToMany('App\Api\V1\Models\MessageTemplate');
    }

     //all variables that need to be created should be added here
    public static function defaultVariables()
    {
        return [
            ['name'=>'first_name', 'description'=>'A person\'s first name'],
            ['name'=>'last_name', 'description'=>'A person\'s last name'],
            ['name'=>'phone_number', 'description'=>'A person\'s phone number'],
            ['name'=>'email_address', 'description'=>'A person\'s email address'],
        ];
    }
}
