<?php
// services/CarbonEstimate.php — Google Travel Impact Model (TIM) Service
// Calls the TIM API to get per-flight CO2e emissions estimates.
// API Docs: https://developers.google.com/travel/impact-model/reference/rest

require_once __DIR__ . '/EnvLoader.php';
require_once __DIR__ . '/HttpClient.php';

class CarbonEstimate
{
    private const API_BASE = 'https://travelimpactmodel.googleapis.com/v1/flights:computeFlightEmissions';

    /**
     * Get CO2e emissions for a specific flight.
     *
     * @param string $origin        IATA airport code (e.g. "BOM")
     * @param string $destination   IATA airport code (e.g. "DEL")
     * @param string $carrierCode   Operating carrier IATA code (e.g. "AI")
     * @param int    $flightNumber  Flight number (e.g. 101)
     * @param array  $departureDate Associative array: ['year' => 2026, 'month' => 9, 'day' => 27]
     * @return array Result with emissions data or error
     */
    public static function getFlightEmissions(
        string $origin,
        string $destination,
        string $carrierCode,
        int $flightNumber,
        array $departureDate
    ): array {
        $apiKey = EnvLoader::get('GOOGLE_TIM_API_KEY');

        if (empty($apiKey)) {
            return self::fallbackEstimate($origin, $destination);
        }

        $url = self::API_BASE . '?key=' . urlencode($apiKey);

        $payload = [
            'flights' => [
                [
                    'origin'               => strtoupper($origin),
                    'destination'           => strtoupper($destination),
                    'operatingCarrierCode'  => strtoupper($carrierCode),
                    'flightNumber'          => $flightNumber,
                    'departureDate'         => [
                        'year'  => (int) ($departureDate['year'] ?? date('Y')),
                        'month' => (int) ($departureDate['month'] ?? date('n')),
                        'day'   => (int) ($departureDate['day'] ?? date('j'))
                    ]
                ]
            ]
        ];

        $response = HttpClient::postJson($url, $payload, [], 8);
        $data = HttpClient::decodeJson($response);

        if (!$data) {
            return [
                'status' => 'error',
                'message' => 'TIM API call failed',
                'http_status' => $response['status'],
                'error' => $response['error'],
                'fallback' => self::fallbackEstimate($origin, $destination)
            ];
        }

        // Extract emissions from API response
        $flightEmission = $data['flightEmissions'][0] ?? null;
        if (!$flightEmission) {
            return [
                'status' => 'error',
                'message' => 'No emission data returned for this flight',
                'raw_response' => $data,
                'fallback' => self::fallbackEstimate($origin, $destination)
            ];
        }

        $emissionsGrams = $flightEmission['emissionsGramsPerPax'] ?? null;
        $modelVersion = $flightEmission['modelVersion'] ?? null;

        return [
            'status' => 'success',
            'source' => 'Google Travel Impact Model (Live)',
            'flight' => [
                'origin'      => strtoupper($origin),
                'destination' => strtoupper($destination),
                'carrier'     => strtoupper($carrierCode),
                'number'      => $flightNumber,
                'date'        => $departureDate
            ],
            'emissions' => [
                'grams_per_pax'  => $emissionsGrams,
                'kg_per_pax'     => $emissionsGrams !== null ? round($emissionsGrams / 1000, 2) : null,
                'model_version'  => $modelVersion
            ]
        ];
    }

    /**
     * Fallback estimation using DEFRA emission factors when API key is unavailable.
     * Uses 0.255 kg CO2 per passenger-km (short/medium haul average).
     *
     * @param string $origin
     * @param string $destination
     * @return array
     */
    private static function fallbackEstimate(string $origin, string $destination): array
    {
        // Common Indian airport-pair approximate distances (km) for demo purposes
        $knownDistances = [
            'BOM-DEL' => 1140, 'DEL-BOM' => 1140,
            'BOM-GOI' => 460,  'GOI-BOM' => 460,
            'BOM-BLR' => 840,  'BLR-BOM' => 840,
            'DEL-BLR' => 1740, 'BLR-DEL' => 1740,
            'BOM-CCU' => 1650, 'CCU-BOM' => 1650,
            'DEL-CCJ' => 2450, 'CCJ-DEL' => 2450,
            'BOM-COK' => 1060, 'COK-BOM' => 1060,
            'DEL-GOI' => 1550, 'GOI-DEL' => 1550,
            'BLR-GOI' => 440,  'GOI-BLR' => 440,
            'BOM-MAA' => 1030, 'MAA-BOM' => 1030,
        ];

        $key = strtoupper($origin) . '-' . strtoupper($destination);
        $distKm = $knownDistances[$key] ?? 800; // Default 800km for unknown pairs
        $factor = 0.255; // kg CO2 per passenger-km (DEFRA short/medium haul average)
        $co2Kg = round($distKm * $factor, 2);

        return [
            'status' => 'success',
            'source' => 'Way2Green Estimated (DEFRA factors)',
            'flight' => [
                'origin'      => strtoupper($origin),
                'destination' => strtoupper($destination)
            ],
            'emissions' => [
                'grams_per_pax'      => round($co2Kg * 1000),
                'kg_per_pax'         => $co2Kg,
                'estimated_distance_km' => $distKm,
                'factor_kg_per_km'   => $factor,
                'model_version'      => 'Way2Green-DEFRA-Fallback'
            ]
        ];
    }
}
?>
