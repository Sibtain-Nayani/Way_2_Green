<?php
// api/google_routes.php — Google Routes API Proxy Endpoint
// Proxies requests to the Google Routes/Directions API server-side.
// POST: { origin: "Mumbai", destination: "Goa", travelMode: "DRIVE" }
// POST (compare): { origin: "Mumbai", destination: "Goa", compare: true, modes: [...] }
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

require_once __DIR__ . '/../services/GoogleRoutes.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    http_response_code(400);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Invalid JSON body.',
        'usage'   => '{ "origin": "Mumbai, India", "destination": "Goa, India", "travelMode": "DRIVE" }'
    ]);
    exit;
}

$origin      = trim($data['origin'] ?? '');
$destination = trim($data['destination'] ?? '');

if (empty($origin) || empty($destination)) {
    http_response_code(400);
    echo json_encode([
        'status'  => 'error',
        'message' => 'origin and destination are required.'
    ]);
    exit;
}

// Comparison mode: multiple travel modes at once
$compare = !empty($data['compare']);
if ($compare) {
    $modes = $data['modes'] ?? ['DRIVE', 'TRANSIT', 'WALK'];
    $result = GoogleRoutes::compareRoutes($origin, $destination, $modes);
} else {
    $travelMode = $data['travelMode'] ?? 'DRIVE';
    $result = GoogleRoutes::computeRoute($origin, $destination, $travelMode);
}

echo json_encode($result, JSON_PRETTY_PRINT);
?>
