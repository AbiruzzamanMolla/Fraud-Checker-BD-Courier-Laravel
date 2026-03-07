<?php

namespace Azmolla\FraudCheckerBdCourier\Helpers;

use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

/**
 * Class CourierDataValidator
 *
 * Provides helper methods for environment, configuration, and phone number validation.
 * Centralizes common checks used across different courier service classes.
 *
 * @package Azmolla\FraudCheckerBdCourier\Helpers
 */
class CourierDataValidator
{
    /**
     * Verifies that the given environment variables exist.
     *
     * @param array $variables
     * @throws InvalidArgumentException
     */
    public static function enforceEnv(array $variables): void
    {
        foreach ($variables as $var) {
            if (empty(env($var))) {
                throw new InvalidArgumentException(sprintf("The environment variable %s is required but missing.", $var));
            }
        }
    }

    /**
     * Validates whether the given string is a proper Bangladeshi mobile number.
     *
     * @param string $mobileNumber
     * @throws InvalidArgumentException
     */
    public static function checkBdMobile(string $mobileNumber): void
    {
        $validation = Validator::make(
            ['mobile' => $mobileNumber],
            [
                'mobile' => [
                    'required',
                    'regex:/^01[3-9][0-9]{8}$/'
                ]
            ],
            [
                'mobile.regex' => 'The provided phone number is invalid. Please format it locally (e.g., 01*********) without +88.'
            ]
        );

        if ($validation->fails()) {
            throw new InvalidArgumentException($validation->errors()->first('mobile'));
        }
    }

    /**
     * Verifies that the given config keys are populated.
     *
     * @param array $configDefinitions
     * @throws InvalidArgumentException
     */
    public static function enforceConfig(array $configDefinitions): void
    {
        foreach ($configDefinitions as $conf) {
            if (empty(config($conf))) {
                throw new InvalidArgumentException(sprintf("The config key %s is required but missing.", $conf));
            }
        }
    }
}
