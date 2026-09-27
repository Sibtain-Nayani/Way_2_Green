<?php
// api/places.php — OpenTripMap Points of Interest Endpoint
// GET ?lat=X&lon=Y&radius=5000&kinds=natural,parks
// GET ?xid=W12345 → place detail
// GET ?dest=munnar&type=eco → predefined destination eco attractions
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); echo json_encode(['status'=>'error','message'=>'Use GET']); exit; }

require_once __DIR__ . '/../services/OpenTripMap.php';
require_once __DIR__ . '/../services/Weather.php';

// Detail lookup
if (!empty($_GET['xid'])) {
    echo json_encode(OpenTripMap::getPlaceDetails(trim($_GET['xid'])), JSON_PRETTY_PRINT); exit;
}

// Predefined destination slug
if (!empty($_GET['dest'])) {
    $slug = strtolower(trim($_GET['dest']));
    $coords = Weather::DESTINATION_COORDS[$slug] ?? null;
    if (!$coords) { http_response_code(404); echo json_encode(['status'=>'error','message'=>"Unknown dest '$slug'"]); exit; }
    $radius = intval($_GET['radius'] ?? 8000);
    $type   = strtolower($_GET['type'] ?? 'eco');
    $kinds  = $type === 'access' ? OpenTripMap::ACCESS_KINDS : OpenTripMap::ECO_KINDS;
    echo json_encode(OpenTripMap::getNearbyPlaces($coords['lat'], $coords['lon'], $radius, $kinds), JSON_PRETTY_PRINT);
    exit;
}

// Custom lat/lon
$lat = isset($_GET['lat']) ? floatval($_GET['lat']) : null;
$lon = isset($_GET['lon']) ? floatval($_GET['lon']) : null;
if ($lat === null || $lon === null) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'Provide lat & lon, xid, or dest slug',
        'examples'=>['places'=>'?lat=10.089&lon=77.060&radius=5000','dest'=>'?dest=munnar&type=eco','detail'=>'?xid=W123456']]);
    exit;
}

$radius = intval($_GET['radius'] ?? 5000);
$kinds  = trim($_GET['kinds'] ?? OpenTripMap::ECO_KINDS);
$limit  = intval($_GET['limit'] ?? 30);
echo json_encode(OpenTripMap::getNearbyPlaces($lat, $lon, $radius, $kinds, $limit), JSON_PRETTY_PRINT);
?>
