<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Contracts\Cache\Factory;
use App\Api\V1\Models\Setting;
use App\Http\Controllers\Controller;
use App\Api\V1\Requests\SettingRequest;
use Dingo\Api\Exception\StoreResourceFailedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SettingController extends Controller
{
    public function index()
    {
        if ($settings = Setting::all()) {
            return $settings;
        }
        
        throw new NotFoundHttpException('No setting found');
    }
 
    /**
    * Updates the settings.
    *
    * @param \App\Api\V1\Requests\SettingRequest   $request
    * @param \Illuminate\Contracts\Cache\Factory   $cache
    * @param \App\Api\V1\Models\Setting   $setting
    * @param int $id
    *
    * @return json
    */
    public function update(SettingRequest $request, Factory $cache, Setting $setting, $id)
    {
        // update settings
        if (! $setting = $setting->find($id)) {
            throw new NotFoundHttpException('No setting found');
        }
        if (! $setting->update($request->only('value'))) {
            throw new StoreResourceFailedException('setting update failed');
        }

        // When the settings have been updated, clear the cache for the key 'settings':
        $cache->forget('settings');

        // E.g., redirect back to the settings index page with a success flash message
        return new Response(['status'=>  'setting updated successfully'], 201);
    }

    //https://stackoverflow.com/questions/34126578/laravel-set-global-variable-from-settings-table
}
