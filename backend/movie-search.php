<?php
/**
 * CINEMA BOOKING - Smart Movie Search Backend API
 * 
 * Hybrid Search Engine:
 * 1. Queries local cinema database (in theatres / coming soon with booking links)
 * 2. Queries TMDB API /3/search/movie for global discovery
 * 3. Deduplicates and merges results, preferring local assets and posters
 * 4. Gracefully degrades to local database search if TMDB is offline or unconfigured
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/tmdb.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: private, max-age=60');

$query = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$lang = trim($_GET['lang'] ?? TMDB_DEFAULT_LANG);

if (mb_strlen($query) < 2) {
    echo json_encode([
        'success'         => true,
        'query'           => $query,
        'total_results'   => 0,
        'local_count'     => 0,
        'tmdb_count'      => 0,
        'tmdb_configured' => tmdb_is_configured(),
        'results'         => []
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

$pdo = getDBConnection();
$results = [];
$matchedTmdbIds = [];
$matchedTitles = [];

// --------------------------------------------------------------------------
// 1. Search Local Cinema Database
// --------------------------------------------------------------------------
try {
    $searchWildcard = '%' . $query . '%';
    $stmt = $pdo->prepare("
        SELECT m.*,
               (SELECT COUNT(*) FROM shows s WHERE s.movie_id = m.id AND s.show_date >= CURDATE() AND s.status = 'active') AS active_shows_count
        FROM movies m
        WHERE m.title LIKE ? OR m.genre LIKE ?
        ORDER BY 
            CASE WHEN m.title LIKE ? THEN 0 ELSE 1 END,
            m.status ASC,
            m.rating DESC
        LIMIT 8
    ");
    $stmt->execute([$searchWildcard, $searchWildcard, $query . '%']);
    $localMovies = $stmt->fetchAll();

    foreach ($localMovies as $m) {
        $year = !empty($m['release_date']) ? date('Y', strtotime($m['release_date'])) : '';
        $hasShows = (int)$m['active_shows_count'] > 0;
        
        $statusLabel = 'In Theatres';
        if ($m['status'] === 'coming_soon') {
            $statusLabel = 'Coming Soon';
        } elseif (!$hasShows) {
            $statusLabel = 'Now Showing';
        }

        $rawOverview = $m['description'] ?? '';
        $shortOverview = !empty($rawOverview) ? mb_substr(strip_tags($rawOverview), 0, 140) . (mb_strlen($rawOverview) > 140 ? '...' : '') : '';

        $entry = [
            'id'             => (int)$m['id'],
            'tmdb_id'        => !empty($m['tmdb_id']) ? (int)$m['tmdb_id'] : null,
            'title'          => $m['title'],
            'release_year'   => $year,
            'genre'          => $m['genre'],
            'language'       => $m['language'],
            'rating'         => (float)$m['rating'],
            'certificate'    => $m['certificate'] ?: 'UA',
            'overview'       => $shortOverview,
            'poster_url'     => movie_poster_url($m['poster_url']),
            'is_local'       => true,
            'status'         => $m['status'],
            'status_label'   => $statusLabel,
            'has_shows'      => $hasShows,
            'details_url'    => base_url('movie-details.php?id=' . $m['id']),
            'booking_url'    => $hasShows ? base_url('movie-details.php?id=' . $m['id']) : null
        ];

        $results[] = $entry;

        if (!empty($m['tmdb_id'])) {
            $matchedTmdbIds[] = (int)$m['tmdb_id'];
        }
        $matchedTitles[] = mb_strtolower(trim($m['title']));
    }
} catch (Exception $e) {
    // Graceful error logging; continue to TMDB or return empty
    error_log("Local movie search error: " . $e->getMessage());
}

$localCount = count($results);
$tmdbCount = 0;
$tmdbError = null;
$tmdbConfigured = tmdb_is_configured();

// --------------------------------------------------------------------------
// 2. Query TMDB API (if configured)
// --------------------------------------------------------------------------
if ($tmdbConfigured) {
    $tmdbRes = tmdb_search_movies($query, $page, $lang);

    if ($tmdbRes['success'] && !empty($tmdbRes['data']['results'])) {
        foreach ($tmdbRes['data']['results'] as $item) {
            $itemId = (int)$item['id'];
            $itemTitle = trim($item['title'] ?? '');
            $normalizedTitle = mb_strtolower($itemTitle);

            // Deduplicate: Skip if already present in local database results
            if (in_array($itemId, $matchedTmdbIds, true) || in_array($normalizedTitle, $matchedTitles, true)) {
                continue;
            }

            $year = !empty($item['release_date']) ? substr($item['release_date'], 0, 4) : '';
            $rating = !empty($item['vote_average']) ? round((float)$item['vote_average'], 1) : null;
            $poster = tmdb_poster_url($item['poster_path'] ?? null, 'w185');

            $rawTmdbOverview = $item['overview'] ?? '';
            $shortTmdbOverview = !empty($rawTmdbOverview) ? mb_substr(strip_tags($rawTmdbOverview), 0, 140) . (mb_strlen($rawTmdbOverview) > 140 ? '...' : '') : '';

            $results[] = [
                'id'             => 'tmdb_' . $itemId,
                'tmdb_id'        => $itemId,
                'title'          => $itemTitle,
                'release_year'   => $year,
                'genre'          => 'Global Release',
                'language'       => strtoupper($item['original_language'] ?? 'EN'),
                'rating'         => $rating,
                'certificate'    => null,
                'overview'       => $shortTmdbOverview,
                'poster_url'     => $poster,
                'is_local'       => false,
                'status'         => 'global',
                'status_label'   => 'TMDB Global',
                'has_shows'      => false,
                'details_url'    => base_url('movie-details.php?tmdb_id=' . $itemId),
                'booking_url'    => null
            ];

            $tmdbCount++;

            // Cap total dropdown results to 15 for optimal performance & UX
            if (count($results) >= 15) {
                break;
            }
        }
    } else {
        $tmdbError = $tmdbRes['error'] ?? null;
    }
}

echo json_encode([
    'success'         => true,
    'query'           => $query,
    'total_results'   => count($results),
    'local_count'     => $localCount,
    'tmdb_count'      => $tmdbCount,
    'tmdb_configured' => $tmdbConfigured,
    'tmdb_error'      => $tmdbError,
    'results'         => $results
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

