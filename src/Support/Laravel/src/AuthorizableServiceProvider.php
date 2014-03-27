<?php

namespace JoshuaJabbour\Authorizable\Laravel;

use JoshuaJabbour\Authorizable\Manager as AuthorizableManager;
use Illuminate\Support\ServiceProvider;

class AuthorizableServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('joshuajabbour/authorizable', 'authorizable', __DIR__.'/..');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app['authorizable'] = $this->app->share(function ($app) {

            $authorizable = new AuthorizableManager($app['auth']->user());

            if (is_callable($initializer = $app['config']->get('authorizable::initialize', null))) {
                $initializer($authorizable);
            }

            return $authorizable;

        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('authorizable');
    }
}
