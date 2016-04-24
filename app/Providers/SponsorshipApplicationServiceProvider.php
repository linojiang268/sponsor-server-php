<?php
namespace Sponsor\Providers;

use Illuminate\Support\ServiceProvider;

class SponsorshipApplicationServiceProvider extends ServiceProvider
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
            \Sponsor\Contracts\Repositories\SponsorshipApplicationRepository::class,
            \Sponsor\Repositories\SponsorshipApplicationRepository::class
        );
    }
    
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
           \Sponsor\Contracts\Repositories\SponsorshipApplicationRepository::class,
        ];
    }
}
