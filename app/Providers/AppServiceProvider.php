<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Access\AuthorizationException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //MySQL < v5.7.7 compability : https://laravel-news.com/laravel-5-4-key-too-long-error
        //Schema::defaultStringLength(191);

        //custom AuthorizationException message
        $this->app->make('api.exception')->register(function (AuthorizationException $e) {
            abort(403, "This action is unauthorized");
        });

       
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
