<?php

namespace App\Providers;

use App\Helpers\Helper;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use App\Api\V1\Models\Setting;
use Illuminate\Contracts\Cache\Factory;

class SettingsServiceProvider extends ServiceProvider
{
    /**
    * Bootstrap the application services.
    *
    * @param \Illuminate\Contracts\Cache\Factory $cache
    * @param \App\Api\V1\Models\Setting $settings
    *
    * @return void
    */
    public function boot(Factory $cache, Setting $settings)
    {
        
        //forget caching for now
        if (!Helper::isMigrationCommand()) {
        // if (!Helper::isMigrationCommand() && !App::runningInConsole()) {            
            $settings = $settings->pluck('value', 'key')->all();
            config()->set('settings', $settings);
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
