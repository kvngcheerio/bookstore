<?php

namespace App\Providers;

use App\Helpers\Helper;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Cache\Factory;
use App\Api\V1\Models\MessageTemplate;

class MessageTemplateServiceProvider extends ServiceProvider
{
    /**
    * Bootstrap the application services.
    *
    * @param \Illuminate\Contracts\Cache\Factory $cache
    * @param \App\Api\V1\Models\Setting $messageTemplate
    *
    * @return void
    */
    public function boot(Factory $cache, MessageTemplate $messageTemplate)
    {
        
        //forget caching for now
        if (!Helper::isMigrationCommand()) {
        // if (!Helper::isMigrationCommand() && !App::runningInConsole()) {            
            $messageTemplate = $messageTemplate->get(['slug', 'subject', 'description', 'template'])->toArray();
            foreach ($messageTemplate as $oldKey => $value) {
                $newKey = $messageTemplate[$oldKey]['slug'];
                $messageTemplate[$newKey] = $messageTemplate[$oldKey];
                unset($messageTemplate[$oldKey]);
            }
            //set message_template to config
            config()->set('message_template', $messageTemplate);
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
