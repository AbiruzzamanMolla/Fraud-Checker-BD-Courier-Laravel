<?php

namespace Azmolla\FraudCheckerBdCourier\Tests\Unit;

use Azmolla\FraudCheckerBdCourier\Tests\TestCase;
use Azmolla\FraudCheckerBdCourier\FraudCheckerBdCourierManager;
use Azmolla\FraudCheckerBdCourier\Contracts\CourierServiceInterface;
use Mockery;

class FraudCheckerBdCourierManagerTest extends TestCase
{
    public function test_manager_aggregates_successful_stats()
    {
        $phone = '01711111111';

        $steadfastMock = Mockery::mock(CourierServiceInterface::class);
        $steadfastMock->shouldReceive('getDeliveryStats')->once()->with($phone)->andReturn([
            'success' => 5,
            'cancel' => 2,
            'total' => 7,
            'success_ratio' => 71.43,
        ]);

        $pathaoMock = Mockery::mock(CourierServiceInterface::class);
        $pathaoMock->shouldReceive('getDeliveryStats')->once()->with($phone)->andReturn([
            'success' => 10,
            'cancel' => 3,
            'total' => 13,
            'success_ratio' => 76.92,
        ]);

        $redxMock = Mockery::mock(CourierServiceInterface::class);
        $redxMock->shouldReceive('getDeliveryStats')->once()->with($phone)->andReturn([
            'success' => 20,
            'cancel' => 5,
            'total' => 25,
            'success_ratio' => 80.0,
        ]);

        $manager = new FraudCheckerBdCourierManager($steadfastMock, $pathaoMock, $redxMock);
        $result = $manager->check($phone);

        $this->assertEquals(35, $result['aggregate']['total_success']);
        $this->assertEquals(10, $result['aggregate']['total_cancel']);
        $this->assertEquals(45, $result['aggregate']['total_deliveries']);
        $this->assertEquals(77.78, $result['aggregate']['success_ratio']);
        $this->assertEquals(22.22, $result['aggregate']['cancel_ratio']);

        $this->assertEquals(7, $result['steadfast']['total']);
        $this->assertEquals(13, $result['pathao']['total']);
        $this->assertEquals(25, $result['redx']['total']);
    }

    public function test_manager_handles_exceptions_gracefully()
    {
        $phone = '01711111111';

        $steadfastMock = Mockery::mock(CourierServiceInterface::class);
        $steadfastMock->shouldReceive('getDeliveryStats')->once()->with($phone)->andThrow(new \Exception('API Timeout'));

        $pathaoMock = Mockery::mock(CourierServiceInterface::class);
        $pathaoMock->shouldReceive('getDeliveryStats')->once()->with($phone)->andReturn([
            'success' => 10,
            'cancel' => 3,
            'total' => 13,
            'success_ratio' => 76.92,
        ]);

        $redxMock = Mockery::mock(CourierServiceInterface::class);
        $redxMock->shouldReceive('getDeliveryStats')->once()->with($phone)->andReturn([
            'error' => 'Login Failed',
        ]);

        $manager = new FraudCheckerBdCourierManager($steadfastMock, $pathaoMock, $redxMock);
        $result = $manager->check($phone);

        $this->assertEquals(10, $result['aggregate']['total_success']);
        $this->assertEquals(3, $result['aggregate']['total_cancel']);
        $this->assertEquals(13, $result['aggregate']['total_deliveries']);
        $this->assertEquals('Service unavailable or failed to process', $result['steadfast']['error']);
        $this->assertEquals('API Timeout', $result['steadfast']['message']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
