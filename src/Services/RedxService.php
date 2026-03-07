<?php

namespace Azmolla\FraudCheckerBdCourier\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Azmolla\FraudCheckerBdCourier\Helpers\CourierDataValidator;

use Azmolla\FraudCheckerBdCourier\Contracts\CourierServiceInterface;

/**
 * Class RedxService
 *
 * Handles API interactions with RedX courier to fetch delivery statistics.
 * Uses caching to store the access token and prevent hitting login rate limits.
 *
 * @package Azmolla\FraudCheckerBdCourier\Services
 */
readonly class RedxService implements CourierServiceInterface
{
    /**
     * @var string The cache key used to store the RedX access token.
     */
    protected string $cacheKey;

    /**
     * @var int The token expiration time in minutes.
     */
    protected int $cacheMinutes;

    /**
     * @var string The login phone number for RedX API.
     */
    protected string $phone;

    /**
     * @var string The password for RedX API authentication.
     */
    protected string $password;

    /**
     * RedxService constructor.
     *
     * Validates configuration and prepares the authentication details.
     */
    public function __construct()
    {
        $this->cacheKey = 'redx_access_token';
        $this->cacheMinutes = 50;

        // Validate config presence
        CourierDataValidator::enforceConfig([
            'fraud-checker-bd-courier.redx.phone',
            'fraud-checker-bd-courier.redx.password',
        ]);

        // Load from config
        $this->phone = config('fraud-checker-bd-courier.redx.phone');
        $this->password = config('fraud-checker-bd-courier.redx.password');

        CourierDataValidator::checkBdMobile($this->phone);
    }

    /**
     * Retrieve a valid RedX access token from the cache or authenticate to get a new one.
     *
     * @return string|null The access token, or null on failure.
     */
    protected function getAccessToken(): ?string
    {
        // Use cached token if available
        $token = Cache::get($this->cacheKey);
        if ($token) {
            return $token;
        }

        // Request new token from RedX
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'Accept' => 'application/json, text/plain, */*',
        ])->post('https://api.redx.com.bd/v4/auth/login', [
            'phone' => '88' . $this->phone,
            'password' => $this->password,
        ]);

        if (!$response->successful()) {
            return null;
        }

        $token = $response->json('data.accessToken');
        if ($token) {
            Cache::put($this->cacheKey, $token, now()->addMinutes($this->cacheMinutes));
        }

        return $token;
    }

    /**
     * Fetch delivery statistics from RedX for the given phone number.
     *
     * @param string $queryPhone The Bangladeshi mobile number to check.
     * @return array Contains 'success', 'cancel', 'total', and 'success_ratio'.
     *               Returns an array with an 'error' key if the API request fails.
     */
    public function getDeliveryStats(string $queryPhone): array
    {
        try {
            CourierDataValidator::checkBdMobile($queryPhone);

            $accessToken = $this->getAccessToken();

            if (!$accessToken) {
                return ['error' => 'Login failed or unable to get access token from Redx'];
            }

            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'Accept' => 'application/json, text/plain, */*',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ])->get("https://redx.com.bd/api/redx_se/admin/parcel/customer-success-return-rate?phoneNumber=88{$queryPhone}");

            if ($response->successful()) {
                $object = $response->json();

                $success = (int)($object['data']['deliveredParcels'] ?? 0);
                $total = (int)($object['data']['totalParcels'] ?? 0);
                $cancel = max(0, $total - $success);
                $success_ratio = $total > 0 ? round(($success / $total) * 100, 2) : 0;

                return [
                    'success' => $success,
                    'cancel' => $cancel,
                    'total' => $total,
                    'success_ratio' => $success_ratio,
                ];
            } elseif ($response->status() === 401) {
                Cache::forget($this->cacheKey);
                return ['error' => 'Access token expired or invalid for Redx. Please retry.', 'status' => 401];
            }

            return [
                'success' => 0,
                'cancel' => 0,
                'total' => 0,
                'success_ratio' => 0,
                'error' => 'Threshold hit, wait a minute for Redx',
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'An error occurred while processing Redx request',
                'message' => $e->getMessage()
            ];
        }
    }
}
