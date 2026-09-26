<?php
// services/Accessibility.php — OpenStreetMap Overpass API Service
// Queries wheelchair-accessibility data from OSM's crowdsourced database.
// No API key required — uses the public Overpass endpoint.
// Attribution required: "Accessibility data © OpenStreetMap contributors"

require_once __DIR__ . '/HttpClient.php';

class Accessibility
{
    private const OVERPASS_URL = 'https://overpass-api.de/api/interpreter';

    /**
     * Get wheelchair-accessible places near a given coordinate.
     *
     * @param float $lat          Latitude
     * @param float $lon          Longitude
     * @param int   $radiusMeters Search radius in meters (default 1000)
     * @param int   $limit        Maximum results to return (default 50)
     * @return array Result with places array or error
     */
    public static function getAccessiblePlaces(
        float $lat,
        float $lon,
        int $radiusMeters = 1000,
        int $limit = 50
    ): array {
        // Validate coordinates
        if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
            return [
                'status'  => 'error',
                'message' => 'Invalid coordinates. Latitude must be -90 to 90, longitude -180 to 180.'
            ];
        }

        // Clamp radius to reasonable bounds (100m - 5000m)
        $radiusMeters = max(100, min(5000, $radiusMeters));

        // Overpass QL query: find nodes AND ways tagged with wheelchair status
        $query = <<<OVERPASS
[out:json][timeout:15];
(
  node["wheelchair"](around:{$radiusMeters},{$lat},{$lon});
  way["wheelchair"](around:{$radiusMeters},{$lat},{$lon});
);
out center {$limit};
OVERPASS;

        $response = HttpClient::postRaw(
            self::OVERPASS_URL,
            'data=' . urlencode($query),
            ['Content-Type: application/x-www-form-urlencoded'],
            15
        );

        $data = HttpClient::decodeJson($response);

        if (!$data || !isset($data['elements'])) {
            return [
                'status'  => 'error',
                'message' => 'Overpass API request failed or returned invalid data',
                'http_status' => $response['status'],
                'error' => $response['error']
            ];
        }

        // Parse and categorize results
        $places = [];
        $stats = ['yes' => 0, 'limited' => 0, 'no' => 0, 'unknown' => 0];

        foreach ($data['elements'] as $el) {
            $tags = $el['tags'] ?? [];
            $wheelchairStatus = strtolower($tags['wheelchair'] ?? 'unknown');

            // For ways, use center coordinates
            $placeLat = $el['lat'] ?? ($el['center']['lat'] ?? null);
            $placeLon = $el['lon'] ?? ($el['center']['lon'] ?? null);

            if ($placeLat === null || $placeLon === null) {
                continue;
            }

            // Determine place type from common OSM tags
            $placeType = $tags['amenity']
                ?? $tags['tourism']
                ?? $tags['shop']
                ?? $tags['leisure']
                ?? $tags['building']
                ?? 'place';

            $places[] = [
                'name'             => $tags['name'] ?? 'Unnamed place',
                'lat'              => $placeLat,
                'lon'              => $placeLon,
                'wheelchairStatus' => $wheelchairStatus,
                'type'             => $placeType,
                'address'          => self::buildAddress($tags),
                'phone'            => $tags['phone'] ?? null,
                'website'          => $tags['website'] ?? null,
                'opening_hours'    => $tags['opening_hours'] ?? null
            ];

            // Count by status
            if (isset($stats[$wheelchairStatus])) {
                $stats[$wheelchairStatus]++;
            } else {
                $stats['unknown']++;
            }
        }

        return [
            'status' => 'success',
            'source' => 'OpenStreetMap Overpass API',
            'attribution' => 'Accessibility data © OpenStreetMap contributors',
            'query' => [
                'lat'    => $lat,
                'lon'    => $lon,
                'radius' => $radiusMeters
            ],
            'count' => count($places),
            'stats' => $stats,
            'places' => $places
        ];
    }

    /**
     * Build a human-readable address string from OSM tags.
     */
    private static function buildAddress(array $tags): ?string
    {
        $parts = array_filter([
            $tags['addr:housenumber'] ?? null,
            $tags['addr:street'] ?? null,
            $tags['addr:city'] ?? null,
            $tags['addr:postcode'] ?? null
        ]);
        return !empty($parts) ? implode(', ', $parts) : null;
    }
}
?>
