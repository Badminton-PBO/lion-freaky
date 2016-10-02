<?php namespace App\Providers;

use App\Http\Controllers\Auth\ToernooiNlUserProvider;
use App\User;
use Illuminate\Support\ServiceProvider;

class ToernooiNlAuthProvider extends ServiceProvider {

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['auth']->extend('toernooinl',function()
        {
            return new ToernooiNlUserProvider(new User("",""));
        });
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