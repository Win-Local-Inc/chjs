<?php

namespace WinLocalInc\Chjs;

use Illuminate\Support\ServiceProvider;
use WinLocalInc\Chjs\Console\MaxioSync;

class ChjsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'chjs');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'chjs');

        $this->loadMigrationsFrom(__DIR__.'/../Database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/chjs.php' => config_path('chjs.php'),
            ], 'config');

            $this->publishes([
                __DIR__.'/../Database/migrations' => $this->app->databasePath('migrations'),
            ], 'chjs-migrations');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/chjs'),
            ], 'views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/chjs'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/chjs'),
            ], 'lang');*/

            // Registering package commands.
            if ($this->app->runningInConsole()) {
                $this->commands([
                    MaxioSync::class,
                ]);
            }
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/chjs.php', 'chjs');

        // Register the main class to use with the facade
        //        $this->app->singleton(Chjs::class, function () {
        //            return new ChjsBase();
        //        });

        $this->app->singleton(ChjsBase::class, function () {
            return new ChjsBase(
                hostname: config('chjs.hostname'),
                apiKey: config('chjs.api_key'),
                timeout: config('chjs.timeout')
            );
        });

        $this->app->singleton(Chjs::class, function () {
            return new Chjs(
                hostname: config('chjs.hostname'),
                apiKey: config('chjs.api_key'),
                timeout: config('chjs.timeout')
            );
        });
    }
}
