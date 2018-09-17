<?php

namespace App\Api\V1\Requests;

use Config;
use Dingo\Api\Http\FormRequest;

class CreateBookRequest extends FormRequest
{
    public function rules()
    {
        return Config::get('apiauth.create_book.validation_rules');
    }

    public function authorize()
    {
        return true;
    }
}
