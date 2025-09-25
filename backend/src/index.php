<?php
// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS, GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

echo json_encode([
    'message' => 'Rates API Backend',
    'version' => '1.0.0',
    'endpoints' => [
        'POST /api/rates.php' => 'Query accommodation rates'
    ],
    'status' => 'running'
]);
?>