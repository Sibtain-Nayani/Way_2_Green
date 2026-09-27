<?php
// services/Weather.php — OpenWeatherMap Live Weather & Forecast Service
// Provides real-time weather data and 5-day forecasts for the Digital Twin.
// API Docs: https://openweathermap.org/current & /forecast

require_once __DIR__ . '/EnvLoader.php';
require_once __DIR__ . '/HttpClient.php';

class Weather
{
    private const BASE_URL  = 'https://api.openweathermap.org/data/2.5/';
    private const GEO_URL   = 'https://api.openweathermap.org/geo/1.0/';

    /** Predefined coordinates for Way2Green's destination portfolio */
    public const DESTINATION_COORDS = [
        'munnar'    => ['lat' => 10.0889, 'lon' => 77.0595, 'label' => 'Munnar, Kerala'],
        'manali'    => ['lat' => 32.2396, 'lon' => 77.1887, 'label' => 'Manali, Himachal Pradesh'],
        'wayanad'   => ['lat' => 11.6854, 'lon' => 76.1320, 'label' => 'Wayanad, Kerala'],
        'goa'       => ['lat' => 15.1500, 'lon' => 73.9500, 'label' => 'South Goa'],
        'rishikesh' => ['lat' => 30.0869, 'lon' => 78.2676, 'label' => 'Rishikesh, Uttarakhand'],
        'ooty'      => ['lat' => 11.4102, 'lon' => 76.6950, 'label' => 'Ooty, Tamil Nadu'],
        'mumbai'    => ['lat' => 19.0760, 'lon' => 72.8777, 'label' => 'Mumbai'],
        'panvel'    => ['lat' => 18.9878, 'lon' => 73.1111, 'label' => 'Panvel'],
        'delhi'     => ['lat' => 28.6139, 'lon' => 77.2090, 'label' => 'New Delhi'],
        'bengaluru' => ['lat' => 12.9716, 'lon' => 77.5946, 'label' => 'Bengaluru'],
    ];

    /**
     * Get current live weather at a lat/lon coordinate.
     *
     * @param float  $lat  Latitude
     * @param float  $lon  Longitude
     * @param string $name Optional place name for labeling
     * @return array Weather data with eco-impact indicators
     */
    public static function getCurrentWeather(float $lat, float $lon, string $name = ''): array
    {
        $key = EnvLoader::get('OPENWEATHER_API_KEY');
        if (empty($key)) {
            // Graceful fallback for Hackathon/Demo purposes if key is missing
            return [
                'status' => 'success',
                'dest' => $name,
                'timestamp' => time(),
                'temperature' => ['current' => 24.5, 'feels_like' => 25.2, 'min' => 20, 'max' => 28],
                'humidity' => 68,
                'wind_speed' => 4.2,
                'visibility_km' => 10,
                'rain_1h_mm' => 0.0,
                'weather' => [
                    'main' => 'Clear',
                    'description' => 'clear sky',
                    'icon_url' => 'http://openweathermap.org/img/wn/01d@2x.png'
                ],
                'eco_impact' => [
                    'solar_efficiency_pct' => 85,
                    'road_accessibility' => 1.0,
                    'severity' => 'normal'
                ]
            ];
        }

        $url = self::BASE_URL . 'weather?' . http_build_query([
            'lat'   => $lat,
            'lon'   => $lon,
            'appid' => $key,
            'units' => 'metric'
        ]);

        $resp = HttpClient::get($url, [], 8);
        $data = HttpClient::decodeJson($resp);

        if (!$data || $resp['status'] !== 200) {
            return [
                'status'  => 'error',
                'message' => $data['message'] ?? 'Weather API failed',
                'http_status' => $resp['status']
            ];
        }

        return [
            'status'    => 'success',
            'source'    => 'OpenWeatherMap (Live)',
            'location'  => $name ?: ($data['name'] ?? 'Unknown'),
            'lat'       => $lat,
            'lon'       => $lon,
            'timestamp' => $data['dt'] ?? time(),
            'weather'   => [
                'condition'   => $data['weather'][0]['main'] ?? 'Unknown',
                'description' => $data['weather'][0]['description'] ?? '',
                'icon'        => $data['weather'][0]['icon'] ?? '',
                'icon_url'    => 'https://openweathermap.org/img/wn/' . ($data['weather'][0]['icon'] ?? '01d') . '@2x.png'
            ],
            'temperature' => [
                'current'    => round($data['main']['temp'] ?? 0, 1),
                'feels_like' => round($data['main']['feels_like'] ?? 0, 1),
                'min'        => round($data['main']['temp_min'] ?? 0, 1),
                'max'        => round($data['main']['temp_max'] ?? 0, 1),
            ],
            'humidity'      => $data['main']['humidity'] ?? 0,
            'wind_speed'    => $data['wind']['speed'] ?? 0,
            'wind_deg'      => $data['wind']['deg'] ?? 0,
            'visibility_km' => round(($data['visibility'] ?? 10000) / 1000, 1),
            'cloudiness'    => $data['clouds']['all'] ?? 0,
            'rain_1h_mm'    => $data['rain']['1h'] ?? 0,
            'snow_1h_mm'    => $data['snow']['1h'] ?? 0,
            // Digital Twin eco-impact indicators
            'eco_impact'    => self::computeEcoImpact($data)
        ];
    }

    /**
     * Get a 5-day / 3-hour weather forecast for a location.
     *
     * @param float $lat
     * @param float $lon
     * @param int   $days  Number of days (1-5)
     * @return array Forecast data
     */
    public static function getForecast(float $lat, float $lon, int $days = 5): array
    {
        $key = EnvLoader::get('OPENWEATHER_API_KEY');
        if (empty($key)) {
            // Mock forecast data
            $forecasts = [];
            $baseTime = time();
            for ($i=0; $i<8; $i++) {
                $forecasts[] = [
                    'time' => date('Y-m-d H:i:s', $baseTime + ($i * 10800)),
                    'temp' => 24 + rand(-2, 2),
                    'rain_3h_mm' => rand(0, 5),
                    'weather' => 'Clouds'
                ];
            }
            return ['status' => 'success', 'forecast' => $forecasts];
        }

        $cnt = min(40, max(1, $days * 8)); // 8 data points per day (every 3h)

        $url = self::BASE_URL . 'forecast?' . http_build_query([
            'lat'   => $lat,
            'lon'   => $lon,
            'cnt'   => $cnt,
            'appid' => $key,
            'units' => 'metric'
        ]);

        $resp = HttpClient::get($url, [], 8);
        $data = HttpClient::decodeJson($resp);

        if (!$data || $resp['status'] !== 200) {
            return ['status' => 'error', 'message' => $data['message'] ?? 'Forecast API failed'];
        }

        $items = [];
        foreach ($data['list'] ?? [] as $item) {
            $items[] = [
                'dt'          => $item['dt'],
                'time'        => date('Y-m-d H:i', $item['dt']),
                'condition'   => $item['weather'][0]['main'] ?? 'Unknown',
                'description' => $item['weather'][0]['description'] ?? '',
                'icon_url'    => 'https://openweathermap.org/img/wn/' . ($item['weather'][0]['icon'] ?? '01d') . '.png',
                'temp'        => round($item['main']['temp'] ?? 0, 1),
                'humidity'    => $item['main']['humidity'] ?? 0,
                'wind_speed'  => $item['wind']['speed'] ?? 0,
                'rain_3h_mm'  => $item['rain']['3h'] ?? 0,
                'pop'         => round(($item['pop'] ?? 0) * 100), // probability of precipitation %
                'eco_impact'  => self::computeEcoImpact($item)
            ];
        }

        return [
            'status'   => 'success',
            'source'   => 'OpenWeatherMap (5-day Forecast)',
            'location' => $data['city']['name'] ?? 'Unknown',
            'lat'      => $lat,
            'lon'      => $lon,
            'count'    => count($items),
            'forecast' => $items
        ];
    }

    /**
     * Get weather for all Way2Green destinations at once.
     *
     * @return array Map of destination key → weather data
     */
    public static function getAllDestinationsWeather(): array
    {
        $results = [];
        foreach (self::DESTINATION_COORDS as $key => $coords) {
            $results[$key] = self::getCurrentWeather($coords['lat'], $coords['lon'], $coords['label']);
        }
        return [
            'status'       => 'success',
            'generated_at' => date('c'),
            'destinations' => $results
        ];
    }

    /**
     * Compute eco-impact indicators from raw OpenWeatherMap data.
     * These feed directly into the Digital Twin simulation engine.
     *
     * @return array impact scores for travel demand, capacity, safety
     */
    private static function computeEcoImpact(array $data): array
    {
        $temp     = $data['main']['temp'] ?? 20;
        $humidity = $data['main']['humidity'] ?? 60;
        $wind     = $data['wind']['speed'] ?? 2;
        $rain     = ($data['rain']['1h'] ?? $data['rain']['3h'] ?? 0);
        $snow     = ($data['snow']['1h'] ?? $data['snow']['3h'] ?? 0);
        $cond     = strtolower($data['weather'][0]['main'] ?? 'clear');
        $clouds   = $data['clouds']['all'] ?? 0;

        // ── Travel demand multiplier (0.0 – 1.0, 1.0 = ideal conditions) ──
        $travelDemand = 1.0;
        if ($rain > 10)   $travelDemand -= 0.4;
        elseif ($rain > 2) $travelDemand -= 0.2;
        if ($snow > 5)    $travelDemand -= 0.5;
        if ($wind > 15)   $travelDemand -= 0.25;
        if ($temp > 42 || $temp < -5) $travelDemand -= 0.35;
        if (in_array($cond, ['thunderstorm', 'squall', 'tornado'])) $travelDemand -= 0.6;
        $travelDemand = max(0, round($travelDemand, 2));

        // ── Hotel occupancy impact ──
        $hotelOccupancy = 1.0;
        if (in_array($cond, ['thunderstorm', 'tornado'])) $hotelOccupancy += 0.25; // weather-forced stay
        if ($rain > 5)  $hotelOccupancy += 0.1;
        if ($temp > 40) $hotelOccupancy -= 0.2; // extreme heat lowers leisure travel
        $hotelOccupancy = max(0, min(1.5, round($hotelOccupancy, 2)));

        // ── Solar power generation estimate (0-100%) ──
        $solarEfficiency = max(0, round(100 - ($clouds * 0.85) - ($rain * 3), 0));

        // ── Road accessibility (0 = impassable, 1 = clear) ──
        $roadAccess = 1.0;
        if ($snow > 10)    $roadAccess = 0.2;
        elseif ($snow > 3) $roadAccess = 0.5;
        if ($rain > 30)    $roadAccess = min($roadAccess, 0.4);
        if (in_array($cond, ['tornado', 'squall'])) $roadAccess = 0;
        $roadAccess = max(0, round($roadAccess, 2));

        // ── Weather severity alert level ──
        $severity = 'normal';
        if (in_array($cond, ['tornado', 'squall']))      $severity = 'extreme';
        elseif (in_array($cond, ['thunderstorm']))       $severity = 'severe';
        elseif ($rain > 20 || $snow > 10 || $wind > 20) $severity = 'high';
        elseif ($rain > 5  || $snow > 3  || $wind > 10) $severity = 'moderate';

        // ── Carbon footprint adjustment (more bad weather = more car use) ──
        $carbonMultiplier = 1.0 + ($rain * 0.01) + ($snow * 0.02) + (max(0, $wind - 10) * 0.005);

        return [
            'travel_demand_factor'  => $travelDemand,
            'hotel_occupancy_factor' => $hotelOccupancy,
            'solar_efficiency_pct'  => $solarEfficiency,
            'road_accessibility'    => $roadAccess,
            'carbon_multiplier'     => round($carbonMultiplier, 3),
            'severity'              => $severity,
        ];
    }
}
?>
