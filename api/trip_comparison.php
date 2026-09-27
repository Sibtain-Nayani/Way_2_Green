<?php
// api/trip_comparison.php — Aggregated Trip Comparison Endpoint
// Calls all Way2Green services in parallel-ish fashion and returns
// a unified side-by-side comparison of carbon impact, accessibility,
// routing options, and transit data for a given origin-destination pair.
//
// POST: {
//   origin: "Mumbai, India",
//   destination: "Goa, India",
//   originCoords: { lat: 19.076, lon: 72.877 },
//   destCoords: { lat: 15.299, lon: 74.124 },
//   carrierCode: "AI",       (optional — for flight emissions)
//   flightNumber: 101,       (optional)
//   departureDate: { year: 2026, month: 9, day: 28 }  (optional)
// }
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed. Use POST.']);
    exit;
}

// Load all services
require_once __DIR__ . '/../services/CarbonEstimate.php';
require_once __DIR__ . '/../services/Accessibility.php';
require_once __DIR__ . '/../services/Routing.php';
require_once __DIR__ . '/../services/GoogleRoutes.php';
require_once __DIR__ . '/../services/Transit.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    http_response_code(400);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Invalid JSON body.',
        'usage'   => [
            'origin'      => 'Mumbai, India',
            'destination' => 'Goa, India',
            'originCoords' => ['lat' => 19.076, 'lon' => 72.877],
            'destCoords'   => ['lat' => 15.299, 'lon' => 74.124]
        ]
    ]);
    exit;
}

$origin      = trim($data['origin'] ?? '');
$destination = trim($data['destination'] ?? '');
$originCoords = $data['originCoords'] ?? null;
$destCoords   = $data['destCoords'] ?? null;

if (empty($origin) || empty($destination)) {
    http_response_code(400);
    echo json_encode([
        'status'  => 'error',
        'message' => 'origin and destination are required.'
    ]);
    exit;
}

$comparison = [
    'status'      => 'success',
    'origin'      => $origin,
    'destination' => $destination,
    'generated_at' => date('c'),
    'sections'    => []
];

// ────────────────────────────────────────────
// Section 1: Flight Carbon Emissions
// ────────────────────────────────────────────
$carrierCode   = trim($data['carrierCode'] ?? '');
$flightNumber  = intval($data['flightNumber'] ?? 0);
$departureDate = $data['departureDate'] ?? ['year' => (int)date('Y'), 'month' => (int)date('n'), 'day' => (int)date('j')];

if (!empty($carrierCode) && $flightNumber > 0) {
    // Use explicit IATA codes from request, or extract first 3 alpha chars from place name
    $originIataFlight = $data['originIATA'] ?? substr(preg_replace('/[^A-Z]/', '', strtoupper($origin)), 0, 3);
    $destIataFlight   = $data['destIATA'] ?? substr(preg_replace('/[^A-Z]/', '', strtoupper($destination)), 0, 3);
    $comparison['sections']['carbon_flight'] = CarbonEstimate::getFlightEmissions(
        $originIataFlight,
        $destIataFlight,
        $carrierCode,
        $flightNumber,
        $departureDate
    );
} else {
    // Use origin/destination as IATA codes or first 3 chars as rough fallback
    $originIata = $data['originIATA'] ?? substr(preg_replace('/[^A-Z]/', '', strtoupper($origin)), 0, 3);
    $destIata   = $data['destIATA'] ?? substr(preg_replace('/[^A-Z]/', '', strtoupper($destination)), 0, 3);
    $comparison['sections']['carbon_estimate'] = CarbonEstimate::getFlightEmissions(
        $originIata,
        $destIata,
        'XX', // Generic carrier
        0,
        $departureDate
    );
}

// ────────────────────────────────────────────
// Section 2: Accessibility at Destination
// ────────────────────────────────────────────
if ($destCoords && isset($destCoords['lat'], $destCoords['lon'])) {
    $comparison['sections']['accessibility'] = Accessibility::getAccessiblePlaces(
        floatval($destCoords['lat']),
        floatval($destCoords['lon']),
        2000, // 2km radius for destination area
        30
    );
} else {
    $comparison['sections']['accessibility'] = [
        'status'  => 'skipped',
        'message' => 'destCoords (lat/lon) not provided — accessibility lookup requires coordinates.'
    ];
}

// ────────────────────────────────────────────
// Section 3: Multi-Modal Routing (OpenRouteService)
// ────────────────────────────────────────────
if ($originCoords && $destCoords) {
    $startOrs = [floatval($originCoords['lon']), floatval($originCoords['lat'])];
    $endOrs   = [floatval($destCoords['lon']), floatval($destCoords['lat'])];
    $comparison['sections']['routing_ors'] = Routing::compareRoutes($startOrs, $endOrs);
} else {
    $comparison['sections']['routing_ors'] = [
        'status'  => 'skipped',
        'message' => 'originCoords and destCoords required for ORS routing.'
    ];
}

// ────────────────────────────────────────────
// Section 4: Google Routes Comparison
// ────────────────────────────────────────────
$comparison['sections']['routing_google'] = GoogleRoutes::compareRoutes(
    $origin,
    $destination,
    ['DRIVE', 'TRANSIT', 'WALK']
);

// ────────────────────────────────────────────
// Section 5: Transit Stops at Destination
// ────────────────────────────────────────────
if ($destCoords && isset($destCoords['lat'], $destCoords['lon'])) {
    $comparison['sections']['transit'] = Transit::getNearbyStops(
        floatval($destCoords['lat']),
        floatval($destCoords['lon']),
        1000,
        15
    );
} else {
    $comparison['sections']['transit'] = [
        'status'  => 'skipped',
        'message' => 'destCoords (lat/lon) not provided — transit lookup requires coordinates.'
    ];
}

// ────────────────────────────────────────────
// Section 6: Eco Summary / Green Score
// ────────────────────────────────────────────
$comparison['sections']['eco_summary'] = buildEcoSummary($comparison['sections']);

echo json_encode($comparison, JSON_PRETTY_PRINT);

// ──────────────────────────────────────────────────────────
// Helper Functions
// ──────────────────────────────────────────────────────────

/**
 * Build a summary eco score from all collected data.
 */
function buildEcoSummary(array $sections): array
{
    $summary = [
        'green_score' => 0,
        'max_score'   => 100,
        'breakdown'   => [],
        'recommendations' => []
    ];

    // Carbon component (0–30 points)
    $carbonSection = $sections['carbon_flight'] ?? $sections['carbon_estimate'] ?? null;
    if ($carbonSection && $carbonSection['status'] === 'success') {
        $co2Kg = $carbonSection['emissions']['kg_per_pax'] ?? 999;
        if ($co2Kg < 50) {
            $carbonScore = 30;
        } elseif ($co2Kg < 150) {
            $carbonScore = 20;
        } elseif ($co2Kg < 300) {
            $carbonScore = 10;
        } else {
            $carbonScore = 5;
            $summary['recommendations'][] = 'Consider train or bus alternatives to reduce your carbon footprint significantly.';
        }
        $summary['breakdown']['carbon'] = ['score' => $carbonScore, 'max' => 30, 'co2_kg' => $co2Kg];
        $summary['green_score'] += $carbonScore;
    }

    // Accessibility component (0–30 points)
    $accessSection = $sections['accessibility'] ?? null;
    if ($accessSection && $accessSection['status'] === 'success') {
        $stats = $accessSection['stats'] ?? [];
        $totalPlaces = ($stats['yes'] ?? 0) + ($stats['limited'] ?? 0) + ($stats['no'] ?? 0);
        $accessiblePlaces = ($stats['yes'] ?? 0);
        if ($totalPlaces > 0) {
            $ratio = $accessiblePlaces / $totalPlaces;
            $accessScore = min(30, round($ratio * 30));
        } else {
            $accessScore = 10; // Some base score if no data
        }
        $summary['breakdown']['accessibility'] = ['score' => $accessScore, 'max' => 30, 'accessible_places' => $accessiblePlaces, 'total_places' => $totalPlaces];
        $summary['green_score'] += $accessScore;

        if ($accessiblePlaces < 5) {
            $summary['recommendations'][] = 'This destination has limited wheelchair-accessible venues. Plan ahead for accommodation.';
        }
    }

    // Transit component (0–20 points)
    $transitSection = $sections['transit'] ?? null;
    if ($transitSection && $transitSection['status'] === 'success') {
        $stopCount = $transitSection['count'] ?? 0;
        if ($stopCount >= 10) {
            $transitScore = 20;
        } elseif ($stopCount >= 5) {
            $transitScore = 15;
        } elseif ($stopCount >= 1) {
            $transitScore = 10;
        } else {
            $transitScore = 0;
            $summary['recommendations'][] = 'Limited public transit at this destination. Consider EV rental or shared rides.';
        }
        $summary['breakdown']['transit'] = ['score' => $transitScore, 'max' => 20, 'nearby_stops' => $stopCount];
        $summary['green_score'] += $transitScore;
    }

    // Routing diversity component (0–20 points)
    $routingSection = $sections['routing_ors'] ?? null;
    if ($routingSection && $routingSection['status'] === 'success') {
        $routeCount = count($routingSection['routes'] ?? []);
        $routeScore = min(20, $routeCount * 7);
        $summary['breakdown']['routing_options'] = ['score' => $routeScore, 'max' => 20, 'available_modes' => $routeCount];
        $summary['green_score'] += $routeScore;
    }

    // Cap at 100
    $summary['green_score'] = min(100, $summary['green_score']);

    // Overall label
    if ($summary['green_score'] >= 80) {
        $summary['label'] = 'Excellent Green Trip';
        $summary['emoji'] = '🌿';
    } elseif ($summary['green_score'] >= 60) {
        $summary['label'] = 'Good Green Choice';
        $summary['emoji'] = '🌱';
    } elseif ($summary['green_score'] >= 40) {
        $summary['label'] = 'Moderate Impact';
        $summary['emoji'] = '🍃';
    } else {
        $summary['label'] = 'Room for Improvement';
        $summary['emoji'] = '⚠️';
    }

    return $summary;
}
?>
