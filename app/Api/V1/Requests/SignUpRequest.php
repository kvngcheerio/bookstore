<?php

namespace App\Api\V1\Requests;

use Config;
use Dingo\Api\Http\FormRequest;

class SignUpRequest extends FormRequest
{

    public function rules()
    {
        return Config::get( 'apiauth.sign_up.validation_rules' );
    }

    public function authorize()
    {
        return true;
    }

    public function messages()
    {
        return [
            'employed_date.numeric' => 'Year of employment must be in digits',
            'employed_date.min'=> 'Year of employment cannot be earlier than 1950'
        ];
    }
}
