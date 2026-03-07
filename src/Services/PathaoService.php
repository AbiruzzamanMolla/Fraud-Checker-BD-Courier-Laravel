<?php

namespace Azmolla\FraudCheckerBdCourier\Services;

use Illuminate\Support\Facades\Http;
use Azmolla\FraudCheckerBdCourier\Helpers\CourierDataValidator;

use Azmolla\FraudCheckerBdCourier\Contracts\CourierServiceInterface;

/**
 * Class PathaoService
 *
 * Handles API interactions with Pathao courier to fetch delivery statistics
 * for a specific customer phone number.
 *
 * @package Azmolla\FraudCheckerBdCourier\Services
 */
readonly class PathaoService implements CourierServiceInterface
{
    /**
     * @var string The username for Pathao API authentication.
     */
    protected string $username;

    /**
     * @var string The password for Pathao API authentication.
     */
    protected string $password;

    /**
     * PathaoService constructor.
     *
     * Validates configuration and initializes the API credentials.
     */
    public function __construct()
    {
        CourierDataValidator::enforceConfig([
            'fraud-checker-bd-courier.pathao.user',
            'fraud-checker-bd-courier.pathao.password',
        ]);

        $this->username = config('fraud-checker-bd-courier.pathao.user');
        $this->password = config('fraud-checker-bd-courier.pathao.password');
    }

    /**
     * Fetch delivery statistics from Pathao for the given phone number.
     *
     * @param string $phoneNumber The Bangladeshi mobile number to check.
     * @return array Contains 'success', 'cancel', 'total', and 'success_ratio'.
     *               In case of an error, returns an array with an 'error' key.
     */
    public function getDeliveryStats(string $phoneNumber): array
    {
        try {
            CourierDataValidator::checkBdMobile($phoneNumber);

            $response = Http::post('https://merchant.pathao.com/api/v1/login', [
                'username' => $this->username,
                'password' => $this->password,
            ]);

            if (!$response->successful()) {
                return ['error' => 'Failed to authenticate with Pathao', 'status' => $response->status()];
            }

            $data = $response->json();
            $accessToken = trim($data['access_token'] ?? '');

            if (!$accessToken) {
                return ['error' => 'No access token received from Pathao'];
            }

            $responseAuth = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ])->post('https://merchant.pathao.com/api/v1/user/success', [
                'phone' => $phoneNumber,
            ]);

            if (!$responseAuth->successful()) {
                return ['error' => 'Failed to retrieve customer data from Pathao', 'status' => $responseAuth->status()];
            }

            $object = $responseAuth->json();

            $success = (int)($object['data']['customer']['successful_delivery'] ?? 0);
            $total = (int)($object['data']['customer']['total_delivery'] ?? 0);
            $cancel = max(0, $total - $success);
            $success_ratio = $total > 0 ? round(($success / $total) * 100, 2) : 0;

            return [
                'success' => $success,
                'cancel' => $cancel,
                'total' => $total,
                'success_ratio' => $success_ratio,
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'An error occurred while processing Pathao request',
                'message' => $e->getMessage()
            ];
        }
    }
}
