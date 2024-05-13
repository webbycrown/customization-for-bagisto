<?php

namespace Webbycrown\Customization\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;

class CustomizedServiceProvider extends ServiceProvider
{
    /**
     * Register your middleware aliases here.
     *
     * @var array
     */
    protected $middlewareAliases = [
        'sanctum.admin'    => \Webbycrown\Customization\Http\Middleware\AdminMiddleware::class,
        'sanctum.customer' => \Webbycrown\Customization\Http\Middleware\CustomerMiddleware::class,
        'sanctum.locale'   => \Webbycrown\Customization\Http\Middleware\LocaleMiddleware::class,
        'sanctum.currency' => \Webbycrown\Customization\Http\Middleware\CurrencyMiddleware::class,
    ];

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(Router $router)
    {
        $this->activateMiddlewareAliases();
        
        $this->loadRoutesFrom(__DIR__ . '/../Routes/admin-routes.php');

        $this->loadRoutesFrom(__DIR__ . '/../Routes/shop-routes.php');

        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'wc_customization');

        Blade::anonymousComponentPath(__DIR__.'/../Resources/views', 'wc_customization');

    }
    
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConfig();
    }

    /**
     * Activate middleware aliases.
     *
     * @return void
     */
    protected function activateMiddlewareAliases()
    {
        collect($this->middlewareAliases)->each(function ($className, $alias) {
            $this->app['router']->aliasMiddleware($alias, $className);
        });
    }
    
    /**
     * Register package config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Config/menu.php', 'menu.admin'
        );

        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Config/system.php', 'core'
        );
        
    }

}
