<?php

namespace App\Api\V1\Models;

use Illuminate\Database\Eloquent\Model;

class AccountActivation extends Model
{

    protected $table = 'account_activations';
        
    protected $fillable = ['email', 'token'];
}
