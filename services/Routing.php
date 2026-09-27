<?php
// services/Routing.php — OpenRouteService Directions API
// Provides multimodal routing (driving, cycling, walking) with distance, duration, and geometry.
// Free tier: generous daily quota, no credit card required.
// API Docs: https://openrouteservice.org/dev/#/api-docs/v2/directions

require_once __DIR__ . '/EnvLoader.php';
require_once __DIR__ . '/HttpClient.php';

class Routing
{
    private const API_BASE = 'https://api.openrouteservice.org/v2/directions/';

    /**
     * Valid ORS profile identifiers.
     */
    private const PROFILES = [
        'driving-car',
        'driving-hgv',
        'cycling-regular',
        'cycling-road',
        'cycling-mountain',
        'cycling-electric',
        'foot-walking',
        'foot-hiking',
        'wheelchair'
    ];

    /**
     * Get a route between two coordinates for a given travel profile.
     *
     * @param array  $start   [longitude, latitude] (ORS uses lon,lat order)
     * @param array  $end     [longitude, latitude]
     * @param string $profile ORS profile name (default: 'driving-car')
     * @return array Route result with distance, duration, geometry, or error
     */
    public static function getRoute(array $start, array $end, string $profile = 'driving-car'): array
    {
        $apiKey = EnvLoader::get('ORS_API_KEY');

        if (empty($apiKey)) {
            return [
                'status'  => 'error',
                'message' => 'ORS_API_KEY is not configured. Add it to your .env file.',
                'help'    => 'Sign up at openrouteservice.org for a free API key.'
            ];
        }

        // Validate profile
        if (!in_array($profile, self::PROFILES, true)) {
            return [
                'status'  => 'error',
                'message' => "Invalid profile '$profile'.",
                'valid_profiles' => self::PROFILES
            ];
        }

        $url = self::API_BASE . $profile;

        $payload = [
            'coordinates' => [$start, $end],
            'instructions' => false,
            'geometry'     => true,
            'units'        => 'km'
        ];

        $response = HttpClient::postJson(
            $url,
            $payload,
            ['Authorization: ' . $apiKey],
            10
        );

        $data = HttpClient::decodeJson($response);

        if (!$data || isset($data['error'])) {
            return [
                'status'      => 'error',
                'message'     => $data['error']['message'] ?? 'ORS API request failed',
                'http_status' => $response['status'],
                'error'       => $response['error']
            ];
        }

        // Parse the first route
        $route = $data['routes'][0] ?? null;
        if (!$route) {
            return [
                'status'  => 'error',
                'message' => 'No route found between the given coordinates.'
            ];
        }

        $summary = $route['summary'] ?? [];

        return [
            'status'  => 'success',
            'source'  => 'OpenRouteService',
            'profile' => $profile,
            'route'   => [
                'distance_km'   => round($summary['distance'] ?? 0, 2),
                'duration_min'  => round(($summary['duration'] ?? 0) / 60, 1),
                'duration_text' => self::formatDuration($summary['duration'] ?? 0),
                'geometry'      => $route['geometry'] ?? null,
                'bbox'          => $route['bbox'] ?? null
            ]
        ];
    }

    /**
     * Get routes for multiple profiles (comparison mode).
     *
     * @param array  $start    [longitude, latitude]
     * @param array  $end      [longitude, latitude]
     * @param array  $profiles Array of profile strings to compare
     * @return array Multi-profile comparison result
     */
    public static function compareRoutes(
        array $start,
        array $end,
        array $profiles = ['driving-car', 'cycling-regular', 'foot-walking']
    ): array {
        $results = [];

        foreach ($profiles as $profile) {
            $results[] = self::getRoute($start, $end, $profile);
        }

        return [
            'status'  => 'success',
            'source'  => 'OpenRouteService',
            'start'   => $start,
            'end'     => $end,
            'routes'  => $results
        ];
    }

    /**
     * Format duration in seconds to a human-readable string.
     */
    private static function formatDuration(float $seconds): string
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
