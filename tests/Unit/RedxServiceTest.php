<?php

namespace Azmolla\FraudCheckerBdCourier\Tests\Unit;

use Azmolla\FraudCheckerBdCourier\Tests\TestCase;
use Azmolla\FraudCheckerBdCourier\Services\RedxService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class RedxServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::clear();
    }

    public function test_redx_successful_fetch()
    {
        $phone = '01711111111';

        Http::fake([
            'https://api.redx.com.bd/v4/auth/login' => Http::response([
                'data' => [
                    'accessToken' => 'fake_redx_token_xyz'
                ]
            ], 200),

            "https://redx.com.bd/api/redx_se/admin/parcel/customer-success-return-rate?phoneNumber=88{$phone}" => Http::response([
                'data' => [
                    'deliveredParcels' => 20,
                    'totalParcels' => 25
                ]
            ], 200),
        ]);

        $service = new RedxService();
        $result = $service->getDeliveryStats($phone);

        $this->assertEquals([
            'success' => 20,
            'cancel' => 5,
            'total' => 25,
            'success_ratio' => 80.0,
        ], $result);
    }
}
