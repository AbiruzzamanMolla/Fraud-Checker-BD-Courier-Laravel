<?php

namespace Azmolla\FraudCheckerBdCourier\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Azmolla\FraudCheckerBdCourier\FraudCheckerBdCourierServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            FraudCheckerBdCourierServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Mock the required configs to bypass configuration exception
        $app['config']->set('fraud-checker-bd-courier.steadfast.user', 'fake_email@test.com');
        $app['config']->set('fraud-checker-bd-courier.steadfast.password', 'fake_password');

        $app['config']->set('fraud-checker-bd-courier.pathao.user', 'fake_user');
        $app['config']->set('fraud-checker-bd-courier.pathao.password', 'fake_password');

        $app['config']->set('fraud-checker-bd-courier.redx.phone', '01700000000');
        $app['config']->set('fraud-checker-bd-courier.redx.password', 'fake_password');
    }
}
