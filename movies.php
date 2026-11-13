<?php
/**
 * CINEMA BOOKING - Movie Listing & Filters
 * Professional, authentic cinema discovery interface (BookMyShow standard)
 */

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/backend/movie-process.php';

$pageTitle = 'Explore Movies - In Theatres & Upcoming Releases';

$status = $_GET['status'] ?? null;
$genre = $_GET['genre'] ?? null;
$language = $_GET['language'] ?? null;
$search = $_GET['q'] ?? null;

$movies = get_all_movies($status, $genre, $language, $search);

$extraJs = '<script src="' . asset_url('js/booking.js') . '"></script>';

include __DIR__ . '/includes/header.php';
?>

<div class="container mb-5">
    <!-- Clean, Professional Page Header -->
    <div class="page-header-wrap">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <h1 class="page-title">
                    <?= $status === 'coming_soon' ? 'Coming Soon to Cinemas' : ($status === 'now_showing' ? 'Now Showing in Theatres' : 'Explore Movies') ?>
                </h1>
                <p class="page-subtitle">
                    <?= !empty($search) ? 'Search results for "' . htmlspecialchars($search) . '" (' . count($movies) . ' titles found)' : 'Discover the latest blockbuster releases, advance bookings, and critically acclaimed cinema.' ?>
                </p>
            </div>

            <!-- Single Integrated Search Bar -->
            <form class="d-flex" action="<?= base_url('movies.php') ?>" method="GET" style="min-width: 280px; max-width: 360px;">
                <div class="input-group" style="background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 6px; overflow: hidden; width: 100%;">
                    <span class="input-group-text bg-transparent border-0 text-secondary ps-3"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control bg-transparent border-0 text-white shadow-none ps-2" placeholder="Search movies by title..." value="<?= htmlspecialchars($search ?? '') ?>" style="font-size: 0.875rem;">
                    <?php if ($status): ?>
                        <input type="hidden" name="status" value="<?= htmlspecialchars($status) ?>">
                    <?php endif; ?>
                    <?php if (!empty($search)): ?>
                        <a href="<?= base_url('movies.php' . ($status ? '?status=' . urlencode($status) : '')) ?>" class="btn btn-sm btn-link text-secondary text-decoration-none d-flex align-items-center pe-3" title="Clear Search">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Human-Crafted Filter Architecture: Status Segmented Control + Genre Chips -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <!-- Status Switcher (Segmented Rail) -->
        <div class="filter-segmented-rail">
            <button type="button" class="filter-seg-btn movie-filter-btn <?= (empty($status) && empty($genre)) ? 'active' : '' ?>" data-filter="all">
                All Titles
            </button>
            <button type="button" class="filter-seg-btn movie-filter-btn <?= $status === 'now_showing' ? 'active' : '' ?>" data-filter="now_showing">
                <i class="bi bi-play-circle-fill me-1 text-danger"></i>Now Showing
            </button>
            <button type="button" class="filter-seg-btn movie-filter-btn <?= $status === 'coming_soon' ? 'active' : '' ?>" data-filter="coming_soon">
                <i class="bi bi-calendar-event me-1"></i>Coming Soon
            </button>
        </div>

        <!-- Genre Filter Chips -->
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="text-secondary fs-xs text-uppercase letter-spacing fw-semibold me-1 d-none d-md-inline">Genre:</span>
            <button type="button" class="filter-chip-btn movie-filter-btn <?= $genre === 'Action' ? 'active' : '' ?>" data-filter="Action">Action</button>
            <button type="button" class="filter-chip-btn movie-filter-btn <?= $genre === 'Sci-Fi' ? 'active' : '' ?>" data-filter="Sci-Fi">Sci-Fi</button>
            <button type="button" class="filter-chip-btn movie-filter-btn <?= $genre === 'Drama' ? 'active' : '' ?>" data-filter="Drama">Drama</button>
            <button type="button" class="filter-chip-btn movie-filter-btn <?= $genre === 'Comedy' ? 'active' : '' ?>" data-filter="Comedy">Comedy</button>
        </div>
    </div>

    <!-- Movie Cards Grid -->
    <?php if (empty($movies)): ?>
        <div class="p-5 text-center my-5 rounded-3" style="background: var(--bg-card); border: 1px dashed var(--border);">
            <div class="fs-1 text-secondary mb-3"><i class="bi bi-film"></i></div>
            <h4 class="text-white fw-semibold">No Movies Found</h4>
            <p class="text-secondary small mb-4">We could not find any titles matching your current filter or search criteria.</p>
            <a href="<?= base_url('movies.php') ?>" class="btn btn-accent px-4">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filters
            </a>
        </div>
    <?php else: ?>
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3 g-md-4">
            <?php foreach ($movies as $movie): ?>
                <div class="col filterable-movie-col" 
                     data-genre="<?= htmlspecialchars($movie['genre']) ?>" 
                     data-status="<?= htmlspecialchars($movie['status']) ?>"
                     data-language="<?= htmlspecialchars($movie['language']) ?>">
                    <div class="movie-grid-card">
                        <!-- Poster Container with Authentic 2:3 Ratio -->
                        <a href="<?= base_url('movie-details.php?id=' . $movie['id']) ?>" class="movie-poster-container text-decoration-none">
                            <img src="<?= movie_poster_url($movie['poster_url']) ?>" 
                                 alt="<?= htmlspecialchars($movie['title']) ?>" 
                                 class="movie-poster-img" 
                                 loading="lazy"
                                 onerror="this.onerror=null; this.src='<?= asset_url('images/poster-placeholder.svg') ?>';">
                            <div class="poster-meta-strip">
                                <span class="poster-rating">
                                    <i class="bi bi-star-fill"></i> <?= number_format((float)$movie['rating'], 1) ?>
                                </span>
                                <span class="poster-cert"><?= htmlspecialchars($movie['certificate'] ?: 'UA') ?></span>
                            </div>
                        </a>

                        <!-- Movie Card Metadata & Action -->
                        <div class="movie-card-content">
                            <h3 class="movie-title">
                                <a href="<?= base_url('movie-details.php?id=' . $movie['id']) ?>" class="text-decoration-none text-reset" title="<?= htmlspecialchars($movie['title']) ?>">
                                    <?= htmlspecialchars($movie['title']) ?>
                                </a>
                            </h3>
                            <div class="movie-genre d-flex align-items-center justify-content-between">
                                <span><?= htmlspecialchars($movie['genre']) ?></span>
                                <span class="text-secondary opacity-75"><?= htmlspecialchars($movie['language']) ?></span>
                            </div>
                            <div class="movie-meta d-flex align-items-center justify-content-between text-secondary mb-3" style="font-size: 0.75rem;">
                                <span><i class="bi bi-clock me-1"></i><?= format_duration((int)$movie['duration_mins']) ?></span>
                                <span class="badge bg-dark border border-secondary border-opacity-50 text-light fw-normal" style="font-size: 0.65rem;">2D, IMAX</span>
                            </div>
                            <div class="movie-card-action">
                                <?php if ($movie['status'] === 'now_showing'): ?>
                                    <a href="<?= base_url('movie-details.php?id=' . $movie['id']) ?>" class="btn-book-card text-decoration-none">
                                        Book Tickets
                                    </a>
                                <?php else: ?>
                                    <a href="<?= base_url('movie-details.php?id=' . $movie['id']) ?>" class="btn-details-card text-decoration-none">
                                        View Details
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Trailer Video Modal -->
<div class="modal fade" id="trailerModal" tabindex="-1" aria-labelledby="trailerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-black border border-secondary">
            <div class="modal-header border-secondary border-opacity-50">
                <h5 class="modal-title text-white" id="trailerModalLabel"><i class="bi bi-play-circle text-danger me-2"></i>Official Movie Trailer</h5>
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
