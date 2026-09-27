<?php
// test_apis.php — Way2Green API Key Verification Script
// Run from project root via XAMPP: C:\xampp\php\php.exe test_apis.php
// Or hit via browser: http://localhost/Way_2_Green/test_apis.php

// Load .env
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        $eqPos = strpos($line, '=');
        if ($eqPos === false) continue;
        $key = trim(substr($line, 0, $eqPos));
        $value = trim(substr($line, $eqPos + 1));
        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
}

$isCli = php_sapi_name() === 'cli';
$nl    = $isCli ? "\n" : "<br>";
$bold  = fn($t) => $isCli ? "\033[1m$t\033[0m" : "<b>$t</b>";
$green = fn($t) => $isCli ? "\033[32m$t\033[0m" : "<span style='color:#16a34a'>$t</span>";
$red   = fn($t) => $isCli ? "\033[31m$t\033[0m" : "<span style='color:#dc2626'>$t</span>";
$yellow= fn($t) => $isCli ? "\033[33m$t\033[0m" : "<span style='color:#ca8a04'>$t</span>";
$cyan  = fn($t) => $isCli ? "\033[36m$t\033[0m" : "<span style='color:#0284c7'>$t</span>";

if (!$isCli) {
    echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Way2Green API Test</title>";
    echo "<style>body{font-family:monospace;background:#0f172a;color:#e2e8f0;padding:2rem;max-width:900px;margin:auto}";
    echo "pre{background:#1e293b;padding:1rem;border-radius:8px;overflow:auto;border:1px solid #334155}</style></head><body>";
    echo "<h2 style='color:#4ade80'>🌿 Way2Green — API Key Verification</h2><pre>";
}

function curlPost(string $url, $body, array $headers, int $timeout = 8): array {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($body) ? json_encode($body) : $body);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    return ['body' => $resp, 'code' => $code, 'error' => $err];
}

function curlGet(string $url, array $headers = [], int $timeout = 8): array {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    return ['body' => $resp, 'code' => $code, 'error' => $err];
}

$results = [];

echo $bold("════════════════════════════════════════════════════════") . $nl;
echo $bold("  Way2Green — API Key Verification") . $nl;
echo $bold("════════════════════════════════════════════════════════") . $nl . $nl;

// ─────────────────────────────────────────────────────────
// TEST 1: Google Travel Impact Model (TIM)
// ─────────────────────────────────────────────────────────
echo $cyan("▶ TEST 1: Google Travel Impact Model (Carbon Emissions)") . $nl;
$timKey = getenv('GOOGLE_TIM_API_KEY');

if (empty($timKey)) {
    echo $yellow("  ⚠  GOOGLE_TIM_API_KEY is not set in .env") . $nl;
    $results['TIM'] = 'missing';
} else {
    echo "  Key: " . substr($timKey, 0, 8) . "..." . $nl;
    echo "  Calling: POST flights:computeFlightEmissions (AI-101, BOM→DEL, 2026-09-28)" . $nl;

    $r = curlPost(
        "https://travelimpactmodel.googleapis.com/v1/flights:computeFlightEmissions?key=" . urlencode($timKey),
        ['flights' => [[
            'origin' => 'BOM',
            'destination' => 'DEL',
            'operatingCarrierCode' => 'AI',
            'flightNumber' => 101,
            'departureDate' => ['year' => 2026, 'month' => 9, 'day' => 28]
        ]]],
        ['Content-Type: application/json']
    );

    if (!empty($r['error'])) {
        echo $red("  ✗ cURL error: " . $r['error']) . $nl;
        $results['TIM'] = 'curl_error';
    } else {
        $data = json_decode($r['body'], true);
        if ($r['code'] === 200 && isset($data['flightEmissions'])) {
            $em = $data['flightEmissions'][0]['emissionsGramsPerPax'] ?? null;
            $kg = $em ? round($em / 1000, 1) : 'n/a';
            echo $green("  ✓ SUCCESS (HTTP 200)") . $nl;
            echo "  → Emissions: " . ($em ?? 'n/a') . " g/pax  ({$kg} kg CO₂e)" . $nl;
            $results['TIM'] = 'ok';
        } elseif ($r['code'] === 400) {
            $err = $data['error']['message'] ?? 'Bad Request';
            echo $yellow("  ⚠ HTTP 400 — $err") . $nl;
            echo "  → API key is VALID but request data may need adjustment." . $nl;
            $results['TIM'] = 'valid_key';
        } elseif ($r['code'] === 403) {
            $err = $data['error']['message'] ?? 'Forbidden';
            echo $red("  ✗ HTTP 403 — $err") . $nl;
            echo "  → API is NOT enabled. Go to Google Cloud Console → API Library → enable 'Travel Impact Model API'" . $nl;
            $results['TIM'] = 'not_enabled';
        } elseif ($r['code'] === 401) {
            echo $red("  ✗ HTTP 401 — Invalid API key") . $nl;
            $results['TIM'] = 'invalid_key';
        } else {
            echo $red("  ✗ HTTP " . $r['code'] . " — Unexpected response") . $nl;
            echo "  → Raw: " . substr($r['body'], 0, 200) . $nl;
            $results['TIM'] = 'error';
        }
    }
}
echo $nl;

// ─────────────────────────────────────────────────────────
// TEST 2: Google Routes API
// ─────────────────────────────────────────────────────────
echo $cyan("▶ TEST 2: Google Routes API (Mumbai → Goa, DRIVE)") . $nl;
$routesKey = getenv('GOOGLE_ROUTES_API_KEY');

if (empty($routesKey)) {
    echo $yellow("  ⚠  GOOGLE_ROUTES_API_KEY is not set in .env") . $nl;
    $results['Routes'] = 'missing';
} else {
    echo "  Key: " . substr($routesKey, 0, 8) . "..." . $nl;
    echo "  Calling: POST routes.googleapis.com/directions/v2:computeRoutes" . $nl;

    $r = curlPost(
        "https://routes.googleapis.com/directions/v2:computeRoutes",
        [
            'origin'      => ['address' => 'Mumbai, Maharashtra, India'],
            'destination' => ['address' => 'Panaji, Goa, India'],
            'travelMode'  => 'DRIVE',
            'units'       => 'METRIC'
        ],
        [
            'Content-Type: application/json',
            'X-Goog-Api-Key: ' . $routesKey,
            'X-Goog-FieldMask: routes.duration,routes.distanceMeters'
        ]
    );

    if (!empty($r['error'])) {
        echo $red("  ✗ cURL error: " . $r['error']) . $nl;
        $results['Routes'] = 'curl_error';
    } else {
        $data = json_decode($r['body'], true);
        if ($r['code'] === 200 && isset($data['routes'])) {
            $dist  = round(($data['routes'][0]['distanceMeters'] ?? 0) / 1000, 1);
            $durS  = intval(str_replace('s', '', $data['routes'][0]['duration'] ?? '0s'));
            $durH  = floor($durS / 3600);
            $durM  = round(($durS % 3600) / 60);
            echo $green("  ✓ SUCCESS (HTTP 200)") . $nl;
            echo "  → Distance: {$dist} km  |  Drive time: {$durH}h {$durM}m" . $nl;
            $results['Routes'] = 'ok';
        } elseif ($r['code'] === 403) {
            $err = $data['error']['message'] ?? 'Forbidden';
            echo $red("  ✗ HTTP 403 — $err") . $nl;
            echo "  → Enable 'Routes API' in Google Cloud Console and attach a billing account." . $nl;
            $results['Routes'] = 'not_enabled';
        } elseif ($r['code'] === 401) {
            echo $red("  ✗ HTTP 401 — Invalid API key") . $nl;
            $results['Routes'] = 'invalid_key';
        } else {
            echo $red("  ✗ HTTP " . $r['code']) . $nl;
            echo "  → Raw: " . substr($r['body'], 0, 300) . $nl;
            $results['Routes'] = 'error';
        }
    }
}
echo $nl;

// ─────────────────────────────────────────────────────────
// TEST 3: Gemini AI
// ─────────────────────────────────────────────────────────
echo $cyan("▶ TEST 3: Gemini AI (eco-travel tip)") . $nl;
$geminiKey = getenv('GEMINI_API_KEY');

if (empty($geminiKey)) {
    echo $yellow("  ⚠  GEMINI_API_KEY is not set in .env") . $nl;
    $results['Gemini'] = 'missing';
} else {
    echo "  Key: " . substr($geminiKey, 0, 8) . "..." . $nl;
    echo "  Calling: gemini-1.5-flash:generateContent" . $nl;

    $r = curlPost(
        "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . urlencode($geminiKey),
        [
            'contents' => [['parts' => [['text' => 'In one sentence, what is the greenest way to travel from Mumbai to Goa?']]]],
            'generationConfig' => ['temperature' => 0.5, 'maxOutputTokens' => 80]
        ],
        ['Content-Type: application/json']
    );

    if (!empty($r['error'])) {
        echo $red("  ✗ cURL error: " . $r['error']) . $nl;
        $results['Gemini'] = 'curl_error';
    } else {
        $data = json_decode($r['body'], true);
        if ($r['code'] === 200 && isset($data['candidates'])) {
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            echo $green("  ✓ SUCCESS (HTTP 200)") . $nl;
            echo "  → Response: " . trim($text) . $nl;
            $results['Gemini'] = 'ok';
        } elseif ($r['code'] === 400) {
            $err = $data['error']['message'] ?? 'Bad Request';
            echo $red("  ✗ HTTP 400 — $err") . $nl;
            $results['Gemini'] = 'invalid_key';
        } elseif ($r['code'] === 403) {
            $err = $data['error']['message'] ?? 'Forbidden';
            echo $red("  ✗ HTTP 403 — $err") . $nl;
            echo "  → Enable 'Generative Language API' in Google Cloud Console." . $nl;
            $results['Gemini'] = 'not_enabled';
        } else {
            echo $red("  ✗ HTTP " . $r['code']) . $nl;
            echo "  → Raw: " . substr($r['body'], 0, 300) . $nl;
            $results['Gemini'] = 'error';
        }
    }
}
echo $nl;

// ─────────────────────────────────────────────────────────
// TEST 4: OpenStreetMap Overpass (no key needed)
// ─────────────────────────────────────────────────────────
echo $cyan("▶ TEST 4: OpenStreetMap Overpass API (accessibility — no key required)") . $nl;
echo "  Calling: Overpass for wheelchair-tagged places near Munnar (10.089, 77.060)" . $nl;

$query = '[out:json][timeout:10];node["wheelchair"](around:2000,10.089,77.060);out 5;';
$r = curlPost(
    "https://overpass-api.de/api/interpreter",
    'data=' . urlencode($query),
    ['Content-Type: application/x-www-form-urlencoded'],
    12
);

if (!empty($r['error'])) {
    echo $red("  ✗ cURL error: " . $r['error']) . $nl;
    $results['Overpass'] = 'curl_error';
} else {
    $data = json_decode($r['body'], true);
    if ($r['code'] === 200 && isset($data['elements'])) {
        $count = count($data['elements']);
        echo $green("  ✓ SUCCESS (HTTP 200)") . $nl;
        echo "  → Found {$count} wheelchair-tagged place(s) within 2km of Munnar" . $nl;
        if ($count > 0) {
            foreach (array_slice($data['elements'], 0, 3) as $el) {
                $name = $el['tags']['name'] ?? 'Unnamed';
                $wc   = $el['tags']['wheelchair'] ?? '?';
                echo "     • {$name} — wheelchair: {$wc}" . $nl;
            }
        }
        $results['Overpass'] = 'ok';
    } else {
        echo $yellow("  ⚠ HTTP " . $r['code'] . " — Overpass may be busy. Try again.") . $nl;
        $results['Overpass'] = 'error';
    }
}
echo $nl;

// ─────────────────────────────────────────────────────────
// TEST 5: Transitland (no key needed)
// ─────────────────────────────────────────────────────────
echo $cyan("▶ TEST 5: Transitland API (transit stops — no key required)") . $nl;
echo "  Calling: Transitland for stops near Mumbai (19.076, 72.877, radius 500m)" . $nl;

$r = curlGet("https://transit.land/api/v2/rest/stops?lat=19.076&lon=72.877&radius=500&limit=5", [], 10);

if (!empty($r['error'])) {
    echo $red("  ✗ cURL error: " . $r['error']) . $nl;
    $results['Transitland'] = 'curl_error';
} else {
    $data = json_decode($r['body'], true);
    if ($r['code'] === 200 && isset($data['stops'])) {
        $count = count($data['stops']);
        echo $green("  ✓ SUCCESS (HTTP 200)") . $nl;
        echo "  → Found {$count} transit stop(s) within 500m of Mumbai Central" . $nl;
        foreach (array_slice($data['stops'], 0, 3) as $stop) {
            echo "     • " . ($stop['stop_name'] ?? 'Unnamed') . $nl;
        }
        $results['Transitland'] = 'ok';
    } elseif ($r['code'] === 401) {
        echo $yellow("  ⚠ 401 — Rate-limited without API key. Add TRANSITLAND_API_KEY to .env for more quota.") . $nl;
        $results['Transitland'] = 'rate_limited';
    } else {
        echo $yellow("  ⚠ HTTP " . $r['code'] . " — " . substr($r['body'], 0, 100)) . $nl;
        $results['Transitland'] = 'error';
    }
}
echo $nl;

// ─────────────────────────────────────────────────────────
// SUMMARY
// ─────────────────────────────────────────────────────────
echo $bold("════════════════════════════════════════════════════════") . $nl;
echo $bold("  SUMMARY") . $nl;
echo $bold("════════════════════════════════════════════════════════") . $nl;

$icons = ['ok' => '✓', 'valid_key' => '✓', 'missing' => '○', 'not_enabled' => '✗', 'invalid_key' => '✗', 'error' => '✗', 'curl_error' => '✗', 'rate_limited' => '⚠', 'unknown' => '?'];
$labels= ['ok' => 'WORKING', 'valid_key' => 'KEY VALID (API not enabled yet)', 'missing' => 'KEY MISSING', 'not_enabled' => 'API NOT ENABLED', 'invalid_key' => 'INVALID KEY', 'error' => 'ERROR', 'curl_error' => 'NETWORK ERROR', 'rate_limited' => 'RATE LIMITED (add key)', 'unknown' => 'UNKNOWN'];

$map = [
    'TIM'        => 'Google TIM (carbon)',
    'Routes'     => 'Google Routes',
    'Gemini'     => 'Gemini AI',
    'Overpass'   => 'OpenStreetMap (accessibility)',
    'Transitland'=> 'Transitland (transit)'
];

foreach ($map as $k => $label) {
    $status = $results[$k] ?? 'unknown';
    $icon   = $icons[$status]  ?? '?';
    $text   = $labels[$status] ?? 'UNKNOWN';
    $line   = "  $icon  $label — $text";
    if (in_array($status, ['ok', 'valid_key'])) echo $green($line) . $nl;
    elseif (in_array($status, ['missing', 'rate_limited'])) echo $yellow($line) . $nl;
    else echo $red($line) . $nl;
}

echo $nl;

if (!$isCli) echo "</pre></body></html>";
?>
