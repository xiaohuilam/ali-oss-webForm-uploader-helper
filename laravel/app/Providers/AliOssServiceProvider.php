<?php

namespace App\Providers;

use App\Services\AliOssService;
use Illuminate\Support\ServiceProvider;

class aliOssServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('aliOss', function($app){
            return new AliOssService();
        });
    }
}
