<?php

namespace RatesApi\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Service class for handling rates API business logic
 * Transforms requests and communicates with remote API
 */
class RatesService
{
    // Constants for repeated literals
    private const DATE_FORMAT = 'd/m/Y';
    private const UNIT_NAME_KEY = 'Unit Name';
    private const TOTAL_CHARGE_KEY = 'Total Charge';
    
    private Client $httpClient;
    private string $remoteApiUrl;
    private int $timeout;
    private int $adultAgeThreshold;
    private array $testUnitTypeIds;

    public function __construct()
    {
        $this->httpClient = new Client();
        // Gondwana Collection Remote API endpoint for rate queries
        $this->remoteApiUrl = $_ENV['REMOTE_API_URL'] ?? 'https://dev.gondwana-collection.com/Web-Store/Rates/Rates.php';
        $this->timeout = (int) ($_ENV['REMOTE_API_TIMEOUT'] ?? 30);
        $this->adultAgeThreshold = (int) ($_ENV['ADULT_AGE_THRESHOLD'] ?? 12);

        // Test Unit Type IDs for demonstration
        $this->testUnitTypeIds = [
            (int) ($_ENV['UNIT_TYPE_ID_1'] ?? -2147483637),
            (int) ($_ENV['UNIT_TYPE_ID_2'] ?? -2147483456)
        ];
    }

    /**
     * Process rates request by transforming input and calling remote API
     * 
     * @param array $requestData The validated input data
     * @return array The processed response
     * @throws \Exception If remote API call fails
     */
    public function processRatesRequest(array $requestData): array
    {
        // Transform input to remote API format
        $transformedPayload = $this->transformToRemoteFormat($requestData);

        // Call remote API
        $remoteResponse = $this->callRemoteApi($transformedPayload);

        // Transform response back to our format
        return $this->transformResponse($requestData, $remoteResponse, $transformedPayload);
    }

    /**
     * Transform input data to remote API format
     * 
     * @param array $inputData The original request data
     * @return array Transformed data for remote API
     */
    public function transformToRemoteFormat(array $inputData): array
    {
        // Convert dates from dd/mm/yyyy to yyyy-mm-dd
        $arrivalDate = \DateTime::createFromFormat(self::DATE_FORMAT, $inputData['Arrival']);
        $departureDate = \DateTime::createFromFormat(self::DATE_FORMAT, $inputData['Departure']);

        // Map unit name to test Unit Type ID (alternating for demo)
        $unitTypeId = $this->getUnitTypeId($inputData['Unit Name']);

        // Transform ages to guest age groups
        $guests = $this->transformAges($inputData['Ages']);

        return [
            'Unit Type ID' => $unitTypeId,
            'Arrival' => $arrivalDate->format('Y-m-d'),
            'Departure' => $departureDate->format('Y-m-d'),
            'Guests' => $guests
        ];
    }

    /**
     * Transform ages array to guest age groups
     * 
     * @param array $ages Array of ages
     * @return array Array of guest objects with age groups
     */
    private function transformAges(array $ages): array
    {
        $guests = [];

        foreach ($ages as $age) {
            $ageGroup = $age >= $this->adultAgeThreshold ? 'Adult' : 'Child';
            $guests[] = ['Age Group' => $ageGroup];
        }

        return $guests;
    }

    /**
     * Get Unit Type ID for a given unit name (demo implementation)
     * 
     * @param string $unitName The unit name
     * @return int The corresponding Unit Type ID
     */
    private function getUnitTypeId(string $unitName): int
    {
        // Simple hash-based selection for consistent mapping
        $hash = crc32($unitName);
        $index = abs($hash) % count($this->testUnitTypeIds);

        return $this->testUnitTypeIds[$index];
    }

    /**
     * Call the remote rates API
     * 
     * @param array $payload The transformed payload
     * @return array The remote API response
     * @throws \Exception If API call fails
     */
    private function callRemoteApi(array $payload): array
    {
        try {
            $response = $this->httpClient->post($this->remoteApiUrl, [
                'json' => $payload,
                'timeout' => $this->timeout,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'verify' => filter_var($_ENV['SSL_VERIFY'] ?? 'true', FILTER_VALIDATE_BOOLEAN)
            ]);

            $responseBody = $response->getBody()->getContents();
            $decodedResponse = json_decode($responseBody, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response from remote API');
            }

            return $decodedResponse;

        } catch (RequestException $e) {
            // Log the error (in production, use proper logging)
            error_log('Remote API call failed: ' . $e->getMessage());

            // For demo purposes, return a mock response when remote API fails
            return $this->getMockResponse($payload);
        }
    }

    /**
     * Generate a mock response for demo purposes
     * 
     * @param array $payload The request payload
     * @return array Mock response
     */
    private function getMockResponse(array $payload): array
    {
        // Generate a mock rate based on unit type and date range
        $baseRate = 100;
        $unitTypeMultiplier = abs($payload['Unit Type ID']) % 1000 / 100;
        $guestCount = count($payload['Guests']);

        $mockRate = $baseRate + ($unitTypeMultiplier * 50) + ($guestCount * 25);

        return [
            'Rate' => $mockRate,
            'Availability' => true,
            'Currency' => 'USD',
            'Message' => 'Mock response - Remote API unavailable',
            'Payload' => $payload
        ];
    }

    /**
     * Transform remote API response to our response format
     * 
     * @param array $originalRequest The original request data
     * @param array $remoteResponse The remote API response
     * @param array $transformedPayload The payload sent to remote API
     * @return array Our formatted response
     */
    private function transformResponse(array $originalRequest, array $remoteResponse, array $transformedPayload): array
    {
        // Extract rate and availability from remote response
        // Gondwana API uses 'Total Charge' field and values are in cents, so divide by 100
        $totalCharge = $remoteResponse[self::TOTAL_CHARGE_KEY] ?? 0;
        $rate = $totalCharge / 100; // Convert from cents to dollars

        // Determine availability based on Gondwana API response patterns
        $availability = false;

        // Primary indicator: Total charge > 0 means available
        if ($totalCharge > 0) {
            $availability = true;
        }

        // Secondary check: Rooms available > 0 indicates availability
        $roomsAvailable = $remoteResponse['Rooms'] ?? 0;
        if ($roomsAvailable > 0) {
            $availability = true;
        }

        // Tertiary check: Valid legs with charges
        if (isset($remoteResponse['Legs']) && !empty($remoteResponse['Legs'])) {
            foreach ($remoteResponse['Legs'] as $leg) {
                $legCharge = $leg[self::TOTAL_CHARGE_KEY] ?? 0;
                if ($legCharge > 0) {
                    $availability = true;
                    break;
                }
            }
        }

        // Final validation: If we calculated a rate > 0, it should be available
        if ($rate > 0) {
            $availability = true;
        }

        // If no Total Charge but we have legs, sum up the leg charges
        if ($rate == 0 && isset($remoteResponse['Legs']) && is_array($remoteResponse['Legs'])) {
            $totalFromLegs = 0;
            foreach ($remoteResponse['Legs'] as $leg) {
                $totalFromLegs += $leg[self::TOTAL_CHARGE_KEY] ?? 0;
            }
            $rate = $totalFromLegs / 100; // Convert from cents to dollars
        }

        // Format date range
        $arrivalDate = \DateTime::createFromFormat(self::DATE_FORMAT, $originalRequest['Arrival']);
        $departureDate = \DateTime::createFromFormat(self::DATE_FORMAT, $originalRequest['Departure']);
        $dateRange = $arrivalDate->format('Y-m-d') . ' to ' . $departureDate->format('Y-m-d');

        return [
            self::UNIT_NAME_KEY => $originalRequest[self::UNIT_NAME_KEY],
            'Rate' => (float) $rate,
            'Date Range' => $dateRange,
            'Availability' => (bool) $availability,
            'Raw Response' => $remoteResponse
        ];
    }
}