<?php

namespace App\Api\V1\Requests;

use Config;
use Dingo\Api\Http\FormRequest;

class InternalUserEditRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return Config::get('apiauth.edit_account.validation_rules');
    }

    public function messages()
    {
        return [
            'employed_date.numeric' => 'Year of employment must be in digits',
            'employed_date.min' => 'Year of employment cannot be earlier than 1950'
        ];
    }
}
