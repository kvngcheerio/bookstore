<?php

namespace App\Api\V1\Traits;

use Dingo\Api\Http\Response;
use Illuminate\Support\Facades\Validator;
use Dingo\Api\Exception\StoreResourceFailedException;

trait HandlesValidation
{
    public function isValid($request, $rules = [], $messages = [])
    {
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return new Response([
                    'error' => [
                        'message'=> 'validation failed',
                        'status_code' => 401,
                        'errors' => $validator->errors()
                        ]], 401);
        }
    }
}
