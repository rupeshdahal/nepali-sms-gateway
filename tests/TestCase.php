<?php

namespace RupeshDai\NepaliSmsGateway\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use RupeshDai\NepaliSmsGateway\Providers\SmsServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            SmsServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Set up testing environment configurations
        $app['config']->set('sms.default', 'sparrow');
        $app['config']->set('sms.gateways.sparrow', [
            'token' => 'test-token',
            'from' => 'TEST',
        ]);

        $app['config']->set('sms.gateways.akash', [
            'auth_key' => 'akash-auth-key',
            'sender_id' => 'AKASHTEST',
        ]);

        $app['config']->set('sms.gateways.fast', [
            'api_key' => 'fast-api-key',
            'sender' => 'FASTTEST',
        ]);
    }
}
