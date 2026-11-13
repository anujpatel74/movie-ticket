<?php
/**
 * CINEMA BOOKING - Movie Query and Processing Backend
 */

require_once __DIR__ . '/../includes/functions.php';

function get_featured_movies(): array {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM movies WHERE is_featured = 1 AND status = 'now_showing' ORDER BY rating DESC LIMIT 5");
    return $stmt->fetchAll();
}

function get_now_showing_movies(int $limit = 8): array {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM movies WHERE status = 'now_showing' ORDER BY release_date DESC LIMIT ?");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function get_coming_soon_movies(int $limit = 6): array {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM movies WHERE status = 'coming_soon' ORDER BY release_date ASC LIMIT ?");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function get_all_movies(?string $status = null, ?string $genre = null, ?string $language = null, ?string $search = null): array {
    $pdo = getDBConnection();
    $sql = "SELECT * FROM movies WHERE 1=1";
    $params = [];

    if (!empty($status)) {
        $sql .= " AND status = ?";
        $params[] = $status;
    }

    if (!empty($genre)) {
        $sql .= " AND genre LIKE ?";
        $params[] = '%' . $genre . '%';
    }

    if (!empty($language)) {
        $sql .= " AND language = ?";
        $params[] = $language;
    }

    if (!empty($search)) {
        $sql .= " AND (title LIKE ? OR description LIKE ? OR genre LIKE ?)";
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }

    $sql .= " ORDER BY status ASC, release_date DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function get_movie_by_id_or_slug($identifier): ?array {
    $pdo = getDBConnection();
    if (is_numeric($identifier)) {
        $stmt = $pdo->prepare("SELECT * FROM movies WHERE id = ? LIMIT 1");
    } else {
        $stmt = $pdo->prepare("SELECT * FROM movies WHERE slug = ? LIMIT 1");
    }
    $stmt->execute([$identifier]);
    $movie = $stmt->fetch();
    return $movie ?: null;
}

/**
 * Fetch available show dates for a movie
 */
function get_movie_show_dates(int $movieId): array {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT DISTINCT show_date 
        FROM shows 
        WHERE movie_id = ? AND show_date >= CURDATE() AND status = 'active'
        ORDER BY show_date ASC
        LIMIT 7
    ");
    $stmt->execute([$movieId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Fetch shows for a movie on a specific date, grouped by theatre and screen
 */
function get_movie_theatre_shows(int $movieId, string $showDate, ?int $cityId = null): array {
    $pdo = getDBConnection();
    
    $sql = "
        SELECT s.*, 
               t.id AS theatre_id, t.name AS theatre_name, t.address AS theatre_address, t.facilities,
               sc.screen_name, sc.screen_type
        FROM shows s
        JOIN theatres t ON s.theatre_id = t.id
        JOIN screens sc ON s.screen_id = sc.id
        WHERE s.movie_id = ? AND s.show_date = ? AND s.status = 'active'
    ";
    $params = [$movieId, $showDate];

    if ($cityId) {
        $sql .= " AND t.city_id = ?";
        $params[] = $cityId;
    }

    $sql .= " ORDER BY t.name ASC, s.show_time ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $shows = $stmt->fetchAll();

    // Group by theatre
    $grouped = [];
    foreach ($shows as $sh) {
        $tid = $sh['theatre_id'];
        if (!isset($grouped[$tid])) {
            $grouped[$tid] = [
                'theatre_id'      => $sh['theatre_id'],
                'theatre_name'    => $sh['theatre_name'],
                'theatre_address' => $sh['theatre_address'],
                'facilities'      => $sh['facilities'],
                'shows'           => []
            ];
        }
        $grouped[$tid]['shows'][] = $sh;
    }

    return array_values($grouped);
}

