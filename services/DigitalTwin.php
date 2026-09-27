<?php
// services/DigitalTwin.php — AI Digital Twin Simulation Engine
// Integrates weather, accessibility, routing, and tourism data to simulate
// how weather-driven changes propagate through the Way2Green ecosystem.
// Supports what-if scenario modelling for the hackathon Task 1 requirements.

require_once __DIR__ . '/EnvLoader.php';
require_once __DIR__ . '/HttpClient.php';
require_once __DIR__ . '/Weather.php';

class DigitalTwin
{
    /**
     * Run a complete Digital Twin simulation for a destination.
     * Integrates live weather + what-if modifiers to estimate cascading effects.
     *
     * @param float  $lat               Destination latitude
     * @param float  $lon               Destination longitude
     * @param string $destName          Human-readable destination name
     * @param array  $whatIf            Optional what-if overrides:
     *                                  ['rain_mm' => 50, 'temp_c' => 45, 'wind_mps' => 20,
     *                                   'condition' => 'Thunderstorm', 'flood_depth_cm' => 30]
     * @return array Full Digital Twin state with cascading effect propagation
     */
    public static function runSimulation(
        float $lat,
        float $lon,
        string $destName = '',
        array $whatIf = []
    ): array {
        // ── Step 1: Get live weather baseline ──────────────────────────────
        $liveWeather = Weather::getCurrentWeather($lat, $lon, $destName);
        $forecast = Weather::getForecast($lat, $lon, 3);

        // ── Step 2: Apply what-if overrides ────────────────────────────────
        $simWeather = self::applyWhatIf($liveWeather, $whatIf);

        // ── Step 3: Layer 1 — Direct effects (primary impacts) ─────────────
        $direct = self::computeDirectEffects($simWeather);

        // ── Step 4: Layer 2 — Cascading effects (secondary impacts) ──────
        $cascading = self::computeCascadingEffects($direct, $simWeather);

        // ── Step 5: Layer 3 — Tertiary / higher-order effects ─────────────
        $tertiary = self::computeTertiaryEffects($cascading, $direct);

        // ── Step 6: Probabilistic uncertainty bounds ───────────────────────
        $uncertainty = self::computeUncertainty($simWeather, $whatIf);

        // ── Step 7: Nugen/Gemini AI narrative generation ───────────────────
        $narrative = self::generateNarrative($destName, $simWeather, $direct, $cascading, $whatIf);

        return [
            'status' => 'success',
            'simulation_type' => empty($whatIf) ? 'live' : 'what-if',
            'destination' => $destName,
            'lat' => $lat,
            'lon' => $lon,
            'generated_at' => date('c'),
            'live_weather' => $liveWeather,
            'simulated_weather' => $simWeather,
            'forecast_summary' => self::summarizeForecast($forecast),
            'what_if_params' => $whatIf,
            'layers' => [
                'direct' => $direct,
                'cascading' => $cascading,
                'tertiary' => $tertiary
            ],
            'uncertainty' => $uncertainty,
            'ai_narrative' => $narrative,
            'green_score_impact' => self::computeGreenScoreImpact($direct, $cascading)
        ];
    }

    /**
     * Run what-if scenario comparisons across multiple weather intensities.
     * Returns an array of simulation snapshots for slider/animation use.
     *
     * @param float  $lat
     * @param float  $lon
     * @param string $destName
     * @param string $parameter  Which parameter to sweep: 'rain_mm', 'temp_c', 'wind_mps'
     * @param array  $values     Array of values to sweep through
     * @return array Array of simulation results, one per value
     */
    public static function runParameterSweep(
        float $lat,
        float $lon,
        string $destName,
        string $parameter,
        array $values
    ): array {
        $snapshots = [];
        foreach ($values as $value) {
            $whatIf = [$parameter => floatval($value)];
            // Auto-set condition based on rain
            if ($parameter === 'rain_mm') {
                if ($value > 30)
                    $whatIf['condition'] = 'Thunderstorm';
                elseif ($value > 10)
                    $whatIf['condition'] = 'Rain';
                elseif ($value > 1)
                    $whatIf['condition'] = 'Drizzle';
                else
                    $whatIf['condition'] = 'Clear';
            }
            $sim = self::runSimulation($lat, $lon, $destName, $whatIf);
            $snapshots[] = [
                'parameter_value' => $value,
                'parameter' => $parameter,
                'direct_effects' => $sim['layers']['direct'],
                'green_score' => $sim['green_score_impact'],
                'severity' => $sim['simulated_weather']['eco_impact']['severity'] ?? 'normal'
            ];
        }

        return [
            'status' => 'success',
            'parameter' => $parameter,
            'values' => $values,
            'snapshots' => $snapshots
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    // Private: What-If Application
    // ──────────────────────────────────────────────────────────────────────

    private static function applyWhatIf(array $weather, array $whatIf): array
    {
        if (empty($whatIf))
            return $weather;

        $sim = $weather; // deep enough copy for our structure
        if (isset($whatIf['rain_mm'])) {
            $sim['rain_1h_mm'] = floatval($whatIf['rain_mm']);
        }
        if (isset($whatIf['temp_c'])) {
            $sim['temperature']['current'] = floatval($whatIf['temp_c']);
        }
        if (isset($whatIf['wind_mps'])) {
            $sim['wind_speed'] = floatval($whatIf['wind_mps']);
        }
        if (isset($whatIf['condition'])) {
            $sim['weather']['condition'] = $whatIf['condition'];
        }
        if (isset($whatIf['flood_depth_cm'])) {
            $sim['flood_depth_cm'] = floatval($whatIf['flood_depth_cm']);
        }
        if (isset($whatIf['humidity'])) {
            $sim['humidity'] = intval($whatIf['humidity']);
        }

        // Recompute eco_impact based on modified values
        $rawData = [
            'main' => [
                'temp' => $sim['temperature']['current'] ?? 20,
                'humidity' => $sim['humidity'] ?? 60
            ],
            'wind' => ['speed' => $sim['wind_speed'] ?? 2],
            'rain' => ['1h' => $sim['rain_1h_mm'] ?? 0],
            'snow' => ['1h' => $sim['snow_1h_mm'] ?? 0],
            'weather' => [['main' => $sim['weather']['condition'] ?? 'Clear']],
            'clouds' => ['all' => $sim['cloudiness'] ?? 0]
        ];
        // Use Weather's eco impact computation (access via public method trick)
        $sim['eco_impact'] = self::recomputeEcoImpact($rawData);
        $sim['is_simulated'] = true;
        $sim['what_if'] = $whatIf;

        return $sim;
    }

    private static function recomputeEcoImpact(array $data): array
    {
        $temp = $data['main']['temp'] ?? 20;
        $wind = $data['wind']['speed'] ?? 2;
        $rain = $data['rain']['1h'] ?? 0;
        $snow = $data['snow']['1h'] ?? 0;
        $cond = strtolower($data['weather'][0]['main'] ?? 'clear');
        $clouds = $data['clouds']['all'] ?? 0;

        $travelDemand = 1.0;
        if ($rain > 10)
            $travelDemand -= 0.4;
        elseif ($rain > 2)
            $travelDemand -= 0.2;
        if ($snow > 5)
            $travelDemand -= 0.5;
        if ($wind > 15)
            $travelDemand -= 0.25;
        if ($temp > 42 || $temp < -5)
            $travelDemand -= 0.35;
        if (in_array($cond, ['thunderstorm', 'squall', 'tornado']))
            $travelDemand -= 0.6;
        $travelDemand = max(0, round($travelDemand, 2));

        $hotelOccupancy = 1.0;
        if (in_array($cond, ['thunderstorm', 'tornado']))
            $hotelOccupancy += 0.25;
        if ($rain > 5)
            $hotelOccupancy += 0.1;
        if ($temp > 40)
            $hotelOccupancy -= 0.2;
        $hotelOccupancy = max(0, min(1.5, round($hotelOccupancy, 2)));

        $solarEfficiency = max(0, round(100 - ($clouds * 0.85) - ($rain * 3), 0));
        $roadAccess = 1.0;
        if ($snow > 10)
            $roadAccess = 0.2;
        elseif ($snow > 3)
            $roadAccess = 0.5;
        if ($rain > 30)
            $roadAccess = min($roadAccess, 0.4);
        if (in_array($cond, ['tornado', 'squall']))
            $roadAccess = 0;
        $roadAccess = max(0, round($roadAccess, 2));

        $severity = 'normal';
        if (in_array($cond, ['tornado', 'squall']))
            $severity = 'extreme';
        elseif (in_array($cond, ['thunderstorm']))
            $severity = 'severe';
        elseif ($rain > 20 || $snow > 10 || $wind > 20)
            $severity = 'high';
        elseif ($rain > 5 || $snow > 3 || $wind > 10)
            $severity = 'moderate';

        $carbonMultiplier = 1.0 + ($rain * 0.01) + ($snow * 0.02) + (max(0, $wind - 10) * 0.005);

        return [
            'travel_demand_factor' => $travelDemand,
            'hotel_occupancy_factor' => $hotelOccupancy,
            'solar_efficiency_pct' => $solarEfficiency,
            'road_accessibility' => $roadAccess,
            'carbon_multiplier' => round($carbonMultiplier, 3),
            'severity' => $severity,
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    // Private: Effect Propagation Layers
    // ──────────────────────────────────────────────────────────────────────

    private static function computeDirectEffects(array $simWeather): array
    {
        $eco = $simWeather['eco_impact'] ?? [];
        $rain = $simWeather['rain_1h_mm'] ?? 0;
        $temp = $simWeather['temperature']['current'] ?? 20;
        $wind = $simWeather['wind_speed'] ?? 2;
        $cond = $simWeather['weather']['condition'] ?? 'Clear';
        $flood = $simWeather['flood_depth_cm'] ?? 0;

        return [
            'transportation' => [
                'road_access_factor' => $eco['road_accessibility'] ?? 1.0,
                'flight_delay_risk' => self::flightDelayRisk($wind, $rain, $cond),
                'train_delay_risk' => self::trainDelayRisk($rain, $flood),
                'outdoor_activity' => max(0, $eco['travel_demand_factor'] ?? 1.0),
                'description' => self::transportDesc($eco['road_accessibility'] ?? 1.0, $wind)
            ],
            'accommodation' => [
                'occupancy_factor' => $eco['hotel_occupancy_factor'] ?? 1.0,
                'emergency_demand' => $rain > 20 || $flood > 10 ? 'high' : ($rain > 5 ? 'moderate' : 'low'),
                'solar_power_pct' => $eco['solar_efficiency_pct'] ?? 80,
                'description' => self::hotelDesc($eco['hotel_occupancy_factor'] ?? 1.0)
            ],
            'attractions' => [
                'outdoor_safety' => $eco['travel_demand_factor'] ?? 1.0,
                'closure_risk' => $wind > 15 || $rain > 20 ? 'high' : ($rain > 5 ? 'moderate' : 'low'),
                'visitor_demand_drop' => round((1 - ($eco['travel_demand_factor'] ?? 1.0)) * 100) . '%',
                'description' => self::attractionDesc($eco['severity'] ?? 'normal')
            ],
            'accessibility' => [
                'wheelchair_mobility' => $flood > 5 ? 'severely_limited' : ($rain > 10 ? 'limited' : 'normal'),
                'tactile_path_safety' => $rain > 5 ? 'wet_hazard' : 'safe',
                'guide_dog_conditions' => $temp > 38 ? 'heat_caution' : ($rain > 15 ? 'adverse' : 'good'),
                'description' => self::accessDesc($flood, $rain)
            ],
            'carbon_footprint' => [
                'multiplier' => $eco['carbon_multiplier'] ?? 1.0,
                'extra_emissions_pct' => round(($eco['carbon_multiplier'] - 1.0) * 100, 1) . '%',
                'description' => 'Weather forces more car/taxi use, increasing CO₂ per trip'
            ]
        ];
    }

    private static function computeCascadingEffects(array $direct, array $simWeather): array
    {
        $transportImpact = $direct['transportation']['road_access_factor'];
        $occupancyImpact = $direct['accommodation']['occupancy_factor'];
        $rain = $simWeather['rain_1h_mm'] ?? 0;
        $temp = $simWeather['temperature']['current'] ?? 20;

        return [
            'restaurant_demand' => [
                'factor' => min(1.5, $occupancyImpact * 0.9 + ($rain > 10 ? 0.3 : 0)),
                'description' => $rain > 10
                    ? 'Stranded travelers boost restaurant demand; outdoor seating closed'
                    : 'Normal demand patterns'
            ],
            'workforce_availability' => [
                'factor' => $transportImpact < 0.5 ? 0.6 : ($transportImpact < 0.8 ? 0.85 : 1.0),
                'description' => $transportImpact < 0.5
                    ? 'Staff transportation disrupted; 40% reduction in workforce capacity'
                    : 'Minor transport disruptions; slight workforce delays possible'
            ],
            'water_resource_stress' => [
                'factor' => $rain > 30 ? 1.5 : ($rain < 1 && $temp > 35 ? 0.6 : 1.0),
                'description' => $rain > 30
                    ? 'Flood risk strains drainage; backup water supply needed'
                    : ($temp > 35 ? 'Heat wave increases water consumption significantly' : 'Normal water usage')
            ],
            'event_viability' => [
                'score' => round($transportImpact * 0.5 + (1.0 - min(1, $rain / 30)) * 0.5, 2),
                'description' => $rain > 15 || $transportImpact < 0.5
                    ? 'Outdoor events at high cancellation risk'
                    : 'Events can proceed with weather monitoring'
            ],
            'eco_hotel_operations' => [
                'solar_backup_needed' => $direct['accommodation']['solar_power_pct'] < 40,
                'water_harvest_boost' => $rain > 10,
                'compost_disruption' => $rain > 30,
                'description' => self::ecoHotelDesc($direct['accommodation']['solar_power_pct'], $rain)
            ]
        ];
    }

    private static function computeTertiaryEffects(array $cascading, array $direct): array
    {
        $workforce = $cascading['workforce_availability']['factor'];
        $eventScore = $cascading['event_viability']['score'];
        $waterStress = $cascading['water_resource_stress']['factor'];

        return [
            'local_economy_impact' => [
                'revenue_factor' => round($workforce * $eventScore * 0.9, 2),
                'description' => 'Reduced workforce + event cancellations compound revenue loss'
            ],
            'sustainability_score_shift' => [
                'delta' => round(($waterStress > 1.2 ? -15 : ($waterStress < 0.8 ? -8 : 0))
                    + ($direct['accommodation']['solar_power_pct'] < 40 ? -12 : 0), 0),
                'description' => 'Renewable energy reduction and resource stress lower eco-score'
            ],
            'tourist_sentiment' => [
                'index' => round($eventScore * 80 + $direct['transportation']['road_access_factor'] * 20),
                'description' => 'Traveler confidence composite score (0-100)'
            ],
            'recovery_time_hours' => [
                'estimate' => self::estimateRecovery($direct, $cascading),
                'description' => 'Estimated hours for system to return to normal operations'
            ]
        ];
    }

    private static function computeUncertainty(array $simWeather, array $whatIf): array
    {
        $isWhatIf = !empty($whatIf);
        $severity = $simWeather['eco_impact']['severity'] ?? 'normal';

        // Higher uncertainty for what-if scenarios and extreme conditions
        $baseUncertainty = $isWhatIf ? 0.25 : 0.10;
        $severityMult = ['extreme' => 1.8, 'severe' => 1.5, 'high' => 1.3, 'moderate' => 1.1, 'normal' => 1.0];
        $mult = $severityMult[$severity] ?? 1.0;

        return [
            'confidence_pct' => round((1 - $baseUncertainty * $mult) * 100),
            'uncertainty_band' => round($baseUncertainty * $mult * 100) . '%',
            'model_type' => $isWhatIf ? 'counterfactual' : 'live',
            'note' => $isWhatIf
                ? 'What-if scenario: outputs are probabilistic estimates'
                : 'Live simulation: uncertainty reflects forecast model accuracy'
        ];
    }

    private static function generateNarrative(
        string $dest,
        array $simWeather,
        array $direct,
        array $cascading,
        array $whatIf
    ): array {
        $apiKey = EnvLoader::get('GEMINI_API_KEY');
        $cond = $simWeather['weather']['condition'] ?? 'Clear';
        $temp = $simWeather['temperature']['current'] ?? 20;
        $rain = $simWeather['rain_1h_mm'] ?? 0;
        $severity = $simWeather['eco_impact']['severity'] ?? 'normal';

        $isWhatIf = !empty($whatIf);
        $context = $isWhatIf
            ? "WHAT-IF scenario: " . json_encode($whatIf)
            : "LIVE weather: {$cond}, {$temp}°C, {$rain}mm/hr rain";

        $prompt = "You are the Way2Green Digital Twin AI for sustainable & accessible hospitality in India.
{$context} at destination: {$dest}.

Weather severity: {$severity}.
Direct impacts:
- Transport access: " . ($direct['transportation']['road_access_factor'] * 100) . "%
- Hotel occupancy factor: " . ($direct['accommodation']['occupancy_factor']) . "
- Outdoor attraction safety: " . ($direct['attractions']['outdoor_safety']) . "
- Accessibility (wheelchair): " . ($direct['accessibility']['wheelchair_mobility']) . "

Cascading effects:
- Restaurant demand: " . ($cascading['restaurant_demand']['factor']) . "
- Workforce availability: " . ($cascading['workforce_availability']['factor'] * 100) . "%
- Event viability score: " . ($cascading['event_viability']['score']) . "

Generate a concise Digital Twin situational briefing in JSON:
{
  \"headline\": \"one sentence summary\",
  \"transport_advisory\": \"one sentence\",
  \"accessibility_advisory\": \"one sentence on wheelchair/inclusive access\",
  \"eco_advisory\": \"one sentence on solar/sustainability impact\",
  \"recommended_actions\": [\"action1\", \"action2\", \"action3\"],
  \"recovery_outlook\": \"one sentence\"
}";

        if (!empty($apiKey)) {
            $r = HttpClient::postJson(
                "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . urlencode($apiKey),
                [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => ['responseMimeType' => 'application/json', 'temperature' => 0.6]
                ],
                ['Content-Type: application/json'],
                8
            );
            $parsed = HttpClient::decodeJson($r);
            if ($parsed) {
                $text = $parsed['candidates'][0]['content']['parts'][0]['text'] ?? '';
                $json = json_decode($text, true);
                if ($json) {
                    return array_merge(['source' => 'Gemini 1.5 Flash (Live)'], $json);
                }
            }
        }

        // Smart fallback narrative
        return [
            'source' => 'Way2Green Digital Twin Engine',
            'headline' => self::narrativeHeadline($dest, $severity, $cond),
            'transport_advisory' => $direct['transportation']['description'],
            'accessibility_advisory' => $direct['accessibility']['description'],
            'eco_advisory' => $direct['accommodation']['solar_power_pct'] < 40
                ? "Solar generation reduced to {$direct['accommodation']['solar_power_pct']}% — backup power activated."
                : "Renewable systems operating within normal range despite weather.",
            'recommended_actions' => self::recommendedActions($severity, $direct, $cascading),
            'recovery_outlook' => "System expected to recover within " . self::estimateRecovery($direct, $cascading) . " hours."
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    // Private: Helper Descriptors
    // ──────────────────────────────────────────────────────────────────────

    private static function flightDelayRisk(float $wind, float $rain, string $cond): string
    {
        if (in_array(strtolower($cond), ['thunderstorm', 'tornado']))
            return 'high';
        if ($wind > 20 || $rain > 20)
            return 'high';
        if ($wind > 12 || $rain > 8)
            return 'moderate';
        return 'low';
    }

    private static function trainDelayRisk(float $rain, float $flood): string
    {
        if ($flood > 20 || $rain > 40)
            return 'high';
        if ($rain > 15 || $flood > 5)
            return 'moderate';
        return 'low';
    }

    private static function transportDesc(float $road, float $wind): string
    {
        if ($road < 0.3)
            return "Roads severely impacted — vehicle movement unsafe. Emergency services only.";
        if ($road < 0.7)
            return "Partial road closures. Significant delays expected. Plan for 2-3x travel time.";
        if ($wind > 15)
            return "Strong winds affecting driving safety. Reduce speed on exposed routes.";
        return "Transport conditions within normal parameters.";
    }

    private static function hotelDesc(float $occ): string
    {
        if ($occ > 1.3)
            return "High emergency occupancy. Hotels near capacity — guests may be weather-stranded.";
        if ($occ > 1.0)
            return "Above-normal demand. Eco-resorts receiving increased bookings.";
        if ($occ < 0.7)
            return "Below-normal demand. Opportunities for deep eco-discount promotions.";
        return "Normal occupancy patterns. Eco-resort operations stable.";
    }

    private static function attractionDesc(string $severity): string
    {
        $map = [
            'extreme' => "All outdoor attractions closed. Nature trails, parks unsafe.",
            'severe' => "Most outdoor sites closed or operating at reduced capacity.",
            'high' => "Outdoor activities significantly curtailed. Indoor alternatives advised.",
            'moderate' => "Some outdoor attractions limited. Check with operators before visiting.",
            'normal' => "All eco-attractions operating normally."
        ];
        return $map[$severity] ?? "Conditions normal.";
    }

    private static function accessDesc(float $flood, float $rain): string
    {
        if ($flood > 20)
            return "Severe flooding — all mobility aid routes compromised. Evacuation assistance may be needed.";
        if ($flood > 5)
            return "Minor flooding on accessible pathways. Wheelchair users advised to seek indoor routes.";
        if ($rain > 15)
            return "Heavy rain making tactile paths and ramps slippery. Caution advised for mobility aid users.";
        return "Accessibility conditions good — all designated routes operational.";
    }

    private static function ecoHotelDesc(int $solar, float $rain): string
    {
        if ($solar < 20)
            return "Critical: Solar output minimal. Backup generators activated, increasing carbon footprint temporarily.";
        if ($solar < 50)
            return "Solar reduced. Rainwater harvesting boosted. Bio-systems stable.";
        return "Eco-hotel systems operating efficiently. Rainwater harvest active.";
    }

    private static function narrativeHeadline(string $dest, string $severity, string $cond): string
    {
        $labels = [
            'extreme' => "🚨 Extreme {$cond} Emergency at {$dest} — Digital Twin active",
            'severe' => "⚠️ Severe {$cond} Disrupting {$dest} Hospitality Ecosystem",
            'high' => "🌧️ High-Impact {$cond} Affecting {$dest} Operations",
            'moderate' => " Moderate Weather Disruption at {$dest} — Monitoring Active",
            'normal' => "🌿 {$dest} Digital Twin: All Systems Operating Normally"
        ];
        return $labels[$severity] ?? "Digital Twin active for {$dest}";
    }

    private static function recommendedActions(string $severity, array $direct, array $cascading): array
    {
        $actions = [];
        if ($severity === 'extreme' || $severity === 'severe') {
            $actions[] = "Activate emergency response protocols for all eco-properties";
            $actions[] = "Contact all booked guests with weather advisory and indoor alternatives";
            $actions[] = "Deploy backup renewable energy systems immediately";
        }
        if ($direct['transportation']['road_access_factor'] < 0.5) {
            $actions[] = "Arrange alternative shuttle services via safer routes";
        }
        if ($cascading['workforce_availability']['factor'] < 0.8) {
            $actions[] = "Implement remote-check-in and reduced service mode for staff safety";
        }
        if ($direct['accommodation']['solar_power_pct'] < 40) {
            $actions[] = "Activate backup power — prioritize refrigeration and medical equipment";
        }
        $actions[] = "Update accessibility routes for wheelchair users and mobility aid guests";
        return array_slice($actions, 0, 5);
    }

    private static function estimateRecovery(array $direct, array $cascading): int
    {
        $roadFactor = $direct['transportation']['road_access_factor'];
        $workforce = $cascading['workforce_availability']['factor'];
        if ($roadFactor < 0.2)
            return 72;
        if ($roadFactor < 0.5)
            return 24;
        if ($workforce < 0.7)
            return 12;
        if ($roadFactor < 0.8)
            return 6;
        return 2;
    }

    private static function summarizeForecast(array $forecast): array
    {
        if (($forecast['status'] ?? '') !== 'success')
            return ['available' => false];
        $items = $forecast['forecast'] ?? [];
        $maxRain = 0;
        $minTemp = 999;
        $maxTemp = -999;
        $sevCounts = [];
        foreach ($items as $item) {
            $maxRain = max($maxRain, $item['rain_3h_mm'] ?? 0);
            $minTemp = min($minTemp, $item['temp'] ?? 20);
            $maxTemp = max($maxTemp, $item['temp'] ?? 20);
            $sev = $item['eco_impact']['severity'] ?? 'normal';
            $sevCounts[$sev] = ($sevCounts[$sev] ?? 0) + 1;
        }
        arsort($sevCounts);
        return [
            'available' => true,
            'hours_ahead' => count($items) * 3,
            'max_rain_3h_mm' => $maxRain,
            'temp_range' => [$minTemp, $maxTemp],
            'dominant_severity' => array_key_first($sevCounts) ?? 'normal'
        ];
    }

    private static function computeGreenScoreImpact(array $direct, array $cascading): array
    {
        $base = 75; // baseline green score
        $delta = 0;
        $delta += round(($direct['transportation']['road_access_factor'] - 1) * 15);
        $delta += round(($direct['accommodation']['solar_power_pct'] - 70) / 10);
        $delta -= round((1 - ($cascading['workforce_availability']['factor'])) * 10);
        $delta -= $direct['carbon_footprint']['multiplier'] > 1.2 ? 8 : 0;
        $adjusted = max(0, min(100, $base + $delta));
        return [
            'baseline' => $base,
            'adjusted' => $adjusted,
            'delta' => $adjusted - $base,
            'label' => $adjusted >= 70 ? 'Good' : ($adjusted >= 50 ? 'Moderate' : 'Impacted')
        ];
    }
}
?>