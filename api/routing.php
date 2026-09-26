<?php
// api/routing.php — OpenRouteService Multimodal Routing Endpoint
// Proxies requests to the ORS Directions API server-side.
// POST: { start: [lon,lat], end: [lon,lat], profile: "driving-car" }
// POST (compare): { start: [lon,lat], end: [lon,lat], compare: true, profiles: [...] }
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed. Use POST.']);
    exit;
}

require_once __DIR__ . '/../services/Routing.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    http_response_code(400);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Invalid JSON body.',
        'usage'   => '{ "start": [lon, lat], "end": [lon, lat], "profile": "driving-car" }'
    ]);
    exit;
}

$start = $data['start'] ?? null;
$end   = $data['end'] ?? null;

// Validate coordinates
if (!is_array($start) || count($start) !== 2 || !is_array($end) || count($end) !== 2) {
    http_response_code(400);
    echo json_encode([
        'status'  => 'error',
        'message' => 'start and end must be [longitude, latitude] arrays.',
        'example' => ['start' => [77.060, 10.089], 'end' => [73.950, 15.150]]
    ]);
    exit;
}

// Comparison mode: multiple profiles at once
$compare = !empty($data['compare']);
if ($compare) {
    $profiles = $data['profiles'] ?? ['driving-car', 'cycling-regular', 'foot-walking'];
    $result = Routing::compareRoutes($start, $end, $profiles);
} else {
    $profile = $data['profile'] ?? 'driving-car';
    $result = Routing::getRoute($start, $end, $profile);
}

echo json_encode($result, JSON_PRETTY_PRINT);
?>
