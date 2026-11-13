<?php
/**
 * CINEMA BOOKING - Movie Details & Showtime Selection
 * Authentic, high-contrast cinema production view
 */

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/backend/movie-process.php';
require_once __DIR__ . '/backend/movie-details.php';

$movieId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$tmdbId = isset($_GET['tmdb_id']) ? (int)$_GET['tmdb_id'] : 0;
$movie = null;
$isLocalMovie = true;

if ($movieId > 0) {
    $movie = get_movie_by_id_or_slug($movieId);
    $isLocalMovie = true;
} elseif ($tmdbId > 0) {
    $movie = get_movie_details_by_tmdb_id($tmdbId);
    $isLocalMovie = !empty($movie['is_local']);
}

if (!$movie) {
    set_flash('danger', 'The requested movie was not found.');
    redirect(base_url('movies.php'));
}

$pageTitle = $movie['title'] . ' - Showtimes & Tickets';
$activeCity = get_selected_city();

// Fetch available show dates if local movie
$availableDates = [];
$theatreShows = [];
$selectedDate = date('Y-m-d');

if ($isLocalMovie) {
    $localId = (int)$movie['id'];
    $availableDates = get_movie_show_dates($localId);
    if (empty($availableDates)) {
        // If no future shows, provide today and next 2 days as fallback
        $availableDates = [date('Y-m-d'), date('Y-m-d', strtotime('+1 day')), date('Y-m-d', strtotime('+2 days'))];
    }

    $selectedDate = $_GET['date'] ?? $availableDates[0];
    if (!in_array($selectedDate, $availableDates)) {
        $selectedDate = $availableDates[0];
    }

    // Fetch shows for the selected date
    $theatreShows = get_movie_theatre_shows($localId, $selectedDate, $activeCity['id']);
}

$extraJs = '<script src="' . asset_url('js/booking.js') . '"></script>';

include __DIR__ . '/includes/header.php';
?>

<div class="container mb-5">
    <!-- Movie Details Hero Banner -->
    <div class="movie-details-hero">
        <div class="movie-backdrop-bg" style="background-image: url('<?= movie_backdrop_url($movie['backdrop_url'], $movie['poster_url']) ?>');"></div>
        <div class="movie-details-content">
            <div class="row g-4 align-items-center">
                <div class="col-md-4 col-lg-3 text-center text-md-start">
                    <div class="movie-details-poster mx-auto">
                        <img src="<?= movie_poster_url($movie['poster_url']) ?>" alt="<?= htmlspecialchars($movie['title']) ?>" class="img-fluid" onerror="this.onerror=null; this.src='<?= asset_url('images/poster-placeholder.svg') ?>';">
                    </div>
                </div>

                <div class="col-md-8 col-lg-9">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <span class="badge bg-danger fs-xs px-2 py-1"><?= htmlspecialchars($movie['certificate'] ?: 'UA') ?></span>
                        <span class="badge bg-dark border border-secondary border-opacity-50 text-light fs-xs px-2 py-1"><?= htmlspecialchars($movie['language']) ?></span>
                        <span class="badge bg-dark border border-secondary border-opacity-50 text-secondary fs-xs px-2 py-1"><?= htmlspecialchars($movie['status'] === 'now_showing' ? 'Now Showing' : 'Coming Soon') ?></span>
                    </div>

                    <h1 class="text-white fw-bold mb-2" style="font-size: 2.25rem; letter-spacing: -0.02em;"><?= htmlspecialchars($movie['title']) ?></h1>

                    <div class="d-flex align-items-center gap-3 text-secondary small mb-3 flex-wrap">
                        <span class="d-inline-flex align-items-center gap-1 text-white fw-semibold">
                            <i class="bi bi-star-fill" style="color: var(--rating-star);"></i>
                            <?= number_format((float)$movie['rating'], 1) ?>/10 IMDb
                        </span>
                        <span>•</span>
                        <span><i class="bi bi-clock me-1"></i><?= format_duration((int)$movie['duration_mins']) ?></span>
                        <span>•</span>
                        <span><i class="bi bi-film me-1"></i><?= htmlspecialchars($movie['genre']) ?></span>
                        <span>•</span>
                        <span>Released: <?= format_date($movie['release_date']) ?></span>
                    </div>

                    <h6 class="text-white-50 text-uppercase fs-xs fw-bold mt-3 mb-1">Synopsis</h6>
                    <p class="text-light mb-4 pe-lg-5" style="max-width: 820px; font-size: 0.925rem; line-height: 1.6; color: #D1D5DB;">
                        <?= htmlspecialchars($movie['description']) ?>
                    </p>

                    <div class="d-flex gap-3 flex-wrap">
                        <?php if ($isLocalMovie): ?>
                            <a href="#showtimesSection" class="btn btn-accent px-4 py-2">
                                <i class="bi bi-ticket-perforated me-2"></i>Book Tickets
                            </a>
                        <?php else: ?>
                            <a href="<?= base_url('movies.php?status=now_showing') ?>" class="btn btn-accent px-4 py-2">
                                <i class="bi bi-film me-2"></i>Browse Now Showing
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($movie['trailer_url'])): ?>
                            <button type="button" class="btn btn-outline-dark-custom px-4 py-2" data-bs-toggle="modal" data-bs-target="#trailerModal" data-trailer-url="<?= htmlspecialchars($movie['trailer_url']) ?>">
                                <i class="bi bi-play-circle text-danger me-2"></i>Watch Trailer
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($isLocalMovie): ?>
    <!-- Showtimes Section -->
    <div id="showtimesSection" class="pt-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h3 class="section-heading-editorial m-0">Select Showtimes</h3>
                <p class="text-secondary small mb-0">Browsing cinemas in <strong class="text-white"><?= htmlspecialchars($activeCity['name']) ?></strong></p>
            </div>
            
            <!-- City switcher link -->
            <div class="dropdown">
                <button class="btn btn-outline-dark-custom btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-geo-alt text-danger me-1"></i>Change City (<?= htmlspecialchars($activeCity['name']) ?>)
                </button>
                <ul class="dropdown-menu dropdown-menu-city dropdown-menu-end shadow">
                    <?php foreach (get_all_cities() as $c): ?>
                        <li><a class="dropdown-item" href="?id=<?= $movie['id'] ?>&city=<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- Date Pills -->
        <div class="date-selector-wrap">
            <?php foreach ($availableDates as $date): ?>
                <?php
                    $dt = new DateTime($date);
                    $isToday = ($date === date('Y-m-d'));
                    $isTomorrow = ($date === date('Y-m-d', strtotime('+1 day')));
                    $dayLabel = $isToday ? 'TODAY' : ($isTomorrow ? 'TOM' : strtoupper($dt->format('D')));
                    $isActive = ($date === $selectedDate);
                ?>
                <a href="?id=<?= $movie['id'] ?>&date=<?= $date ?><?= isset($_GET['city']) ? '&city=' . (int)$_GET['city'] : '' ?>" class="date-pill <?= $isActive ? 'active' : '' ?>">
                    <span class="day-name"><?= $dayLabel ?></span>
                    <span class="day-number"><?= $dt->format('d') ?></span>
                    <span class="month-name"><?= strtoupper($dt->format('M')) ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Theatres & Shows for Selected Date -->
        <?php if (empty($theatreShows)): ?>
            <div class="p-5 text-center my-4 rounded-3" style="background: var(--bg-card); border: 1px dashed var(--border);">
                <div class="fs-1 text-secondary mb-3"><i class="bi bi-calendar-x"></i></div>
                <h5 class="text-white fw-semibold">No Shows Available in <?= htmlspecialchars($activeCity['name']) ?> for this Date</h5>
                <p class="text-secondary small mb-3">Please select another date above or switch to a nearby city.</p>
                <div class="d-flex justify-content-center gap-2 mt-3">
                    <?php if (count($availableDates) > 1): ?>
                        <a href="?id=<?= $movie['id'] ?>&date=<?= $availableDates[1] ?>" class="btn btn-sm btn-accent px-3">
                            Check <?= date('d M', strtotime($availableDates[1])) ?> Shows
                        </a>
                    <?php endif; ?>
                    <a href="<?= base_url('theatres.php') ?>" class="btn btn-sm btn-outline-dark-custom px-3">Explore All Theatres</a>
                </div>
            </div>
        <?php else: ?>
            <div class="theatre-shows-list">
                <?php foreach ($theatreShows as $theatre): ?>
                    <div class="theatre-show-card">
                        <div class="row align-items-center g-3">
                            <div class="col-lg-4">
                                <h5 class="theatre-name mb-1">
                                    <i class="bi bi-building text-danger me-2"></i><?= htmlspecialchars($theatre['theatre_name']) ?>
                                </h5>
                                <p class="theatre-address mb-2"><?= htmlspecialchars($theatre['theatre_address']) ?></p>
                                <div class="d-flex gap-1 flex-wrap">
                                    <?php 
                                    $facs = explode(',', $theatre['facilities']);
                                    foreach ($facs as $f): 
                                    ?>
                                        <span class="badge bg-dark border border-secondary border-opacity-50 text-secondary fs-xs">
                                            <?= htmlspecialchars(trim($f)) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="col-lg-8">
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach ($theatre['shows'] as $show): ?>
                                        <a href="<?= base_url('seats.php?show_id=' . $show['id']) ?>" 
                                           class="showtime-badge" 
                                           data-bs-toggle="tooltip" 
                                           data-bs-placement="top" 
                                           data-bs-title="<?= htmlspecialchars($show['screen_name']) ?> &bull; Regular: ₹<?= number_format($show['regular_price']) ?> | Premium: ₹<?= number_format($show['premium_price']) ?> | Recliner: ₹<?= number_format($show['recliner_price']) ?>">
                                            <span><?= format_time($show['show_time']) ?></span>
                                            <span class="show-screen-type"><?= htmlspecialchars($show['screen_type']) ?></span>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php else: ?>
        <!-- TMDB Global Discovery Notice -->
        <div class="my-5 p-4 p-md-5 rounded-3 text-center" style="background: var(--bg-card); border: 1px dashed var(--border);">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 60px; height: 60px; background: rgba(229, 9, 69, 0.12); color: var(--accent);">
                <i class="bi bi-globe2 fs-3"></i>
            </div>
            <h4 class="text-white fw-bold mb-2">Global Movie Discovery (TMDB)</h4>
            <p class="text-secondary small mx-auto mb-4" style="max-width: 620px;">
                This title is catalogued from The Movie Database (TMDB). Showtimes have not yet been scheduled in our participating multiplex theatres. Check out currently playing blockbusters or request notification for future release!
            </p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="<?= base_url('movies.php?status=now_showing') ?>" class="btn btn-accent px-4 py-2">
                    <i class="bi bi-ticket-perforated me-1"></i>View In-Theatre Movies
                </a>
                <button type="button" class="btn btn-outline-dark-custom px-4 py-2" onclick="alert('Notification alert saved! We will notify you when screening tickets become available.');">
                    <i class="bi bi-bell me-1"></i>Notify Me on Release
                </button>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Trailer Video Modal -->
<div class="modal fade" id="trailerModal" tabindex="-1" aria-labelledby="trailerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-black border border-secondary">
            <div class="modal-header border-secondary border-opacity-50">
                <h5 class="modal-title text-white" id="trailerModalLabel"><i class="bi bi-play-circle text-danger me-2"></i><?= htmlspecialchars($movie['title']) ?> - Official Trailer</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="ratio ratio-16x9">
                    <iframe id="trailerVideoFrame" src="" title="Movie Trailer" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
