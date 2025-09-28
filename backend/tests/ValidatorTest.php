<?php

namespace RatesApi\Tests;

use PHPUnit\Framework\TestCase;
use RatesApi\Utils\Validator;

/**
 * Unit tests for Validator
 * Tests input validation and sanitization
 */
class ValidatorTest extends TestCase
{
    /**
     * Test valid request validation
     */
    public function testValidRatesRequest(): void
    {
        $validData = [
            'Unit Name' => 'Deluxe Suite',
            'Arrival' => '15/12/2024',
            'Departure' => '20/12/2024',
            'Occupants' => 2,
            'Ages' => [25, 30]
        ];

        $errors = Validator::validateRatesRequest($validData);
        $this->assertEmpty($errors);
    }

    /**
     * Test missing required fields
     */
    public function testMissingRequiredFields(): void
    {
        $invalidData = [
            'Unit Name' => 'Test Room'
            // Missing other required fields
        ];

        $errors = Validator::validateRatesRequest($invalidData);
        $this->assertNotEmpty($errors);
        $this->assertCount(4, $errors); // Missing 4 fields
    }

    /**
     * Test invalid unit name
     */
    public function testInvalidUnitName(): void
    {
        $invalidData = [
            'Unit Name' => '', // Empty
            'Arrival' => '15/12/2024',
            'Departure' => '20/12/2024',
            'Occupants' => 1,
            'Ages' => [25]
        ];

        $errors = Validator::validateRatesRequest($invalidData);
        $this->assertContains('Unit Name must be a non-empty string', $errors);
    }

    /**
     * Test invalid date format
     */
    public function testInvalidDateFormat(): void
    {
        $invalidData = [
            'Unit Name' => 'Test Room',
            'Arrival' => '2024-12-15', // Wrong format
            'Departure' => '20/12/2024',
            'Occupants' => 1,
            'Ages' => [25]
        ];

        $errors = Validator::validateRatesRequest($invalidData);
        $this->assertContains('Arrival date must be in dd/mm/yyyy format', $errors);
    }

    /**
     * Test input sanitization
     */
    public function testInputSanitization(): void
    {
        $dirtyInput = '<script>alert("xss")</script>';
        $cleanInput = Validator::sanitizeInput($dirtyInput);
        
        $this->assertNotEquals($dirtyInput, $cleanInput);
        $this->assertStringNotContainsString('<script>', $cleanInput);
    }

    /**
     * Test array sanitization
     */
    public function testArraySanitization(): void
    {
        $dirtyArray = [
            'name' => '<script>alert("xss")</script>',
            'value' => 'clean value'
        ];
        
        $cleanArray = Validator::sanitizeInput($dirtyArray);
        
        $this->assertStringNotContainsString('<script>', $cleanArray['name']);
        $this->assertEquals('clean value', $cleanArray['value']);
    }
}