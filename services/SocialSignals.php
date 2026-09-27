<?php
// services/SocialSignals.php — Social Media Signal Integration
// Captures real-world traveler reactions to weather events via Reddit,
// OpenStreetMap Notes (crowd-sourced field reports), and RSS feeds.
// No paid API required — uses public endpoints with attribution.

require_once __DIR__ . '/HttpClient.php';

class SocialSignals
{
    /**
     * Fetch Reddit posts about a destination + weather condition.
     * Uses Reddit's public JSON API (no auth for read-only).
     *
     * @param string $destination  Place name (e.g. "Munnar Kerala")
     * @param string $condition    Weather condition (e.g. "flood" "rain" "cyclone")
     * @param int    $limit        Max posts to return (default 10)
     * @return array Posts with sentiment and relevance score
     */
    public static function getRedditSignals(
        string $destination,
        string $condition = '',
        int $limit = 10
    ): array {
        $query = urlencode(trim($destination . ' ' . $condition . ' travel'));
        $url   = "https://www.reddit.com/search.json?q={$query}&type=link&t=week&limit=" . min(25, $limit);

        $resp = HttpClient::get($url, [
            'User-Agent: Way2Green-DigitalTwin/1.0 (Hackathon; Research)'
        ], 8);

        $data = HttpClient::decodeJson($resp);

        if (!$data || !isset($data['data']['children'])) {
            return [
                'status'  => 'error',
                'message' => 'Reddit API unavailable or no results',
                'source'  => 'Reddit Public Search API'
            ];
        }

        $posts = [];
        foreach ($data['data']['children'] as $child) {
            $post = $child['data'];
            $title = $post['title'] ?? '';
            $score = $post['score'] ?? 0;

            // Basic keyword-based sentiment scoring
            $sentiment = self::scoreSentiment($title . ' ' . ($post['selftext'] ?? ''));

            $posts[] = [
                'title'       => $title,
                'url'         => 'https://reddit.com' . ($post['permalink'] ?? ''),
                'subreddit'   => $post['subreddit'] ?? '',
                'score'       => $score,
                'comments'    => $post['num_comments'] ?? 0,
                'created_utc' => $post['created_utc'] ?? null,
                'time_ago'    => self::timeAgo($post['created_utc'] ?? 0),
                'sentiment'   => $sentiment['label'],
                'sentiment_score' => $sentiment['score'],
                'flair'       => $post['link_flair_text'] ?? null
            ];
        }

        // Sort by engagement (score + comments)
        usort($posts, fn($a, $b) => ($b['score'] + $b['comments']) <=> ($a['score'] + $a['comments']));
        $posts = array_slice($posts, 0, $limit);

        return [
            'status'      => 'success',
            'source'      => 'Reddit Public Search API',
            'attribution' => 'Posts © their respective Reddit authors (CC BY 4.0 for non-commercial use)',
            'query'       => $destination . ' ' . $condition,
            'count'       => count($posts),
            'sentiment_summary' => self::summarizeSentiment($posts),
            'posts'       => $posts
        ];
    }

    /**
     * Fetch OpenStreetMap Notes near a coordinate.
     * OSM Notes are crowdsourced field reports — very useful for road closures,
     * flooding, and accessibility issues reported by travelers on-ground.
     *
     * @param float $lat
     * @param float $lon
     * @param int   $radius  Search radius in meters
     * @param int   $limit   Max notes
     * @return array Field reports
     */
    public static function getOsmNotes(float $lat, float $lon, int $radius = 5000, int $limit = 20): array
    {
        $bbox = self::latLonToBbox($lat, $lon, $radius);
        $url  = "https://api.openstreetmap.org/api/0.6/notes.json?bbox={$bbox}&limit={$limit}&closed=0";

        $resp = HttpClient::get($url, [], 10);
        $data = HttpClient::decodeJson($resp);

        if (!$data || !isset($data['features'])) {
            return [
                'status'  => 'error',
                'message' => 'OSM Notes API unavailable',
                'source'  => 'OpenStreetMap Notes'
            ];
        }

        $notes = [];
        foreach ($data['features'] as $feature) {
            $props   = $feature['properties'] ?? [];
            $coords  = $feature['geometry']['coordinates'] ?? [null, null];
            $comment = $props['comments'][0]['text'] ?? '';
            $sentiment = self::scoreSentiment($comment);

            $notes[] = [
                'id'         => $props['id'] ?? null,
                'lat'        => $coords[1],
                'lon'        => $coords[0],
                'text'       => $comment,
                'date_created' => $props['date_created'] ?? null,
                'status'     => $props['status'] ?? 'open',
                'comments'   => count($props['comments'] ?? []),
                'sentiment'  => $sentiment['label'],
                'weather_related' => self::isWeatherRelated($comment)
            ];
        }

        return [
            'status'      => 'success',
            'source'      => 'OpenStreetMap Notes (Crowdsourced Field Reports)',
            'attribution' => 'Field reports © OpenStreetMap contributors (ODbL)',
            'count'       => count($notes),
            'weather_related_count' => count(array_filter($notes, fn($n) => $n['weather_related'])),
            'notes'       => $notes
        ];
    }

    /**
     * Aggregate all social signals for a destination + weather event.
     *
     * @param string $destination
     * @param float  $lat
     * @param float  $lon
     * @param string $condition   Weather keyword
     * @return array Combined signal report
     */
    public static function getAggregatedSignals(
        string $destination,
        float $lat,
        float $lon,
        string $condition = ''
    ): array {
        $reddit = self::getRedditSignals($destination, $condition, 8);
        $osmNotes = self::getOsmNotes($lat, $lon, 5000, 15);

        // Compute overall social alarm level
        $alarmLevel = 'green';
        $negativeCount = 0;

        if ($reddit['status'] === 'success') {
            foreach ($reddit['posts'] as $p) {
                if ($p['sentiment'] === 'negative') $negativeCount++;
            }
        }
        if ($osmNotes['status'] === 'success') {
            $negativeCount += $osmNotes['weather_related_count'] ?? 0;
        }

        if ($negativeCount >= 8)     $alarmLevel = 'red';
        elseif ($negativeCount >= 4) $alarmLevel = 'orange';
        elseif ($negativeCount >= 2) $alarmLevel = 'yellow';

        return [
            'status'        => 'success',
            'destination'   => $destination,
            'condition'     => $condition,
            'alarm_level'   => $alarmLevel,
            'reddit'        => $reddit,
            'osm_notes'     => $osmNotes,
            'summary'       => [
                'total_signals'       => ($reddit['count'] ?? 0) + ($osmNotes['count'] ?? 0),
                'negative_signals'    => $negativeCount,
                'alarm_level'         => $alarmLevel,
                'top_reddit_post'     => $reddit['posts'][0]['title'] ?? null
            ]
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    // Private Helpers
    // ──────────────────────────────────────────────────────────────────────

    private static function scoreSentiment(string $text): array
    {
        $text = strtolower($text);
        $negWords = ['flood', 'dangerous', 'closed', 'avoid', 'cancelled', 'warning', 'disaster',
                     'stranded', 'impassable', 'emergency', 'evacuate', 'storm', 'terrible', 'awful',
                     'unsafe', 'blocked', 'landslide', 'cyclone', 'damage', 'destroyed'];
        $posWords = ['beautiful', 'amazing', 'great', 'perfect', 'lovely', 'safe', 'recommend',
                     'accessible', 'clear', 'wonderful', 'peaceful', 'eco', 'green', 'sustainable'];

        $negScore = 0;
        $posScore = 0;
        foreach ($negWords as $w) { if (str_contains($text, $w)) $negScore++; }
        foreach ($posWords as $w) { if (str_contains($text, $w)) $posScore++; }

        if ($negScore > $posScore) return ['label' => 'negative', 'score' => -$negScore];
        if ($posScore > $negScore) return ['label' => 'positive', 'score' => $posScore];
        return ['label' => 'neutral', 'score' => 0];
    }

    private static function summarizeSentiment(array $posts): array
    {
        $counts = ['positive' => 0, 'neutral' => 0, 'negative' => 0];
        foreach ($posts as $p) { $counts[$p['sentiment']] = ($counts[$p['sentiment']] ?? 0) + 1; }
        $total = array_sum($counts);
        return [
            'counts'   => $counts,
            'dominant' => $total > 0 ? array_key_first(array_slice(arsort($counts) ? $counts : $counts, 0, 1, true)) : 'neutral'
        ];
    }

    private static function isWeatherRelated(string $text): bool
    {
        $keywords = ['rain', 'flood', 'storm', 'wet', 'puddle', 'muddy', 'road closed', 'washed',
                     'drainage', 'hail', 'snow', 'wind', 'cyclone', 'waterlog', 'damage'];
        $text = strtolower($text);
        foreach ($keywords as $k) { if (str_contains($text, $k)) return true; }
        return false;
    }

    private static function latLonToBbox(float $lat, float $lon, int $radiusMeters): string
    {
        $deg = $radiusMeters / 111000;
        $minLon = round($lon - $deg, 6);
        $minLat = round($lat - $deg, 6);
        $maxLon = round($lon + $deg, 6);
        $maxLat = round($lat + $deg, 6);
        return "{$minLon},{$minLat},{$maxLon},{$maxLat}";
    }

    private static function timeAgo(int $timestamp): string
    {
        $diff = time() - $timestamp;
        if ($diff < 3600)  return round($diff / 60) . ' min ago';
        if ($diff < 86400) return round($diff / 3600) . 'h ago';
        return round($diff / 86400) . 'd ago';
    }
}
?>
