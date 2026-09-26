<?php
// api/carbon_estimate.php — Flight Carbon Emissions Estimation Endpoint
// Proxies requests to the Google Travel Impact Model API server-side.
// POST: { origin, destination, carrierCode, flightNumber, departureDate: {year, month, day} }
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

require_once __DIR__ . '/../services/CarbonEstimate.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    http_response_code(400);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Invalid JSON body. Required fields: origin, destination, carrierCode, flightNumber, departureDate'
    ]);
    exit;
}

$origin      = trim($data['origin'] ?? '');
$destination = trim($data['destination'] ?? '');
$carrierCode = trim($data['carrierCode'] ?? '');
$flightNumber = intval($data['flightNumber'] ?? 0);
$departureDate = $data['departureDate'] ?? [];

// Validate required fields
$errors = [];
if (empty($origin))       $errors[] = 'origin is required (IATA code, e.g. "BOM")';
if (empty($destination))  $errors[] = 'destination is required (IATA code, e.g. "DEL")';
if (empty($carrierCode))  $errors[] = 'carrierCode is required (e.g. "AI")';
if ($flightNumber <= 0)   $errors[] = 'flightNumber must be a positive integer';
if (empty($departureDate) || !isset($departureDate['year'])) {
    $errors[] = 'departureDate is required as { year, month, day }';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Validation failed',
        'errors'  => $errors
    ]);
    exit;
}

$result = CarbonEstimate::getFlightEmissions(
    $origin,
    $destination,
    $carrierCode,
    $flightNumber,
    $departureDate
);

echo json_encode($result, JSON_PRETTY_PRINT);
?>
