<?php

namespace Azmolla\FraudCheckerBdCourier\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * Class FraudCheckerBdCourier
 *
 * Facade for the Fraud Checker BD Courier package. Provides static access
 * to the underlying FraudCheckerBdCourierManager instance.
 *
 * @method static array check(string $phoneNumber) Fetch aggregated stats for a given phone number.
 *
 * @package Azmolla\FraudCheckerBdCourier\Facade
 * @see \Azmolla\FraudCheckerBdCourier\FraudCheckerBdCourierManager
 */
class FraudCheckerBdCourier extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'fraud-checker-bd-courier';
    }
}
