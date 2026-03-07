<?php

namespace Azmolla\FraudCheckerBdCourier;

use Illuminate\Support\ServiceProvider;
use Azmolla\FraudCheckerBdCourier\Services\SteadfastService;
use Azmolla\FraudCheckerBdCourier\Services\PathaoService;
use Azmolla\FraudCheckerBdCourier\Services\RedxService;
use Azmolla\FraudCheckerBdCourier\FraudCheckerBdCourierManager;

/**
 * Class FraudCheckerBdCourierServiceProvider
 *
 * Registers the package services and merges configurations into the Laravel container.
 *
 * @package Azmolla\FraudCheckerBdCourier
 */
class FraudCheckerBdCourierServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish the config file on vendor:publish
        $this->publishes([
            __DIR__ . '/../config/fraud-checker-bd-courier.php' => config_path('fraud-checker-bd-courier.php'),
        ], 'config');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/fraud-checker-bd-courier.php',
            'fraud-checker-bd-courier'
        );

        $this->app->singleton('fraud-checker-bd-courier', function ($app) {
            return new FraudCheckerBdCourierManager(
                $app->make(SteadfastService::class),
                $app->make(PathaoService::class),
                $app->make(RedxService::class)
            );
        });
    }
}
