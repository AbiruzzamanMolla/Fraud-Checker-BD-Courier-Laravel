<?php

namespace Azmolla\FraudCheckerBdCourier;

use Azmolla\FraudCheckerBdCourier\Contracts\CourierServiceInterface;

/**
 * Class FraudCheckerBdCourierManager
 *
 * Core manager class responsible for aggregating delivery statistics
 * from multiple courier services (Steadfast, Pathao, RedX).
 *
 * @package Azmolla\FraudCheckerBdCourier
 */
class FraudCheckerBdCourierManager
{
    /**
     * FraudCheckerBdCourierManager constructor.
     *
     * Initializes the manager with the required courier service instances.
     *
     * @param CourierServiceInterface $steadfastService Instance of SteadfastService.
     * @param CourierServiceInterface $pathaoService    Instance of PathaoService.
     * @param CourierServiceInterface $redxService      Instance of RedxService.
     */
    public function __construct(
        protected readonly CourierServiceInterface $steadfastService,
        protected readonly CourierServiceInterface $pathaoService,
        protected readonly CourierServiceInterface $redxService,
    ) {}

    /**
     * Fetch delivery statistics across all configured couriers and aggregate the results.
     *
     * @param string $phoneNumber The Bangladeshi mobile number to check.
     * @return array Returns an associative array containing stats for each courier
     *               as well as an overall aggregated summary.
     */
    public function check(string $phoneNumber): array
    {
        $payload = [
            'steadfast' => null,
            'pathao' => null,
            'redx' => null,
            'aggregate' => [
                'total_success' => 0,
                'total_cancel' => 0,
                'total_deliveries' => 0,
                'success_ratio' => 0,
                'cancel_ratio' => 0,
            ]
        ];

        $services = [
            'steadfast' => $this->steadfastService,
            'pathao' => $this->pathaoService,
            'redx' => $this->redxService,
        ];

        $totalSuccessCount = 0;
        $totalCancelCount = 0;

        foreach ($services as $key => $service) {
            try {
                $stats = $service->getDeliveryStats($phoneNumber);
                $payload[$key] = $stats;

                if (isset($stats['success'], $stats['cancel']) && is_numeric($stats['success']) && is_numeric($stats['cancel'])) {
                    $totalSuccessCount += (int)$stats['success'];
                    $totalCancelCount += (int)$stats['cancel'];
                }
            } catch (\Exception $e) {
                $payload[$key] = [
                    'error' => 'Service unavailable or failed to process',
                    'message' => $e->getMessage()
                ];
            }
        }

        $overallTotal = $totalSuccessCount + $totalCancelCount;

        $payload['aggregate']['total_success'] = $totalSuccessCount;
        $payload['aggregate']['total_cancel'] = $totalCancelCount;
        $payload['aggregate']['total_deliveries'] = $overallTotal;

        if ($overallTotal > 0) {
            $payload['aggregate']['success_ratio'] = round(($totalSuccessCount / $overallTotal) * 100, 2);
            $payload['aggregate']['cancel_ratio'] = round(($totalCancelCount / $overallTotal) * 100, 2);
        }

        return $payload;
    }
}
