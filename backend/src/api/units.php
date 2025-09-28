<?php

/**
 * Units API Endpoint
 * 
 * Returns available unit names for the frontend dropdown
 */

// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'error' => 'Method not allowed',
        'details' => ['Only GET requests are supported']
    ]);
    exit();
}

// Testing unit names for demonstration purposes
// These correspond to the Unit Type IDs [-2147483637, -2147483456] specified for testing
$unitNames = [
    [
        'id' => 'kalahari-farmhouse',
        'name' => 'Kalahari Farmhouse',
        'description' => 'Available camping/farmhouse accommodation (Unit Type ID: -2147483637)',
        'category' => 'Available',
        'unitTypeId' => -2147483637,
        'status' => 'Available for testing'
    ],
    [
        'id' => 'standard-room',
        'name' => 'Standard Room',
        'description' => 'Unavailable accommodation for testing (Unit Type ID: -2147483456)',
        'category' => 'Unavailable',
        'unitTypeId' => -2147483456,
        'status' => 'Unavailable for testing'
    ],
    [
        'id' => 'deluxe-suite',
        'name' => 'Deluxe Suite',
        'description' => 'Available accommodation (maps to Unit Type ID: -2147483637)',
        'category' => 'Available',
        'unitTypeId' => -2147483637,
        'status' => 'Available for testing'
    ],
    [
        'id' => 'executive-suite',
        'name' => 'Executive Suite',
        'description' => 'Unavailable accommodation (maps to Unit Type ID: -2147483456)',
        'category' => 'Unavailable',
        'unitTypeId' => -2147483456,
        'status' => 'Unavailable for testing'
    ]
];

// Return the unit names
http_response_code(200);
echo json_encode([
    'units' => $unitNames,
    'total' => count($unitNames),
    'categories' => array_unique(array_column($unitNames, 'category'))
], JSON_PRETTY_PRINT);
?>