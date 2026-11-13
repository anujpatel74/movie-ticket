<?php
/**
 * CINEMA BOOKING - Booking Selection Flow (Movie -> City -> Theatre -> Date -> Show)
 */

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/backend/movie-process.php';

$pageTitle = 'Select Show & Booking Details';
$pdo = getDBConnection();

$selectedMovieId = isset($_GET['movie_id']) ? (int)$_GET['movie_id'] : 0;
$selectedCityId = isset($_GET['city_id']) ? (int)$_GET['city_id'] : get_selected_city()['id'];

// If show_id is directly passed, forward to seats.php
if (!empty($_GET['show_id'])) {
    redirect(base_url('seats.php?show_id=' . (int)$_GET['show_id']));
}

$allMovies = get_now_showing_movies(20);
$allCities = get_all_cities();

// If a movie is selected, fetch theatres showing this movie
$theatresWithShows = [];
if ($selectedMovieId > 0) {
    $theatresWithShows = get_movie_theatre_shows($selectedMovieId, date('Y-m-d'), $selectedCityId);
}

include __DIR__ . '/includes/header.php';
?>

<div class="container mb-5">
    <div class="page-header-wrap text-center">
        <span class="badge bg-dark border border-danger text-danger px-3 py-1 fs-xs text-uppercase letter-spacing mb-2">Step 1 of 4</span>
        <h1 class="page-title">Select Show & Theatre</h1>
        <p class="page-subtitle">Choose your preferred movie, city, theatre, and showtime</p>
    </div>

    <div class="row g-4 justify-content-center">
        <!-- Selection Form Column -->
        <div class="col-lg-10">
            <div class="p-4 p-md-5 rounded-3" style="background: var(--bg-card); border: 1px solid var(--border);">
                <form action="<?= base_url('booking.php') ?>" method="GET" class="row g-3">
                    <!-- Movie Selector -->
                    <div class="col-md-6">
                        <label class="form-label text-white small fw-bold">1. Select Movie</label>
                        <select name="movie_id" class="form-select cinematic-input" onchange="this.form.submit()">
                            <option value="">-- Choose a Movie --</option>
                            <?php foreach ($allMovies as $m): ?>
                                <option value="<?= $m['id'] ?>" <?= ($m['id'] == $selectedMovieId) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($m['title']) ?> (<?= htmlspecialchars($m['language']) ?> &bull; <?= htmlspecialchars($m['certificate']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- City Selector -->
                    <div class="col-md-6">
                        <label class="form-label text-white small fw-bold">2. Select City</label>
                        <select name="city_id" class="form-select cinematic-input" onchange="this.form.submit()">
                            <?php foreach ($allCities as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= ($c['id'] == $selectedCityId) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>

                <?php if ($selectedMovieId > 0): ?>
                    <hr class="border-secondary border-opacity-25 my-4">

                    <h5 class="text-white fw-bold mb-3">Available Theatres & Timings for Today</h5>

                    <?php if (empty($theatresWithShows)): ?>
                        <div class="p-4 text-center rounded-2 bg-dark border border-secondary border-opacity-25">
                            <i class="bi bi-clock-history text-warning fs-3 mb-2 d-block"></i>
                            <h6 class="text-white">No Shows Scheduled Today in this City</h6>
                            <p class="text-secondary small mb-3">You can view more dates on the movie details page.</p>
                            <a href="<?= base_url('movie-details.php?id=' . $selectedMovieId) ?>" class="btn btn-sm btn-accent px-3">
                                View Full Movie Schedule
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($theatresWithShows as $tItem): ?>
                                <div class="p-3 rounded-2" style="background: var(--bg-secondary); border: 1px solid var(--border);">
                                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-2">
                                        <div>
                                            <h6 class="text-white fw-semibold m-0"><?= htmlspecialchars($tItem['theatre_name']) ?></h6>
                                            <small class="text-secondary"><?= htmlspecialchars($tItem['theatre_address']) ?></small>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                        <?php foreach ($tItem['shows'] as $sh): ?>
                                            <a href="<?= base_url('seats.php?show_id=' . $sh['id']) ?>" class="showtime-badge">
                                                <span><?= format_time($sh['show_time']) ?></span>
                                                <span class="show-screen-type"><?= htmlspecialchars($sh['screen_type']) ?></span>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-5 text-secondary">
                        <i class="bi bi-ticket-perforated fs-1 mb-2 d-block text-danger"></i>
                        <p class="mb-0">Please select a movie above to view showtimes and book seats.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
