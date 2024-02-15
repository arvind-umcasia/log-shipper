<?php

namespace ArvindUmcasia\LogShipper;

use Illuminate\Support\ServiceProvider;
use ArvindUmcasia\LogShipper\Logging\LogShipLogger;

class LogShipperServiceProvider extends ServiceProvider
{
    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/logshipper.php', 'logshipper');

        $this->app->singleton('logshipper.log', function ($app) {
            return new LogShipLogger('log-shipper');
        });
    }

    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \ArvindUmcasia\LogShipper\Commands\LogShipperShipCommand::class,
            ]);
        }
    
        $this->publishes([
            __DIR__.'/../config/logshipper.php' => config_path('logshipper.php'),
        ], 'config');
    }
}
