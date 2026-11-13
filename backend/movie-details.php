<?php
/**
 * CINEMA BOOKING - Movie Details Provider (Local DB & TMDB Enrichment)
 * 
 * Fetches comprehensive movie details:
 * - Title, Overview, Release Date, Genres, Runtime, Language, Rating
 * - High-res Poster and Backdrop URLs
 * - Official YouTube Trailer link & Top Cast
 * - Local showtime cross-referencing
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/tmdb.php';
require_once __DIR__ . '/movie-process.php';

/**
 * Fetch and normalize detailed movie information by TMDB ID
 *
 * @param int $tmdbId
 * @return array|null Normalized movie data array or null on failure
 */
function get_movie_details_by_tmdb_id(int $tmdbId): ?array {
    $pdo = getDBConnection();

    // 1. Check if movie already exists in local database
    $stmt = $pdo->prepare("SELECT * FROM movies WHERE tmdb_id = ? LIMIT 1");
    $stmt->execute([$tmdbId]);
    $localMovie = $stmt->fetch();

    $activeShows = [];
    if ($localMovie) {
        $activeShows = get_movie_show_dates((int)$localMovie['id']);
    }

    // 2. If TMDB is configured, fetch rich global metadata
    if (tmdb_is_configured()) {
        $tmdbRes = tmdb_get_movie_details($tmdbId);
        if ($tmdbRes['success'] && !empty($tmdbRes['data'])) {
            $data = $tmdbRes['data'];

            // Extract genres
            $genres = [];
            if (!empty($data['genres'])) {
                $genres = array_column($data['genres'], 'name');
            }
            $genreString = implode(', ', $genres);

            // Extract YouTube trailer key
            $trailerUrl = null;
            if (!empty($data['videos']['results'])) {
                foreach ($data['videos']['results'] as $v) {
                    if (($v['site'] ?? '') === 'YouTube' && ($v['type'] ?? '') === 'Trailer') {
                        $trailerUrl = 'https://www.youtube.com/watch?v=' . $v['key'];
                        break;
                    }
                }
            }

            // Extract Top Cast
            $cast = [];
            if (!empty($data['credits']['cast'])) {
                $topCast = array_slice($data['credits']['cast'], 0, 5);
                foreach ($topCast as $c) {
                    $cast[] = [
                        'name'      => $c['name'] ?? '',
                        'character' => $c['character'] ?? '',
                        'profile'   => !empty($c['profile_path']) ? tmdb_poster_url($c['profile_path'], 'w185') : null
                    ];
                }
            }

            // Extract Director
            $director = null;
            if (!empty($data['credits']['crew'])) {
                foreach ($data['credits']['crew'] as $crew) {
                    if (($crew['job'] ?? '') === 'Director') {
                        $director = $crew['name'] ?? null;
                        break;
                    }
                }
            }

            // Prefer local posters and backdrop if local movie exists
            $poster = $localMovie ? movie_poster_url($localMovie['poster_url']) : tmdb_poster_url($data['poster_path'], 'w500');
            $backdrop = $localMovie ? movie_backdrop_url($localMovie['backdrop_url'], $localMovie['poster_url']) : tmdb_backdrop_url($data['backdrop_path'], 'w1280');

            return [
                'id'                  => $localMovie['id'] ?? ('tmdb_' . $tmdbId),
                'tmdb_id'             => $tmdbId,
                'title'               => $data['title'] ?? ($localMovie['title'] ?? 'Unknown Title'),
                'original_title'      => $data['original_title'] ?? null,
                'description'         => $data['overview'] ?? ($localMovie['description'] ?? 'No overview available.'),
                'duration_mins'       => (int)($data['runtime'] ?? ($localMovie['duration_mins'] ?? 0)),
                'release_date'        => $data['release_date'] ?? ($localMovie['release_date'] ?? ''),
                'release_year'        => !empty($data['release_date']) ? substr($data['release_date'], 0, 4) : '',
                'genre'               => $genreString ?: ($localMovie['genre'] ?? 'Cinema'),
                'language'            => !empty($data['spoken_languages'][0]['english_name']) ? $data['spoken_languages'][0]['english_name'] : strtoupper($data['original_language'] ?? 'English'),
                'rating'              => round((float)($data['vote_average'] ?? ($localMovie['rating'] ?? 0)), 1),
                'vote_count'          => (int)($data['vote_count'] ?? 0),
                'certificate'         => $localMovie['certificate'] ?? 'UA',
                'poster_url'          => $poster,
                'backdrop_url'        => $backdrop,
                'trailer_url'         => $trailerUrl ?: ($localMovie['trailer_url'] ?? null),
                'cast'                => $cast,
                'director'            => $director,
                'is_local'            => !empty($localMovie),
                'has_shows'           => !empty($activeShows),
                'status'              => $localMovie['status'] ?? 'global',
                'booking_url'         => $localMovie ? base_url('movie-details.php?id=' . $localMovie['id']) : null
            ];
        }
    }

    // 3. Fallback to local movie details if TMDB API is offline or unconfigured
    if ($localMovie) {
        return [
            'id'            => (int)$localMovie['id'],
            'tmdb_id'       => $tmdbId,
            'title'         => $localMovie['title'],
            'description'   => $localMovie['description'],
            'duration_mins' => (int)$localMovie['duration_mins'],
            'release_date'  => $localMovie['release_date'],
            'release_year'  => !empty($localMovie['release_date']) ? date('Y', strtotime($localMovie['release_date'])) : '',
            'genre'         => $localMovie['genre'],
            'language'      => $localMovie['language'],
            'rating'        => (float)$localMovie['rating'],
            'certificate'   => $localMovie['certificate'],
            'poster_url'    => movie_poster_url($localMovie['poster_url']),
            'backdrop_url'  => movie_backdrop_url($localMovie['backdrop_url'], $localMovie['poster_url']),
            'trailer_url'   => $localMovie['trailer_url'],
            'cast'          => [],
            'director'      => null,
            'is_local'      => true,
            'has_shows'     => !empty($activeShows),
            'status'        => $localMovie['status'],
            'booking_url'   => base_url('movie-details.php?id=' . $localMovie['id'])
        ];
    }

    return null;
}

// If accessed directly as a JSON endpoint via HTTP GET
if (isset($_SERVER['SCRIPT_FILENAME']) && realpath($_SERVER['SCRIPT_FILENAME']) === realpath(__FILE__)) {
    header('Content-Type: application/json; charset=utf-8');
    $tmdbId = isset($_GET['tmdb_id']) ? (int)$_GET['tmdb_id'] : 0;
    
    if ($tmdbId <= 0) {
        echo json_encode(['success' => false, 'error' => 'Valid tmdb_id parameter required'], JSON_UNESCAPED_SLASHES);
        exit;
    }

    $details = get_movie_details_by_tmdb_id($tmdbId);
    if ($details) {
        echo json_encode(['success' => true, 'movie' => $details], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Movie not found'], JSON_UNESCAPED_SLASHES);
    }
    exit;
}
