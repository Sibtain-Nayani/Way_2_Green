<?php
// api/accessibility.php — Wheelchair Accessibility Data Endpoint
// Proxies requests to the OpenStreetMap Overpass API server-side.
// GET: ?lat=X&lon=Y&radius=1000
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

require_once __DIR__ . '/../services/Accessibility.php';

$lat    = isset($_GET['lat']) ? floatval($_GET['lat']) : null;
$lon    = isset($_GET['lon']) ? floatval($_GET['lon']) : null;
$radius = isset($_GET['radius']) ? intval($_GET['radius']) : 1000;
$limit  = isset($_GET['limit']) ? intval($_GET['limit']) : 50;

// Validate required fields
if ($lat === null || $lon === null) {
    http_response_code(400);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Missing required parameters: lat and lon',
        'usage'   => 'GET /api/accessibility.php?lat=10.089&lon=77.060&radius=1000'
    ]);
    exit;
}

$result = Accessibility::getAccessiblePlaces($lat, $lon, $radius, $limit);

echo json_encode($result, JSON_PRETTY_PRINT);
?>
