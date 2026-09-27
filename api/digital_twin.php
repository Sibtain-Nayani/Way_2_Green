<?php
// api/digital_twin.php — Digital Twin Simulation API
// POST: { lat, lon, dest, whatIf: { rain_mm, temp_c, wind_mps, condition, flood_depth_cm } }
// POST: { lat, lon, dest, sweep: true, parameter: "rain_mm", values: [0,5,15,30,60] }
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['status'=>'error','message'=>'Use POST']); exit; }

require_once __DIR__ . '/../services/DigitalTwin.php';
require_once __DIR__ . '/../services/Weather.php';

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'Invalid JSON',
        'example'=>['lat'=>10.089,'lon'=>77.060,'dest'=>'Munnar, Kerala','whatIf'=>['rain_mm'=>50,'condition'=>'Thunderstorm']]]);
    exit;
}

// Resolve destination coordinates
$lat  = floatval($data['lat'] ?? 0);
$lon  = floatval($data['lon'] ?? 0);
$dest = trim($data['dest'] ?? '');

// Allow destination slug shortcut
if ($lat === 0.0 && $lon === 0.0 && !empty($data['dest_slug'])) {
    $slug = strtolower($data['dest_slug']);
    $coords = Weather::DESTINATION_COORDS[$slug] ?? null;
    if ($coords) { $lat = $coords['lat']; $lon = $coords['lon']; $dest = $dest ?: $coords['label']; }
}

if ($lat === 0.0 || $lon === 0.0) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'lat and lon are required (or use dest_slug)']);
    exit;
}

// Parameter sweep mode
if (!empty($data['sweep'])) {
    $param  = $data['parameter'] ?? 'rain_mm';
    $values = $data['values'] ?? [0, 5, 15, 30, 60, 100];
    $valid  = ['rain_mm','temp_c','wind_mps','flood_depth_cm','humidity'];
    if (!in_array($param, $valid)) {
        http_response_code(400); echo json_encode(['status'=>'error','message'=>"Invalid parameter. Use: ".implode(', ',$valid)]); exit;
    }
    echo json_encode(DigitalTwin::runParameterSweep($lat, $lon, $dest, $param, $values), JSON_PRETTY_PRINT);
    exit;
}

// Normal simulation (live or what-if)
$whatIf = $data['whatIf'] ?? [];
echo json_encode(DigitalTwin::runSimulation($lat, $lon, $dest, $whatIf), JSON_PRETTY_PRINT);
?>
