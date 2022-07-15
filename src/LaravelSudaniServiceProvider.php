<?php

namespace AlkhatibDev\LaravelSudani;

use Illuminate\Support\ServiceProvider;

class LaravelSudaniServiceProvider extends ServiceProvider {

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

        $this->app->singleton('Sudani', function ($app) {
            return new \AlkhatibDev\LaravelSudani\Sudani();
        });

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishResources();
    }

    protected function publishResources()
    {
        // Publish Configs
        $this->publishes([
            __DIR__.'/../config/laravel-sudani.php' => config_path('laravel-sudani.php'),
        ], 'laravel-sudani-config');
    }

}
