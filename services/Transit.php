<?php
// services/Transit.php — Transitland Public Transit API Service
// Queries real public transit schedules, stops, and routes.
// Free and open data — API key optional but increases rate limit.
// API Docs: https://www.transit.land/documentation

require_once __DIR__ . '/EnvLoader.php';
require_once __DIR__ . '/HttpClient.php';

class Transit
{
    private const API_BASE = 'https://transit.land/api/v2/rest/';

    /**
     * Find public transit stops near a given coordinate.
     *
     * @param float $lat          Latitude
     * @param float $lon          Longitude
     * @param int   $radiusMeters Search radius in meters (default 500, max 5000)
     * @param int   $limit        Maximum stops to return (default 20)
     * @return array Transit stops with route info, or error
     */
    public static function getNearbyStops(
        float $lat,
        float $lon,
        int $radiusMeters = 500,
        int $limit = 20
    ): array {
        // Validate coordinates
        if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
            return [
                'status'  => 'error',
                'message' => 'Invalid coordinates.'
            ];
        }

        $radiusMeters = max(100, min(5000, $radiusMeters));
        $limit = max(1, min(50, $limit));

        $params = [
            'lat'    => $lat,
            'lon'    => $lon,
            'radius' => $radiusMeters,
            'limit'  => $limit
        ];

        $url = self::API_BASE . 'stops?' . http_build_query($params);
        $headers = self::buildHeaders();

        $response = HttpClient::get($url, $headers, 10);
        $data = HttpClient::decodeJson($response);

        if (!$data || !isset($data['stops'])) {
            return [
                'status'      => 'error',
                'message'     => 'Transitland API request failed or no data returned',
                'http_status' => $response['status'],
                'error'       => $response['error']
            ];
        }

        $stops = [];
        foreach ($data['stops'] as $stop) {
            $geometry = $stop['geometry'] ?? [];
            $coords   = $geometry['coordinates'] ?? [null, null]; // [lon, lat]

            // Extract route references served by this stop
            $routeStops = $stop['route_stops'] ?? [];
            $routes = [];
            foreach ($routeStops as $rs) {
                $route = $rs['route'] ?? [];
                $routes[] = [
                    'name'       => $route['route_long_name'] ?? $route['route_short_name'] ?? 'Unknown',
                    'short_name' => $route['route_short_name'] ?? null,
                    'type'       => self::routeTypeName($route['route_type'] ?? -1),
                    'agency'     => $route['agency']['agency_name'] ?? null
                ];
            }

            $stops[] = [
                'name'         => $stop['stop_name'] ?? 'Unnamed Stop',
                'stop_id'      => $stop['stop_id'] ?? null,
                'lat'          => $coords[1],
                'lon'          => $coords[0],
                'location_type' => $stop['location_type'] ?? 0,
                'wheelchair_boarding' => $stop['wheelchair_boarding'] ?? null,
                'routes'       => $routes,
                'route_count'  => count($routes)
            ];
        }

        return [
            'status' => 'success',
            'source' => 'Transitland',
            'query'  => [
                'lat'    => $lat,
                'lon'    => $lon,
                'radius' => $radiusMeters
            ],
            'count' => count($stops),
            'stops' => $stops
        ];
    }

    /**
     * Search for transit routes by name or operator.
     *
     * @param string $query Search query (route name, operator, etc.)
     * @param int    $limit Maximum results
     * @return array Route search results
     */
    public static function searchRoutes(string $query, int $limit = 10): array
    {
        $params = [
            'operator_name' => $query,
            'limit'         => max(1, min(50, $limit))
        ];

        $url = self::API_BASE . 'routes?' . http_build_query($params);
        $headers = self::buildHeaders();

        $response = HttpClient::get($url, $headers, 10);
        $data = HttpClient::decodeJson($response);

        if (!$data || !isset($data['routes'])) {
            return [
                'status'      => 'error',
                'message'     => 'Transitland route search failed',
                'http_status' => $response['status'],
                'error'       => $response['error']
            ];
        }

        $routes = [];
        foreach ($data['routes'] as $route) {
            $routes[] = [
                'name'       => $route['route_long_name'] ?? $route['route_short_name'] ?? 'Unknown',
                'short_name' => $route['route_short_name'] ?? null,
                'type'       => self::routeTypeName($route['route_type'] ?? -1),
                'agency'     => $route['agency']['agency_name'] ?? null,
                'color'      => $route['route_color'] ?? null,
                'text_color' => $route['route_text_color'] ?? null
            ];
        }

        return [
            'status' => 'success',
            'source' => 'Transitland',
            'query'  => $query,
            'count'  => count($routes),
            'routes' => $routes
        ];
    }

    /**
     * Build HTTP headers, including API key if configured.
     */
    private static function buildHeaders(): array
    {
        $headers = [];
        $apiKey = EnvLoader::get('TRANSITLAND_API_KEY');
        if (!empty($apiKey)) {
            $headers[] = 'apikey: ' . $apiKey;
        }
        return $headers;
    }

    /**
     * Convert GTFS route_type integer to a human-readable name.
     * https://gtfs.org/schedule/reference/#routestxt
     */
    private static function routeTypeName(int $type): string
    {
        $types = [
            0  => 'Tram / Light Rail',
            1  => 'Subway / Metro',
            2  => 'Rail / Train',
            3  => 'Bus',
            4  => 'Ferry',
            5  => 'Cable Tram',
            6  => 'Aerial Lift / Gondola',
            7  => 'Funicular',
            11 => 'Trolleybus',
            12 => 'Monorail'
        ];
        return $types[$type] ?? 'Transit';
    }
}
?>
