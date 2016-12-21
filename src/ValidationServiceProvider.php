<?php

namespace PrateekKathal\Validation;

use Illuminate\Support\ServiceProvider;
use PrateekKathal\Validation\Factory\Factory;

class ValidationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
      //
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->registerValidator();
    }

    /**
     * Registers Facade.
     *
     * @return SimpleCurl
     */
    private function registerValidator()
    {
        $this->app->bind('extended-validator', function ($app) {
            return new Factory($app['translator'], $app);
        });
    }
}
