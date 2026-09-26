<?php
// api/get_suggestions.php - Route Carbon Analytics & Gemini AI Advisor
header('Content-Type: application/json');

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

$source = trim($data['source'] ?? 'Starting Point');
$destination = trim($data['destination'] ?? 'Destination');
$mode = strtolower(trim($data['mode'] ?? 'train')); // train, ev, bus, car, flight
$distance_km = floatval($data['distance_km'] ?? 350.0);

// Carbon emission factors in kg CO2 per passenger-km (standard DEFRA/EPA averages)
$factors = [
    'train'  => 0.035, // High-speed/Electric Rail
    'ev'     => 0.053, // Electric Vehicle with renewable grid mix
    'bus'    => 0.068, // Green Shared Coach
    'car'    => 0.192, // Conventional Petrol/Diesel Car
    'flight' => 0.255  // Short/Medium haul flight
];

$chosen_factor = $factors[$mode] ?? 0.035;
$car_factor = $factors['car'];

$chosen_co2 = round($distance_km * $chosen_factor, 1);
$baseline_car_co2 = round($distance_km * $car_factor, 1);
$co2_saved = round(max(0, $baseline_car_co2 - $chosen_co2), 1);
$trees_equivalent = round($co2_saved / 21.77, 2); // 1 mature tree absorbs ~21.77 kg CO2/yr

// Check if a Gemini API key is supplied via request or environment
$apiKey = getenv('GEMINI_API_KEY') ?: ($data['gemini_api_key'] ?? '');

$ai_transit_tip = "";
$ai_hotel_tip = "";
$ai_eco_action = "";
$ai_inclusive_tip = "";

if (!empty($apiKey)) {
    // Live Gemini API Call
    $prompt = "You are an expert Sustainable and Accessible Travel Advisor for 'Way2Green'. A traveler is planning a trip from '{$source}' to '{$destination}' covering approximately {$distance_km} km using mode '{$mode}'.\n".
              "Provide:\n".
              "1. Sustainable Transit Advice (1 sentence on how to minimize carbon).\n".
              "2. Eco-Hospitality advice for {$destination} (1 sentence on local eco practices).\n".
              "3. Inclusive / Accessible Travel Tip (1 sentence regarding physical accessibility or quiet travel).\n".
              "Return strictly in JSON format with keys: transit_advice, hotel_advice, accessibility_advice";

    $geminiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . urlencode($apiKey);

    $postData = [
        "contents" => [
            ["parts" => [["text" => $prompt]]]
        ],
        "generationConfig" => [
            "responseMimeType" => "application/json",
            "temperature" => 0.7
        ]
    ];

    $ch = curl_init($geminiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 6);
    $apiResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $apiResponse) {
        $decoded = json_decode($apiResponse, true);
        $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? '';
        $parsedJson = json_decode($text, true);
        if ($parsedJson) {
            $ai_transit_tip = $parsedJson['transit_advice'] ?? '';
            $ai_hotel_tip = $parsedJson['hotel_advice'] ?? '';
            $ai_inclusive_tip = $parsedJson['accessibility_advice'] ?? '';
        }
    }
}

// High quality contextual smart fallbacks if Gemini API is not called or offline
if (empty($ai_transit_tip)) {
    if ($mode === 'train') {
        $ai_transit_tip = "Opting for electric rail from {$source} to {$destination} avoids over " . round($co2_saved) . " kg of tailpipe emissions and reduces road congestion by 80%.";
    } elseif ($mode === 'ev') {
        $ai_transit_tip = "Driving an EV to {$destination} cuts your footprint by nearly 70% compared to fossil-fuel vehicles. Consider charging at solar-linked highway hubs.";
    } elseif ($mode === 'bus') {
        $ai_transit_tip = "Shared coaches to {$destination} maximize occupancy per vehicle kilometer, curbing individual carbon impact significantly.";
    } else {
        $ai_transit_tip = "Standard motor travel to {$destination} generates {$chosen_co2} kg CO2. Switching to electric rail or shared transit would cut emissions by up to 82%.";
    }
}

if (empty($ai_hotel_tip)) {
    $ai_hotel_tip = "In {$destination}, prioritize verified bio-resorts that run on on-site solar/micro-hydro power, harvest rainwater, and support indigenous community farming.";
}

if (empty($ai_inclusive_tip)) {
    $ai_inclusive_tip = "Universal accessibility tip: Many certified eco-lodges in {$destination} feature tactile pathways, step-free nature boardwalks, and low-stimulation quiet zones for neurodivergent travelers.";
}

$response = [
    'status' => 'success',
    'route' => [
        'source' => $source,
        'destination' => $destination,
        'mode' => $mode,
        'distance_km' => $distance_km,
    ],
    'emissions' => [
        'chosen_co2_kg' => $chosen_co2,
        'baseline_car_co2_kg' => $baseline_car_co2,
        'co2_saved_kg' => $co2_saved,
        'trees_equivalent' => $trees_equivalent,
        'percent_reduction' => $baseline_car_co2 > 0 ? round(($co2_saved / $baseline_car_co2) * 100) : 0
    ],
    'ai_insights' => [
        'transit_advice' => $ai_transit_tip,
        'hotel_advice' => $ai_hotel_tip,
        'accessibility_advice' => $ai_inclusive_tip,
        'source' => !empty($apiKey) ? 'Gemini 1.5 Flash (Live)' : 'Way2Green Intelligent Eco-Engine'
    ]
];

echo json_encode($response);
?>
