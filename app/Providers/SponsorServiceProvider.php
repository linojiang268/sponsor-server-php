<?php
namespace Sponsor\Providers;

use Illuminate\Support\ServiceProvider;

class SponsorServiceProvider extends ServiceProvider
{
    
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;
    
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
    
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            \Sponsor\Contracts\Repositories\SponsorRepository::class,
            \Sponsor\Repositories\SponsorRepository::class
        );

        $this->app->when(\Sponsor\ApplicationServices\SponsorServices::class)
             ->needs('hash')
             ->give(\Sponsor\Hashing\PasswordHasher::class);
    }
    
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
           \Sponsor\Contracts\Repositories\SponsorRepository::class,
           \Sponsor\ApplicationServices\SponsorServices::class,
        ];
    }
}
