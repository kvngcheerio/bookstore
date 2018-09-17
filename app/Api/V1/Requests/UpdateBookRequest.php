<?php

namespace App\Api\V1\Requests;

use Dingo\Api\Http\FormRequest;
use Config;

class UpdateBookRequest extends FormRequest
{
    public function rules()
    {
        return Config::get('apiauth.update_book.validation_rules');
    }

    public function authorize()
    {
        return true;
    }
}
