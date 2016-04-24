<?php

namespace Sponsor\Providers;

use Illuminate\Support\ServiceProvider;
use Auth;
use Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->extendValidator();
        $this->extendAuthManager();
    }

    private function extendAuthManager()
    {
        Auth::extend('extended-eloquent', function ($app) {
            // AuthManager allows us only provide UserProvider instead of
            // the whole Guard implmenetation
            return new \Sponsor\Auth\UserProvider(new \Sponsor\Hashing\PasswordHasher(),
                $app['config']['auth.model']);
        });

        Auth::extend('extended-eloquent-team', function ($app) {
            // AuthManager allows us only provide UserProvider instead of
            // the whole Guard implmenetation
            return new \Sponsor\Auth\TeamUserProvider(new \Sponsor\Hashing\PasswordHasher(),
                $app['config']['auth.model-team']);
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

    private function extendValidator()
    {
        Validator::extend('mobile', function($attribute, $value, $parameters) {
            return preg_match('/^1[34578]\d{9}$/', $value) > 0;
        });
        Validator::extend('phone', function($attribute, $value, $parameters) {
            return (preg_match('/^([0-9]{3,4}-)?[0-9]{7,8}$/', $value) > 0)
            || (preg_match('/^1[34578]\d{9}$/', $value) > 0);
        });
    }
}
