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

// Predefined list of available unit names
// In a real application, this could come from a database or external API
$unitNames = [
    [
        'id' => 'standard-room',
        'name' => 'Standard Room',
        'description' => 'Comfortable accommodation with basic amenities',
        'category' => 'Standard'
    ],
    [
        'id' => 'deluxe-suite',
        'name' => 'Deluxe Suite',
        'description' => 'Spacious suite with premium amenities',
        'category' => 'Premium'
    ],
    [
        'id' => 'family-room',
        'name' => 'Family Room',
        'description' => 'Large room suitable for families with children',
        'category' => 'Family'
    ],
    [
        'id' => 'executive-suite',
        'name' => 'Executive Suite',
        'description' => 'Luxury suite with business amenities',
        'category' => 'Luxury'
    ],
    [
        'id' => 'presidential-suite',
        'name' => 'Presidential Suite',
        'description' => 'Top-tier luxury accommodation',
        'category' => 'Luxury'
    ],
    [
        'id' => 'kalahari-farmhouse',
        'name' => 'Kalahari Farmhouse',
        'description' => 'Traditional farmhouse experience',
        'category' => 'Unique'
    ],
    [
        'id' => 'safari-lodge',
        'name' => 'Safari Lodge',
        'description' => 'Authentic safari experience',
        'category' => 'Adventure'
    ],
    [
        'id' => 'desert-camp',
        'name' => 'Desert Camp',
        'description' => 'Desert camping experience',
        'category' => 'Adventure'
    ],
    [
        'id' => 'luxury-tent',
        'name' => 'Luxury Tent',
        'description' => 'Glamping with luxury amenities',
        'category' => 'Unique'
    ],
    [
        'id' => 'conference-room',
        'name' => 'Conference Room',
        'description' => 'Business meeting and conference facilities',
        'category' => 'Business'
    ],
    [
        'id' => 'camping-site',
        'name' => 'Camping Site',
        'description' => 'Basic camping facilities',
        'category' => 'Budget'
    ],
    [
        'id' => 'chalet',
        'name' => 'Chalet',
        'description' => 'Cozy mountain-style accommodation',
        'category' => 'Standard'
    ],
    [
        'id' => 'villa',
        'name' => 'Villa',
        'description' => 'Private villa with exclusive amenities',
        'category' => 'Luxury'
    ],
    [
        'id' => 'cottage',
        'name' => 'Cottage',
        'description' => 'Charming cottage accommodation',
        'category' => 'Standard'
    ],
    [
        'id' => 'bungalow',
        'name' => 'Bungalow',
        'description' => 'Standalone bungalow accommodation',
        'category' => 'Premium'
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