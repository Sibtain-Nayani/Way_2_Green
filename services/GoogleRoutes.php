<?php
// services/GoogleRoutes.php — Google Routes API Service
// Provides turn-by-turn routing with distance, duration, and polyline.
// Uses Google's free monthly credit (billing account required but no charge for typical usage).
// API Docs: https://developers.google.com/maps/documentation/routes

require_once __DIR__ . '/EnvLoader.php';
require_once __DIR__ . '/HttpClient.php';

class GoogleRoutes
{
    private const API_URL = 'https://routes.googleapis.com/directions/v2:computeRoutes';

    /**
     * Valid travel modes for the Google Routes API.
     */
    private const TRAVEL_MODES = ['DRIVE', 'TRANSIT', 'WALK', 'BICYCLE', 'TWO_WHEELER'];

    /**
     * Compute a route between an origin and destination using Google Routes API.
     *
     * @param string $origin     Address or place name (e.g. "Mumbai, India")
     * @param string $destination Address or place name (e.g. "Goa, India")
     * @param string $travelMode Travel mode: DRIVE, TRANSIT, WALK, BICYCLE, TWO_WHEELER
     * @return array Route result with distance, duration, polyline, or error
     */
    public static function computeRoute(
        string $origin,
        string $destination,
        string $travelMode = 'DRIVE'
    ): array {
        $apiKey = EnvLoader::get('GOOGLE_ROUTES_API_KEY');

        if (empty($apiKey)) {
            return [
                'status'  => 'error',
                'message' => 'GOOGLE_ROUTES_API_KEY is not configured. Add it to your .env file.',
                'help'    => 'Enable "Routes API" in your Google Cloud project and create an API key.'
            ];
        }

        $travelMode = strtoupper($travelMode);
        if (!in_array($travelMode, self::TRAVEL_MODES, true)) {
            return [
                'status'       => 'error',
                'message'      => "Invalid travel mode '$travelMode'.",
                'valid_modes'  => self::TRAVEL_MODES
            ];
        }

        $payload = [
            'origin' => [
                'address' => $origin
            ],
            'destination' => [
                'address' => $destination
            ],
            'travelMode'          => $travelMode,
            'computeAlternativeRoutes' => false,
            'languageCode'        => 'en',
            'units'               => 'METRIC'
        ];

        // Field mask tells the API which fields to return (saves quota)
        $fieldMask = 'routes.duration,routes.distanceMeters,routes.polyline.encodedPolyline,routes.legs.duration,routes.legs.distanceMeters,routes.legs.startLocation,routes.legs.endLocation,routes.travelAdvisory';

        $response = HttpClient::postJson(
            self::API_URL,
            $payload,
            [
                'X-Goog-Api-Key: ' . $apiKey,
                'X-Goog-FieldMask: ' . $fieldMask
            ],
            10
        );

        $data = HttpClient::decodeJson($response);

        if (!$data || isset($data['error'])) {
            $errMsg = $data['error']['message'] ?? 'Google Routes API request failed';
            return [
                'status'      => 'error',
                'message'     => $errMsg,
                'http_status' => $response['status'],
                'error'       => $response['error']
            ];
        }

        $route = $data['routes'][0] ?? null;
        if (!$route) {
            return [
                'status'  => 'error',
                'message' => 'No route found between the given locations.'
            ];
        }

        $distanceMeters = $route['distanceMeters'] ?? 0;
        $durationStr    = $route['duration'] ?? '0s'; // e.g. "12345s"
        $durationSecs   = intval(str_replace('s', '', $durationStr));

        return [
            'status'     => 'success',
            'source'     => 'Google Routes API',
            'travelMode' => $travelMode,
            'route'      => [
                'origin'        => $origin,
                'destination'   => $destination,
                'distance_km'   => round($distanceMeters / 1000, 2),
                'distance_text' => self::formatDistance($distanceMeters),
                'duration_min'  => round($durationSecs / 60, 1),
                'duration_text' => self::formatDuration($durationSecs),
                'polyline'      => $route['polyline']['encodedPolyline'] ?? null,
                'legs'          => $route['legs'] ?? []
            ]
        ];
    }

    /**
     * Compare multiple travel modes for the same origin-destination.
     *
     * @param string $origin
     * @param string $destination
     * @param array  $modes Array of travel modes to compare
     * @return array Multi-mode comparison result
     */
    public static function compareRoutes(
        string $origin,
        string $destination,
        array $modes = ['DRIVE', 'TRANSIT', 'WALK']
    ): array {
        $results = [];
        foreach ($modes as $mode) {
            $results[] = self::computeRoute($origin, $destination, $mode);
        }

        return [
            'status'      => 'success',
            'source'      => 'Google Routes API',
            'origin'      => $origin,
            'destination' => $destination,
            'comparisons' => $results
        ];
    }

    /**
     * Format distance in meters to a human-readable string.
     */
    private static function formatDistance(int $meters): string
    {
        if ($meters >= 1000) {
            return round($meters / 1000, 1) . ' km';
        }
        return $meters . ' m';
    }

    /**
     * Format duration in seconds to a human-readable string.
     */
    private static function formatDuration(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $mins  = round(($seconds % 3600) / 60);

        if ($hours > 0) {
            return "{$hours}h {$mins}m";
        }
        return "{$mins} min";
    }
}
?>
