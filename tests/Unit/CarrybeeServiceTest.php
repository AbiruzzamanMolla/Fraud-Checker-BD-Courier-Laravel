<?php

namespace Azmolla\FraudCheckerBdCourier\Tests\Unit;

use Azmolla\FraudCheckerBdCourier\Tests\TestCase;
use Azmolla\FraudCheckerBdCourier\Services\CarrybeeService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

class CarrybeeServiceTest extends TestCase
{
    public function test_carrybee_successful_fetch()
    {
        $phone = '01711111111';

        Config::set('fraud-checker-bd-courier.carrybee.phone', '+8801787350229');
        Config::set('fraud-checker-bd-courier.carrybee.password', '2pWdRmwF');

        Http::fake([
            'https://merchant.carrybee.com/api/auth/csrf' => Http::response([
                'csrfToken' => 'fake_csrf_123'
            ], 200),

            'https://merchant.carrybee.com/api/auth/callback/login?' => Http::response([], 200),

            'https://merchant.carrybee.com/api/auth/session' => Http::response([
                'accessToken' => 'fake_access_token_abc',
                'user' => [
                    'selectedBusinessId' => '15834'
                ]
            ], 200),

            "https://api-merchant.carrybee.com/api/v2/businesses/15834/fraud-check/{$phone}" => Http::response([
                'error' => false,
                'message' => 'Customer fraud check',
                'data' => [
                    'phone' => '8801711111111',
                    'total_order' => 10,
                    'cancelled_order' => 2,
                    'success_rate' => 80
                ]
            ], 200),
        ]);

        $service = new CarrybeeService();
        $result = $service->getDeliveryStats($phone);

        $this->assertEquals([
            'success' => 8,
            'cancel' => 2,
            'total' => 10,
            'success_ratio' => 80.0,
        ], $result);
    }

    public function test_carrybee_failed_login()
    {
        $phone = '01711111111';

        Config::set('fraud-checker-bd-courier.carrybee.phone', '+8801787350229');
        Config::set('fraud-checker-bd-courier.carrybee.password', 'wrong_password');

        Http::fake([
            'https://merchant.carrybee.com/api/auth/csrf' => Http::response([
                'csrfToken' => 'fake_csrf_123'
            ], 200),

            'https://merchant.carrybee.com/api/auth/callback/login?' => Http::response([], 401),
        ]);

        $service = new CarrybeeService();
        $result = $service->getDeliveryStats($phone);

        $this->assertEquals([
            'error' => 'Login failed or unable to get access token from Carrybee',
        ], $result);
    }
}
