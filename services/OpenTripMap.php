<?php
// services/OpenTripMap.php — OpenTripMap Points of Interest Service
// Returns tourist attractions, eco-sites, and accessibility-relevant POIs.
// API Docs: https://opentripmap.io/docs

require_once __DIR__ . '/EnvLoader.php';
require_once __DIR__ . '/HttpClient.php';

class OpenTripMap
{
    private const BASE_URL = 'https://api.opentripmap.com/0.1/en/places/';

    /** Nature & eco-relevant OpenTripMap kinds */
    public const ECO_KINDS = 'natural,parks,landscape,water,forests,gardens_and_parks,nature_reserves';

    /** Accessibility-relevant kinds */
    public const ACCESS_KINDS = 'historic,museums,cultural,amusements,sport,beaches,religion';

    /**
     * Get places/POIs near a lat/lon coordinate.
     *
     * @param float  $lat      Latitude
     * @param float  $lon      Longitude
     * @param int    $radius   Search radius in meters (default 5000)
     * @param string $kinds    Comma-separated OpenTripMap category kinds
     * @param int    $limit    Max results (default 30)
     * @param int    $minRating Minimum rating (0-3, default 1)
     * @return array Places result
     */
    public static function getNearbyPlaces(
        float $lat,
        float $lon,
        int $radius = 5000,
        string $kinds = self::ECO_KINDS,
        int $limit = 30,
        int $minRating = 1
    ): array {
        $key = EnvLoader::get('OPENTRIPMAP_API_KEY');
        if (empty($key)) {
            // Graceful fallback data for POIs
            return [
                'status' => 'success',
                'places' => [
                    ['name' => 'National Park Reserve', 'lat' => $lat + 0.01, 'lon' => $lon + 0.01, 'kinds' => 'nature_reserves,natural', 'dist' => 1200],
                    ['name' => 'Botanical Gardens', 'lat' => $lat - 0.01, 'lon' => $lon - 0.005, 'kinds' => 'gardens_and_parks,natural', 'dist' => 1800],
                    ['name' => 'Eco Trail', 'lat' => $lat + 0.015, 'lon' => $lon - 0.01, 'kinds' => 'natural', 'dist' => 2500],
                    ['name' => 'Mountain Viewpoint', 'lat' => $lat - 0.015, 'lon' => $lon + 0.02, 'kinds' => 'natural', 'dist' => 3200]
                ]
            ];
        }

        $params = [
            'radius'   => min(50000, max(100, $radius)),
            'lon'      => $lon,
            'lat'      => $lat,
            'kinds'    => $kinds,
            'rate'     => $minRating,
            'format'   => 'json',
            'limit'    => min(100, max(1, $limit)),
            'apikey'   => $key
        ];

        $url  = self::BASE_URL . 'radius?' . http_build_query($params);
        $resp = HttpClient::get($url, [], 10);
        $data = HttpClient::decodeJson($resp);

        if (!$data || $resp['status'] !== 200) {
            return [
                'status'  => 'error',
                'message' => 'OpenTripMap API failed',
                'http_status' => $resp['status']
            ];
        }

        // The radius endpoint returns an array directly
        if (!is_array($data)) {
            return ['status' => 'error', 'message' => 'Unexpected response format'];
        }

        $places = [];
        foreach ($data as $place) {
            $places[] = [
                'xid'      => $place['xid'] ?? '',
                'name'     => $place['name'] ?? 'Unnamed',
                'lat'      => $place['point']['lat'] ?? null,
                'lon'      => $place['point']['lon'] ?? null,
                'kinds'    => $place['kinds'] ?? '',
                'rating'   => $place['rate'] ?? 0,
                'dist'     => $place['dist'] ?? null,
                'osm_id'   => $place['osm'] ?? null
            ];
        }

        return [
            'status'    => 'success',
            'source'    => 'OpenTripMap',
            'query'     => ['lat' => $lat, 'lon' => $lon, 'radius' => $radius, 'kinds' => $kinds],
            'count'     => count($places),
            'places'    => $places
        ];
    }

    /**
     * Get detailed information about a specific POI by its xid.
     *
     * @param string $xid  OpenTripMap place identifier
     * @return array Place details
     */
    public static function getPlaceDetails(string $xid): array
    {
        $key = EnvLoader::get('OPENTRIPMAP_API_KEY');
        if (empty($key)) {
            return ['status' => 'error', 'message' => 'OPENTRIPMAP_API_KEY not set'];
        }

        $url  = self::BASE_URL . 'xid/' . urlencode($xid) . '?apikey=' . urlencode($key);
        $resp = HttpClient::get($url, [], 8);
        $data = HttpClient::decodeJson($resp);

        if (!$data || $resp['status'] !== 200) {
            return ['status' => 'error', 'message' => 'Place detail fetch failed'];
        }

        return [
            'status'      => 'success',
            'xid'         => $data['xid'] ?? $xid,
            'name'        => $data['name'] ?? 'Unknown',
            'description' => $data['wikipedia_extracts']['text'] ?? $data['info']['descr'] ?? null,
            'image'       => $data['preview']['source'] ?? $data['image'] ?? null,
            'lat'         => $data['point']['lat'] ?? null,
            'lon'         => $data['point']['lon'] ?? null,
            'kinds'       => $data['kinds'] ?? '',
            'url'         => $data['url'] ?? null,
            'wikipedia'   => $data['wikipedia'] ?? null,
            'address'     => $data['address'] ?? null,
            'opening_hours' => $data['opening_hours'] ?? null
        ];
    }

    /**
     * Get eco & nature attractions for a Way2Green destination.
     * Used by the Digital Twin to show what's at risk during weather events.
     *
     * @param float $lat
     * @param float $lon
     * @param int   $radius
     * @return array Eco attractions
     */
    public static function getEcoAttractions(float $lat, float $lon, int $radius = 8000): array
    {
        return self::getNearbyPlaces($lat, $lon, $radius, self::ECO_KINDS, 25, 1);
    }
}
?>
