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
    private Client $httpClient;
    private string $remoteApiUrl;
    private int $timeout;
    private int $adultAgeThreshold;
    private array $testUnitTypeIds;

    public function __construct()
    {
        $this->httpClient = new Client();
        $this->remoteApiUrl = $_ENV['REMOTE_API_URL'] ?? 'https://dev.gondwana-collection.com/Web-Store/Rates/Rates.php';
        $this->timeout = (int)($_ENV['REMOTE_API_TIMEOUT'] ?? 30);
        $this->adultAgeThreshold = (int)($_ENV['ADULT_AGE_THRESHOLD'] ?? 12);
        
        // Test Unit Type IDs for demonstration
        $this->testUnitTypeIds = [
            (int)($_ENV['UNIT_TYPE_ID_1'] ?? -2147483637),
            (int)($_ENV['UNIT_TYPE_ID_2'] ?? -2147483456)
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
        return $this->transformResponse($requestData, $remoteResponse);
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
        $arrivalDate = \DateTime::createFromFormat('d/m/Y', $inputData['Arrival']);
        $departureDate = \DateTime::createFromFormat('d/m/Y', $inputData['Departure']);

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
                ]
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
     * @return array Our formatted response
     */
    private function transformResponse(array $originalRequest, array $remoteResponse): array
    {
        // Extract rate and availability from remote response
        // Note: This is a simplified implementation - adjust based on actual API response structure
        $rate = $remoteResponse['rate'] ?? $remoteResponse['Rate'] ?? 0;
        $availability = $remoteResponse['available'] ?? $remoteResponse['Availability'] ?? true;

        // Format date range
        $arrivalDate = \DateTime::createFromFormat('d/m/Y', $originalRequest['Arrival']);
        $departureDate = \DateTime::createFromFormat('d/m/Y', $originalRequest['Departure']);
        $dateRange = $arrivalDate->format('Y-m-d') . ' to ' . $departureDate->format('Y-m-d');

        return [
            'Unit Name' => $originalRequest['Unit Name'],
            'Rate' => (float)$rate,
            'Date Range' => $dateRange,
            'Availability' => (bool)$availability,
            'Raw Response' => $remoteResponse
        ];
    }
}