<?php

/**
 * Rates API Endpoint
 * 
 * Handles POST requests for accommodation rate queries
 * Validates input, transforms data, and calls remote API
 */

// Set CORS headers FIRST - before any other output
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS, GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 86400'); // Cache preflight for 24 hours
header('Content-Type: application/json');

// Enable error reporting for development (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to avoid breaking JSON response

// Load dependencies
require_once __DIR__ . '/../../vendor/autoload.php';

use RatesApi\Utils\Validator;
use RatesApi\Services\RatesService;
use Dotenv\Dotenv;

// Load environment variables
try {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../../../');
    $dotenv->load();
} catch (Exception $e) {
    // Environment file not found - use defaults
    error_log('Environment file not found, using defaults');
}

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'error' => 'Method not allowed',
        'details' => ['Only POST requests are supported']
    ]);
    exit();
}

try {
    // Get and decode JSON input
    $rawInput = file_get_contents('php://input');
    
    if (empty($rawInput)) {
        throw new InvalidArgumentException('Request body is empty');
    }

    $inputData = json_decode($rawInput, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new InvalidArgumentException('Invalid JSON in request body');
    }

    // Sanitize input data
    $sanitizedData = Validator::sanitizeInput($inputData);

    // Validate input
    $validationErrors = Validator::validateRatesRequest($sanitizedData);
    
    if (!empty($validationErrors)) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Validation failed',
            'details' => $validationErrors
        ]);
        exit();
    }

    // Process the request
    $ratesService = new RatesService();
    $response = $ratesService->processRatesRequest($sanitizedData);

    // Return successful response
    http_response_code(200);
    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (InvalidArgumentException $e) {
    // Client error (400)
    http_response_code(400);
    echo json_encode([
        'error' => 'Invalid request',
        'details' => [$e->getMessage()]
    ]);

} catch (Exception $e) {
    // Check if it's a remote API error
    if ($e->getCode() === 502) {
        http_response_code(502);
        echo json_encode([
            'error' => 'Remote service unavailable',
            'details' => ['The rates service is temporarily unavailable. Please try again later.']
        ]);
    } else {
        // Generic server error (500)
        http_response_code(500);
        echo json_encode([
            'error' => 'Internal server error',
            'details' => ['An unexpected error occurred. Please try again later.']
        ]);
        
        // Log the actual error for debugging (don't expose to client)
        error_log('Rates API Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    }
}