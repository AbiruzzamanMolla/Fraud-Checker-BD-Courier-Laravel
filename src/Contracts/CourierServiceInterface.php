<?php

namespace Azmolla\FraudCheckerBdCourier\Contracts;

/**
 * Interface CourierServiceInterface
 *
 * Defines the contract for all courier service integrations. Any new courier
 * service added to the package must implement this interface to ensure
 * consistency in fetching delivery statistics.
 *
 * @package Azmolla\FraudCheckerBdCourier\Contracts
 */
interface CourierServiceInterface
{
    /**
     * Get customer delivery statistics based on their phone number.
     *
     * @param string $phoneNumber The customer phone number.
     * @return array Returns an array with keys 'success', 'cancel', 'total', and 'success_ratio'.
     */
    public function getDeliveryStats(string $phoneNumber): array;
}
