<?php

namespace RatesApi\Utils;

/**
 * Input validation utility class
 * Handles validation of incoming API requests
 */
class Validator
{
    /**
     * Validate the rates request payload
     * 
     * @param array $data The request data to validate
     * @return array Array of validation errors (empty if valid)
     */
    public static function validateRatesRequest(array $data): array
    {
        $errors = [];

        // Check required fields
        $requiredFields = ['Unit Name', 'Arrival', 'Departure', 'Occupants', 'Ages'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                $errors[] = "Missing required field: {$field}";
            }
        }

        // If required fields are missing, return early
        if (!empty($errors)) {
            return $errors;
        }

        // Validate Unit Name
        if (!is_string($data['Unit Name']) || trim($data['Unit Name']) === '') {
            $errors[] = "Unit Name must be a non-empty string";
        } elseif (strlen($data['Unit Name']) > 100) {
            $errors[] = "Unit Name must be less than 100 characters";
        } elseif (!preg_match('/^[a-zA-Z0-9\s\-_]+$/', $data['Unit Name'])) {
            $errors[] = "Unit Name contains invalid characters";
        }

        // Validate date formats (dd/mm/yyyy)
        if (!self::isValidDateFormat($data['Arrival'])) {
            $errors[] = "Arrival date must be in dd/mm/yyyy format";
        }

        if (!self::isValidDateFormat($data['Departure'])) {
            $errors[] = "Departure date must be in dd/mm/yyyy format";
        }

        // Validate date logic (arrival before departure)
        if (self::isValidDateFormat($data['Arrival']) && self::isValidDateFormat($data['Departure'])) {
            $arrivalDate = \DateTime::createFromFormat('d/m/Y', $data['Arrival']);
            $departureDate = \DateTime::createFromFormat('d/m/Y', $data['Departure']);

            if ($arrivalDate >= $departureDate) {
                $errors[] = "Arrival date must be before departure date";
            }
        }

        // Validate Occupants
        if (!is_int($data['Occupants']) || $data['Occupants'] <= 0) {
            $errors[] = "Occupants must be a positive integer";
        } elseif ($data['Occupants'] > 20) {
            $errors[] = "Occupants cannot exceed 20";
        }

        // Validate Ages array
        if (!is_array($data['Ages'])) {
            $errors[] = "Ages must be an array";
        } else {
            // Check if occupants count matches ages array length
            if (is_int($data['Occupants']) && count($data['Ages']) !== $data['Occupants']) {
                $errors[] = "Number of ages must match occupants count";
            }

            // Validate each age
            foreach ($data['Ages'] as $index => $age) {
                if (!is_int($age) || $age <= 0) {
                    $errors[] = "Age at index {$index} must be a positive integer";
                } elseif ($age > 150) {
                    $errors[] = "Age at index {$index} cannot exceed 150 years";
                }
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
        $date = \DateTime::createFromFormat('d/m/Y', $dateString);
        return $date && $date->format('d/m/Y') === $dateString;
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