<?php

namespace App\Api\V1\Requests;

use Dingo\Api\Http\FormRequest;
use Config;

class SettingRequest extends FormRequest
{
    public function rules()
    {
        return Config::get('apiauth.setting.validation_rules');
    }

    public function authorize()
    {
        return true;
    }
}
