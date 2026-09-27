<?php
// services/HttpClient.php — Shared cURL HTTP Client
// Provides GET/POST methods with JSON support, timeouts, and error handling.
// Used by all Way2Green service modules to avoid duplicated HTTP boilerplate.

class HttpClient
{
    /** @var int Default timeout in seconds */
    private static $defaultTimeout = 10;

    /**
     * Perform an HTTP GET request.
     *
     * @param string $url     Full URL to request
     * @param array  $headers Optional associative headers ['Header-Name: value']
     * @param int    $timeout Request timeout in seconds
     * @return array ['success' => bool, 'status' => int, 'body' => string, 'error' => string|null]
     */
    public static function get(string $url, array $headers = [], int $timeout = 0): array
    {
        return self::request('GET', $url, null, $headers, $timeout);
    }

    /**
     * Perform an HTTP POST request with a JSON body.
     *
     * @param string      $url     Full URL to request
     * @param array|null  $data    Data to JSON-encode as the request body
     * @param array       $headers Optional additional headers
     * @param int         $timeout Request timeout in seconds
     * @return array ['success' => bool, 'status' => int, 'body' => string, 'error' => string|null]
     */
    public static function postJson(string $url, ?array $data = null, array $headers = [], int $timeout = 0): array
    {
        $headers[] = 'Content-Type: application/json';
        $body = $data !== null ? json_encode($data) : null;
        return self::request('POST', $url, $body, $headers, $timeout);
    }

    /**
     * Perform an HTTP POST request with a raw string body (e.g. Overpass QL).
     *
     * @param string      $url     Full URL to request
     * @param string      $body    Raw body string
     * @param array       $headers Optional additional headers
     * @param int         $timeout Request timeout in seconds
     * @return array ['success' => bool, 'status' => int, 'body' => string, 'error' => string|null]
     */
    public static function postRaw(string $url, string $body, array $headers = [], int $timeout = 0): array
    {
        return self::request('POST', $url, $body, $headers, $timeout);
    }

    /**
     * Decode a JSON response body.
     *
     * @param array $response Response array from get()/postJson()/postRaw()
     * @return array|null Decoded JSON or null on failure
     */
    public static function decodeJson(array $response): ?array
    {
        if (!$response['success'] || empty($response['body'])) {
            return null;
        }
        $decoded = json_decode($response['body'], true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Internal: execute a cURL request.
     */
    private static function request(string $method, string $url, ?string $body, array $headers, int $timeout): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout > 0 ? $timeout : self::$defaultTimeout);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);

        // SSL verification (disable only for local dev if needed — keep enabled in prod)
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($body !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            }
        }

        $responseBody = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        $success = ($responseBody !== false && $httpCode >= 200 && $httpCode < 300);

        return [
            'success' => $success,
            'status'  => $httpCode,
            'body'    => $responseBody ?: '',
            'error'   => !empty($error) ? $error : null
        ];
    }
}
?>
