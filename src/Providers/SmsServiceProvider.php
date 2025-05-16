<?php

namespace RupeshDai\NepaliSmsGateway\Providers;

use Illuminate\Support\ServiceProvider;
use RupeshDai\NepaliSmsGateway\SmsManager;

class SmsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../Config/sms.php' => config_path('sms.php'),
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
            __DIR__ . '/../Config/sms.php', 'sms'
        );

        $this->app->singleton('sms', function ($app) {
            return new SmsManager($app);
        });

        $this->app->alias('sms', SmsManager::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['sms', SmsManager::class];
    }
}
