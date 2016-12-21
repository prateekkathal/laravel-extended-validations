<?php

namespace PrateekKathal\Validation;

use PrateekKathal\Validation\Factory;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\DatabasePresenceVerifier;
use PrateekKathal\Validation\Contracts\Factory as FactoryContract;

class ValidationServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

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
        $this->registerValidatorFactory();
    }

    /**
     * Registers Facade.
     *
     * @return \PrateekKathal\Validation\Validator
     */
    protected function registerValidatorFactory()
    {
        $this->app->singleton('extended-validator', function ($app) {
            return $this->getValidationFactory($app);
        });

        $this->app->bind(Factory::class, function($app) {
            return ($app['extended-validator']) ?: $this->getValidationFactory($app);
        });
    }

    /**
     * Get Validation Factory
     *
     * @param  \Illuminate\Foundation\Application $app
     *
     * @return \PrateekKathal\Validation\Validator
     */
    protected function getValidationFactory($app)
    {
        $validator = new Factory($app['translator'], $app);

        // The validation presence verifier is responsible for determining the existence
        // of values in a given data collection, typically a relational database or
        // other persistent data stores. And it is used to check for uniqueness.
        if (isset($app['validation.presence'])) {
            $validator->setPresenceVerifier($app['validation.presence']);
        }

        return $validator;
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'extended-validator'
        ];
    }
}
