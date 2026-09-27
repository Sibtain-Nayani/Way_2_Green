<?php
// api/social_signals.php — Social Signals API Endpoint
// GET ?dest=Munnar&condition=flood&lat=10.089&lon=77.060
// GET ?dest=Munnar&source=reddit   (reddit only)
// GET ?dest=Munnar&source=osm      (OSM notes only)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); echo json_encode(['status'=>'error','message'=>'Use GET']); exit; }

require_once __DIR__ . '/../services/SocialSignals.php';

$dest      = trim($_GET['dest'] ?? '');
$condition = trim($_GET['condition'] ?? '');
$lat       = isset($_GET['lat']) ? floatval($_GET['lat']) : null;
$lon       = isset($_GET['lon']) ? floatval($_GET['lon']) : null;
$source    = strtolower(trim($_GET['source'] ?? 'all'));

if (empty($dest)) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'dest parameter required',
        'example'=>'?dest=Munnar&condition=flood&lat=10.089&lon=77.060']);
    exit;
}

if ($source === 'reddit') {
    echo json_encode(SocialSignals::getRedditSignals($dest, $condition, 12), JSON_PRETTY_PRINT);
} elseif ($source === 'osm') {
    if ($lat === null || $lon === null) { http_response_code(400); echo json_encode(['status'=>'error','message'=>'lat & lon required for OSM notes']); exit; }
    echo json_encode(SocialSignals::getOsmNotes($lat, $lon), JSON_PRETTY_PRINT);
} else {
    if ($lat === null || $lon === null) {
        // Fallback to Reddit only if no coords
        echo json_encode(SocialSignals::getRedditSignals($dest, $condition, 10), JSON_PRETTY_PRINT);
    } else {
        echo json_encode(SocialSignals::getAggregatedSignals($dest, $lat, $lon, $condition), JSON_PRETTY_PRINT);
    }
}
?>
