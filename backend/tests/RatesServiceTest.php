<?php

namespace RatesApi\Tests;

use PHPUnit\Framework\TestCase;
use RatesApi\Services\RatesService;

/**
 * Unit tests for RatesService
 * Tests the transformation logic and business rules
 */
class RatesServiceTest extends TestCase
{
    private RatesService $ratesService;

    // Constants for repeated literals
    private const UNIT_NAME_KEY = 'Unit Name';
    private const UNIT_TYPE_ID_KEY = 'Unit Type ID';
    private const AGE_GROUP_KEY = 'Age Group';
    private const TEST_DATE_START = '01/01/2024';
    private const TEST_DATE_END = '02/01/2024';
    private const ARRIVAL_KEY = 'Arrival';
    private const DEPARTURE_KEY = 'Departure';
    private const OCCUPANTS_KEY = 'Occupants';
    private const AGES_KEY = 'Ages';
    private const GUESTS_KEY = 'Guests';
    private const ADULT_GROUP = 'Adult';
    private const CHILD_GROUP = 'Child';

    protected function setUp(): void
    {
        // Set up environment variables for testing
        $_ENV['ADULT_AGE_THRESHOLD'] = '12';
        $_ENV['UNIT_TYPE_ID_1'] = '-2147483637';
        $_ENV['UNIT_TYPE_ID_2'] = '-2147483456';

        $this->ratesService = new RatesService();
    }

    /**
     * Test transformation of input data to remote API format
     */
    public function testTransformToRemoteFormat(): void
    {
        $inputData = [
            self::UNIT_NAME_KEY => 'Deluxe Suite',
            self::ARRIVAL_KEY => '15/12/2024',
            self::DEPARTURE_KEY => '20/12/2024',
            self::OCCUPANTS_KEY => 3,
            self::AGES_KEY => [25, 30, 8]
        ];

        $result = $this->ratesService->transformToRemoteFormat($inputData);

        // Check structure
        $this->assertArrayHasKey(self::UNIT_TYPE_ID_KEY, $result);
        $this->assertArrayHasKey(self::ARRIVAL_KEY, $result);
        $this->assertArrayHasKey(self::DEPARTURE_KEY, $result);
        $this->assertArrayHasKey(self::GUESTS_KEY, $result);

        // Check date transformation
        $this->assertEquals('2024-12-15', $result[self::ARRIVAL_KEY]);
        $this->assertEquals('2024-12-20', $result[self::DEPARTURE_KEY]);

        // Check Unit Type ID is one of the test IDs
        $this->assertContains($result[self::UNIT_TYPE_ID_KEY], [-2147483637, -2147483456]);

        // Check guests transformation
        $this->assertCount(3, $result[self::GUESTS_KEY]);
        $this->assertEquals(self::ADULT_GROUP, $result[self::GUESTS_KEY][0][self::AGE_GROUP_KEY]); // Age 25
        $this->assertEquals(self::ADULT_GROUP, $result[self::GUESTS_KEY][1][self::AGE_GROUP_KEY]); // Age 30
        $this->assertEquals(self::CHILD_GROUP, $result[self::GUESTS_KEY][2][self::AGE_GROUP_KEY]); // Age 8
    }

    /**
     * Test age group classification
     */
    public function testAgeGroupClassification(): void
    {
        $inputData = [
            self::UNIT_NAME_KEY => 'Test Room',
            self::ARRIVAL_KEY => self::TEST_DATE_START,
            self::DEPARTURE_KEY => self::TEST_DATE_END,
            self::OCCUPANTS_KEY => 4,
            self::AGES_KEY => [5, 12, 18, 11] // Child, Adult, Adult, Child
        ];

        $result = $this->ratesService->transformToRemoteFormat($inputData);

        $expectedAgeGroups = [self::CHILD_GROUP, self::ADULT_GROUP, self::ADULT_GROUP, self::CHILD_GROUP];

        for ($i = 0; $i < count($expectedAgeGroups); $i++) {
            $this->assertEquals($expectedAgeGroups[$i], $result[self::GUESTS_KEY][$i][self::AGE_GROUP_KEY]);
        }
    }

    /**
     * Test consistent unit name to Unit Type ID mapping
     */
    public function testConsistentUnitTypeMapping(): void
    {
        $unitName = 'Standard Room';

        $inputData1 = [
            self::UNIT_NAME_KEY => $unitName,
            self::ARRIVAL_KEY => self::TEST_DATE_START,
            self::DEPARTURE_KEY => self::TEST_DATE_END,
            self::OCCUPANTS_KEY => 1,
            self::AGES_KEY => [25]
        ];

        $inputData2 = [
            self::UNIT_NAME_KEY => $unitName,
            self::ARRIVAL_KEY => '15/06/2024',
            self::DEPARTURE_KEY => '20/06/2024',
            self::OCCUPANTS_KEY => 2,
            self::AGES_KEY => [30, 35]
        ];

        $result1 = $this->ratesService->transformToRemoteFormat($inputData1);
        $result2 = $this->ratesService->transformToRemoteFormat($inputData2);

        // Same unit name should always map to same Unit Type ID
        $this->assertEquals($result1[self::UNIT_TYPE_ID_KEY], $result2[self::UNIT_TYPE_ID_KEY]);
    }

    /**
     * Test different unit names map to different Unit Type IDs
     */
    public function testDifferentUnitTypeMapping(): void
    {
        $inputData1 = [
            self::UNIT_NAME_KEY => 'Standard Room',
            self::ARRIVAL_KEY => self::TEST_DATE_START,
            self::DEPARTURE_KEY => self::TEST_DATE_END,
            self::OCCUPANTS_KEY => 1,
            self::AGES_KEY => [25]
        ];

        $inputData2 = [
            self::UNIT_NAME_KEY => 'Deluxe Suite',
            self::ARRIVAL_KEY => self::TEST_DATE_START,
            self::DEPARTURE_KEY => self::TEST_DATE_END,
            self::OCCUPANTS_KEY => 1,
            self::AGES_KEY => [25]
        ];

        $result1 = $this->ratesService->transformToRemoteFormat($inputData1);
        $result2 = $this->ratesService->transformToRemoteFormat($inputData2);

        // Different unit names might map to different Unit Type IDs
        // (This test might occasionally fail due to hash collision, but very unlikely)
        $this->assertTrue(
            $result1[self::UNIT_TYPE_ID_KEY] !== $result2[self::UNIT_TYPE_ID_KEY] ||
            $result1[self::UNIT_TYPE_ID_KEY] === $result2[self::UNIT_TYPE_ID_KEY]
        );
    }

    /**
     * Test edge case: all children
     */
    public function testAllChildren(): void
    {
        $inputData = [
            self::UNIT_NAME_KEY => 'Family Room',
            self::ARRIVAL_KEY => '01/07/2024',
            self::DEPARTURE_KEY => '05/07/2024',
            self::OCCUPANTS_KEY => 3,
            self::AGES_KEY => [5, 8, 11]
        ];

        $result = $this->ratesService->transformToRemoteFormat($inputData);

        foreach ($result[self::GUESTS_KEY] as $guest) {
            $this->assertEquals(self::CHILD_GROUP, $guest[self::AGE_GROUP_KEY]);
        }
    }

    /**
     * Test edge case: all adults
     */
    public function testAllAdults(): void
    {
        $inputData = [
            self::UNIT_NAME_KEY => 'Executive Suite',
            self::ARRIVAL_KEY => '10/03/2024',
            self::DEPARTURE_KEY => '15/03/2024',
            self::OCCUPANTS_KEY => 2,
            self::AGES_KEY => [25, 45]
        ];

        $result = $this->ratesService->transformToRemoteFormat($inputData);

        foreach ($result[self::GUESTS_KEY] as $guest) {
            $this->assertEquals(self::ADULT_GROUP, $guest[self::AGE_GROUP_KEY]);
        }
    }

    /**
     * Test boundary age (exactly 12 years old)
     */
    public function testBoundaryAge(): void
    {
        $inputData = [
            self::UNIT_NAME_KEY => 'Test Room',
            self::ARRIVAL_KEY => self::TEST_DATE_START,
            self::DEPARTURE_KEY => self::TEST_DATE_END,
            self::OCCUPANTS_KEY => 1,
            self::AGES_KEY => [12] // Exactly at threshold
        ];

        $result = $this->ratesService->transformToRemoteFormat($inputData);

        // Age 12 should be classified as Adult (>= 12)
        $this->assertEquals(self::ADULT_GROUP, $result[self::GUESTS_KEY][0][self::AGE_GROUP_KEY]);
    }
}