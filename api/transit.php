<?php
// api/transit.php — Public Transit Data Endpoint
// Proxies requests to the Transitland API for nearby stops and route search.
// GET (stops):  ?lat=X&lon=Y&radius=500
// GET (search): ?search=IRCTC&limit=10
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Only accept GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed. Use GET.']);
    exit;
}

require_once __DIR__ . '/../services/Transit.php';

$action = $_GET['action'] ?? 'stops';

if ($action === 'search') {
    // Route search mode
    $query = trim($_GET['search'] ?? '');
    $limit = intval($_GET['limit'] ?? 10);

    if (empty($query)) {
        http_response_code(400);
        echo json_encode([
            'status'  => 'error',
            'message' => 'search parameter is required for route search.',
            'usage'   => 'GET /api/transit.php?action=search&search=IRCTC'
        ]);
        exit;
    }

    $result = Transit::searchRoutes($query, $limit);
} else {
    // Nearby stops mode (default)
    $lat    = isset($_GET['lat']) ? floatval($_GET['lat']) : null;
    $lon    = isset($_GET['lon']) ? floatval($_GET['lon']) : null;
    $radius = intval($_GET['radius'] ?? 500);
    $limit  = intval($_GET['limit'] ?? 20);

    if ($lat === null || $lon === null) {
        http_response_code(400);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Missing required parameters: lat and lon',
            'usage'   => 'GET /api/transit.php?lat=10.089&lon=77.060&radius=500'
        ]);
        exit;
    }

    $result = Transit::getNearbyStops($lat, $lon, $radius, $limit);
}

echo json_encode($result, JSON_PRETTY_PRINT);
?>
