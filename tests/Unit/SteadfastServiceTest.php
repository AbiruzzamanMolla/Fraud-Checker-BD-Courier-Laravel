<?php

namespace Azmolla\FraudCheckerBdCourier\Tests\Unit;

use Azmolla\FraudCheckerBdCourier\Tests\TestCase;
use Azmolla\FraudCheckerBdCourier\Services\SteadfastService;
use Illuminate\Support\Facades\Http;

class SteadfastServiceTest extends TestCase
{
    public function test_steadfast_successful_fetch()
    {
        $phone = '01711111111';

        Http::fake([
            'https://steadfast.com.bd/login' => Http::sequence()
                ->push('<input type="hidden" name="_token" value="fake_csrf_123">', 200)
                ->push('Login Success', 302),

            "https://steadfast.com.bd/user/frauds/check/{$phone}" => Http::response([
                'total_delivered' => 5,
                'total_cancelled' => 2,
            ], 200),

            'https://steadfast.com.bd/user/frauds/check' => Http::response(
                '<meta name="csrf-token" content="logout_csrf_123">',
                200
            ),

            'https://steadfast.com.bd/logout' => Http::response('Logged out', 200),
        ]);

        $service = new SteadfastService();
        $result = $service->getDeliveryStats($phone);

        $this->assertEquals([
            'success' => 5,
            'cancel' => 2,
            'total' => 7,
            'success_ratio' => 71.43,
        ], $result);
    }
}
