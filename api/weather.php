<?php
// api/weather.php — Live Weather & Forecast Endpoint
// GET ?lat=X&lon=Y            → current weather
// GET ?lat=X&lon=Y&forecast=1 → 5-day forecast
// GET ?dest=munnar            → predefined destination
// GET ?all=1                  → all Way2Green destinations
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); echo json_encode(['status'=>'error','message'=>'Use GET']); exit; }

require_once __DIR__ . '/../services/Weather.php';

// All destinations
if (!empty($_GET['all'])) {
    echo json_encode(Weather::getAllDestinationsWeather(), JSON_PRETTY_PRINT); exit;
}

// Predefined destination by slug
if (!empty($_GET['dest'])) {
    $slug = strtolower(trim($_GET['dest']));
    $coords = Weather::DESTINATION_COORDS[$slug] ?? null;
    if (!$coords) {
        http_response_code(404);
        echo json_encode(['status'=>'error','message'=>"Unknown destination '$slug'. Valid: ".implode(', ',array_keys(Weather::DESTINATION_COORDS))]);
        exit;
    }
    if (!empty($_GET['forecast'])) {
        echo json_encode(Weather::getForecast($coords['lat'], $coords['lon'], intval($_GET['days'] ?? 3)), JSON_PRETTY_PRINT);
    } else {
        echo json_encode(Weather::getCurrentWeather($coords['lat'], $coords['lon'], $coords['label']), JSON_PRETTY_PRINT);
    }
    exit;
}

// Custom lat/lon
$lat = isset($_GET['lat']) ? floatval($_GET['lat']) : null;
$lon = isset($_GET['lon']) ? floatval($_GET['lon']) : null;
if ($lat === null || $lon === null) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'Provide lat & lon, dest slug, or all=1',
        'examples'=>['current'=>'?lat=10.089&lon=77.060','forecast'=>'?lat=10.089&lon=77.060&forecast=1','dest'=>'?dest=munnar','all'=>'?all=1']]);
    exit;
}

$name = trim($_GET['name'] ?? '');
if (!empty($_GET['forecast'])) {
    echo json_encode(Weather::getForecast($lat, $lon, intval($_GET['days'] ?? 3)), JSON_PRETTY_PRINT);
} else {
    echo json_encode(Weather::getCurrentWeather($lat, $lon, $name), JSON_PRETTY_PRINT);
}
?>
