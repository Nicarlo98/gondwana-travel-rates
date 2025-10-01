<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

echo json_encode([
    'status' => 'healthy',
    'service' => 'Gondwana Travel Rates API',
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => PHP_VERSION,
    'endpoints' => [
        '/health.php' => 'Health check',
        '/src/api/test.php' => 'CORS test',
        '/src/api/rates.php' => 'Currency conversion',
        '/src/api/units.php' => 'Available currencies'
    ]
]);