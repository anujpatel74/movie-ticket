<?php
/**
 * CINEMA BOOKING - The Movie Database (TMDB) API Configuration & Client
 * 
 * Provides server-side proxying for TMDB v3 API and v4 Bearer Auth.
 * Keeps all API keys securely on the backend server.
 * 
 * Documentation: https://developer.themoviedb.org/
 */

if (!defined('TMDB_API_BASE_URL')) {
    define('TMDB_API_BASE_URL', 'https://api.themoviedb.org/3');
}

if (!defined('TMDB_IMAGE_BASE_URL')) {
    define('TMDB_IMAGE_BASE_URL', 'https://image.tmdb.org/t/p');
}

if (!defined('TMDB_DEFAULT_LANG')) {
    define('TMDB_DEFAULT_LANG', 'en-US');
}

// --------------------------------------------------------------------------
// API CREDENTIALS CONFIGURATION
// You can configure your TMDB credentials via:
// 1. Environment variable: TMDB_API_KEY or TMDB_READ_TOKEN
// 2. Direct constant: define('TMDB_API_KEY', 'your_key_here');
// 3. Fallback constant below:
// --------------------------------------------------------------------------
if (!defined('TMDB_API_KEY_FALLBACK')) {
    define('TMDB_API_KEY_FALLBACK', '');
}
if (!defined('TMDB_READ_TOKEN_FALLBACK')) {
    define('TMDB_READ_TOKEN_FALLBACK', '');
}

/**
 * Universal safe credential retriever
 */
function tmdb_get_credential(string $name): string {
    if (defined($name) && !empty(constant($name))) {
        return trim(constant($name));
    }
    if (!empty($_ENV[$name])) {
        return trim($_ENV[$name]);
    }
    if (!empty($_SERVER[$name])) {
        return trim($_SERVER[$name]);
    }
    $val = getenv($name);
    if (!empty($val)) {
        return trim($val);
    }
    $fallbackConst = $name . '_FALLBACK';
    if (defined($fallbackConst) && !empty(constant($fallbackConst))) {
        return trim(constant($fallbackConst));
    }
    return '';
}

/**
 * Get active TMDB API Key (v3)
 */
function tmdb_api_key(): string {
    return tmdb_get_credential('TMDB_API_KEY');
}

/**
 * Get active TMDB Read Access Token (v4)
 */
function tmdb_read_token(): string {
    return tmdb_get_credential('TMDB_READ_TOKEN');
}

/**
 * Check if TMDB API credentials are configured
 */
function tmdb_is_configured(): bool {
    return (!empty(tmdb_api_key()) || !empty(tmdb_read_token()));
}

/**
 * Make an authenticated server-side HTTP request to TMDB API via PHP cURL
 *
 * @param string $endpoint API path, e.g. '/search/movie' or '/movie/550'
 * @param array $params Query string parameters
 * @return array ['success' => bool, 'status_code' => int, 'data' => mixed, 'error' => ?string]
 */
function tmdb_api_request(string $endpoint, array $params = []): array {
    $apiKey = tmdb_api_key();
    $bearerToken = tmdb_read_token();

    if (empty($apiKey) && empty($bearerToken)) {
        return [
            'success'     => false,
            'status_code' => 401,
            'data'        => null,
            'error'       => 'TMDB API credentials not configured on server. Please set TMDB_API_KEY or TMDB_READ_TOKEN.'
        ];
    }

    // Prepare query parameters
    $query = $params;
    if (!isset($query['language'])) {
        $query['language'] = TMDB_DEFAULT_LANG;
    }

    // If using v3 key, append as query parameter unless bearer token is present
    if (empty($bearerToken) && !empty($apiKey)) {
        $query['api_key'] = $apiKey;
    }

    $url = rtrim(TMDB_API_BASE_URL, '/') . '/' . ltrim($endpoint, '/');
    if (!empty($query)) {
        $url .= '?' . http_build_query($query);
    }

    $ch = curl_init();
    $headers = [
        'Accept: application/json',
        'User-Agent: CinemaBooking/2.0 (PHP-Server-Proxy)'
    ];

    if (!empty($bearerToken)) {
        $headers[] = 'Authorization: Bearer ' . $bearerToken;
    }

    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT        => 6,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2
    ]);

    $response = curl_exec($ch);
    $curlErrno = curl_errno($ch);
    $curlError = curl_error($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curlErrno !== 0) {
        error_log("TMDB API cURL Error [{$curlErrno}]: " . $curlError);
        return [
            'success'     => false,
            'status_code' => $httpCode ?: 500,
            'data'        => null,
            'error'       => 'Movie service currently unavailable. Please try again later.'
        ];
    }

    $decoded = json_decode($response, true);

    if ($httpCode >= 200 && $httpCode < 300) {
        return [
            'success'     => true,
            'status_code' => $httpCode,
            'data'        => $decoded,
            'error'       => null
        ];
    }

    // Specific HTTP error translations
    $errorMessage = 'TMDB API returned HTTP ' . $httpCode;
    if (isset($decoded['status_message'])) {
        $errorMessage = $decoded['status_message'];
    } elseif ($httpCode === 401) {
        $errorMessage = 'Invalid TMDB API credentials. Please verify your API Key.';
    } elseif ($httpCode === 404) {
        $errorMessage = 'Resource not found on TMDB.';
    } elseif ($httpCode === 429) {
        $errorMessage = 'TMDB rate limit reached. Please wait a moment and retry.';
    }

    return [
        'success'     => false,
        'status_code' => $httpCode,
        'data'        => $decoded,
        'error'       => $errorMessage
    ];
}

/**
 * Generate full secure TMDB poster image URL with fallback to local placeholder
 */
function tmdb_poster_url(?string $posterPath, string $size = 'w342'): string {
    if (empty($posterPath)) {
        return asset_url('images/poster-placeholder.svg');
    }
    return rtrim(TMDB_IMAGE_BASE_URL, '/') . '/' . $size . '/' . ltrim($posterPath, '/');
}

/**
 * Generate full secure TMDB backdrop image URL with fallback to local default
 */
function tmdb_backdrop_url(?string $backdropPath, string $size = 'w1280'): string {
    if (empty($backdropPath)) {
        return asset_url('images/oppenheimer_bg.jpg');
    }
    return rtrim(TMDB_IMAGE_BASE_URL, '/') . '/' . $size . '/' . ltrim($backdropPath, '/');
}

/**
 * Search movies on TMDB
 *
 * @param string $query Movie search query
 * @param int $page Page number (1-based)
 * @param string $lang Language code
 * @return array
 */
function tmdb_search_movies(string $query, int $page = 1, string $lang = 'en-US'): array {
    $query = trim($query);
    if (empty($query)) {
        return ['success' => true, 'data' => ['results' => [], 'total_results' => 0, 'page' => 1]];
    }

    return tmdb_api_request('/search/movie', [
        'query'         => $query,
        'page'          => max(1, $page),
        'language'      => $lang,
        'include_adult' => 'false'
    ]);
}

/**
 * Fetch detailed movie information from TMDB
 *
 * @param int $tmdbId TMDB Movie ID
 * @param string $lang Language code
 * @return array
 */
function tmdb_get_movie_details(int $tmdbId, string $lang = 'en-US'): array {
    if ($tmdbId <= 0) {
        return ['success' => false, 'status_code' => 400, 'error' => 'Invalid TMDB Movie ID'];
    }

    return tmdb_api_request("/movie/{$tmdbId}", [
        'language'           => $lang,
        'append_to_response' => 'videos,credits,similar'
    ]);
}

