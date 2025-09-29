<?php

namespace RatesApi\Utils;

/**
 * Input validation utility class
 * Handles validation of incoming API requests
 */
class Validator
{
    // Constants for repeated literals
    private const DATE_FORMAT = 'd/m/Y';
    private const UNIT_NAME_KEY = 'Unit Name';
    
    /**
     * Validate the rates request payload
     * 
     * @param array $data The request data to validate
     * @return array Array of validation errors (empty if valid)
     */
    public static function validateRatesRequest(array $data): array
    {
        $errors = [];

        // Check required fields first
        $errors = self::validateRequiredFields($data);
        if (!empty($errors)) {
            return $errors;
        }

        // Validate individual fields
        $errors = array_merge($errors, self::validateUnitName($data));
        $errors = array_merge($errors, self::validateDates($data));
        $errors = array_merge($errors, self::validateOccupants($data));
        $errors = array_merge($errors, self::validateAges($data));

        return $errors;
    }

    /**
     * Validate required fields
     */
    private static function validateRequiredFields(array $data): array
    {
        $errors = [];
        $requiredFields = [self::UNIT_NAME_KEY, 'Arrival', 'Departure', 'Occupants', 'Ages'];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                $errors[] = "Missing required field: {$field}";
            }
        }
        
        return $errors;
    }

    /**
     * Validate unit name
     */
    private static function validateUnitName(array $data): array
    {
        $errors = [];
        $unitName = $data[self::UNIT_NAME_KEY];
        
        if (!is_string($unitName) || trim($unitName) === '') {
            $errors[] = "Unit Name must be a non-empty string";
        } elseif (strlen($unitName) > 100) {
            $errors[] = "Unit Name must be less than 100 characters";
        } elseif (!preg_match('/^[a-zA-Z0-9\s\-_]+$/', $unitName)) {
            $errors[] = "Unit Name contains invalid characters";
        }
        
        return $errors;
    }

    /**
     * Validate dates
     */
    private static function validateDates(array $data): array
    {
        $errors = [];
        
        if (!self::isValidDateFormat($data['Arrival'])) {
            $errors[] = "Arrival date must be in dd/mm/yyyy format";
        }

        if (!self::isValidDateFormat($data['Departure'])) {
            $errors[] = "Departure date must be in dd/mm/yyyy format";
        }

        // Validate date logic if both dates are valid
        if (self::isValidDateFormat($data['Arrival']) && self::isValidDateFormat($data['Departure'])) {
            $arrivalDate = \DateTime::createFromFormat(self::DATE_FORMAT, $data['Arrival']);
            $departureDate = \DateTime::createFromFormat(self::DATE_FORMAT, $data['Departure']);

            if ($arrivalDate >= $departureDate) {
                $errors[] = "Arrival date must be before departure date";
            }
        }
        
        return $errors;
    }

    /**
     * Validate occupants
     */
    private static function validateOccupants(array $data): array
    {
        $errors = [];
        $occupants = $data['Occupants'];
        
        if (!is_int($occupants) || $occupants <= 0) {
            $errors[] = "Occupants must be a positive integer";
        } elseif ($occupants > 20) {
            $errors[] = "Occupants cannot exceed 20";
        }
        
        return $errors;
    }

    /**
     * Validate ages array
     */
    private static function validateAges(array $data): array
    {
        $errors = [];
        $ages = $data['Ages'];
        
        if (!is_array($ages)) {
            $errors[] = "Ages must be an array";
            return $errors;
        }

        // Check count matches occupants
        if (is_int($data['Occupants']) && count($ages) !== $data['Occupants']) {
            $errors[] = "Number of ages must match occupants count";
        }

        // Validate each age
        foreach ($ages as $index => $age) {
            if (!is_int($age) || $age <= 0) {
                $errors[] = "Age at index {$index} must be a positive integer";
            } elseif ($age > 150) {
                $errors[] = "Age at index {$index} cannot exceed 150 years";
            }
        }
        
        return $errors;
    }

    /**
     * Check if a date string is in valid dd/mm/yyyy format
     * 
     * @param mixed $dateString The date string to validate
     * @return bool True if valid format, false otherwise
     */
    private static function isValidDateFormat($dateString): bool
    {
        if (!is_string($dateString)) {
            return false;
        }

        // Check format with regex
        if (!preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dateString)) {
            return false;
        }

        // Validate actual date
        $date = \DateTime::createFromFormat(self::DATE_FORMAT, $dateString);
        return $date && $date->format(self::DATE_FORMAT) === $dateString;
    }

    /**
     * Sanitize input data to prevent XSS and other attacks
     * 
     * @param mixed $data The data to sanitize
     * @return mixed Sanitized data
     */
    public static function sanitizeInput($data)
    {
        if (is_string($data)) {
            return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
        }

        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }

        return $data;
    }
}