<?php

namespace App\Api\V1\Requests;

use Config;
use Dingo\Api\Http\FormRequest;

class AddUserRequest extends FormRequest
{
    public function rules()
    {
        return Config::get('apiauth.add_user.validation_rules');
    }

    public function authorize()
    {
        return true;
    }
}
