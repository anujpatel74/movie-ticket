<?php
/**
 * CINEMA BOOKING - Homepage
 * Premium, Authentic Cinema Discovery Experience
 */

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/backend/movie-process.php';

$pageTitle = 'Home - Blockbuster Cinema & Instant Booking';
$featuredMovies = get_featured_movies();
$nowShowing = get_now_showing_movies(8);
$comingSoon = get_coming_soon_movies(4);

include __DIR__ . '/includes/header.php';
?>

<div class="container mb-5">
    <!-- Hero Banner Carousel (Widescreen Cinematic Marquee) -->
    <?php if (!empty($featuredMovies)): ?>
        <div class="hero-carousel-wrap mb-5">
            <div id="heroMovieCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="6500">
                <div class="carousel-indicators">
                    <?php foreach ($featuredMovies as $idx => $m): ?>
                        <button type="button" data-bs-target="#heroMovieCarousel" data-bs-slide-to="<?= $idx ?>" class="<?= $idx === 0 ? 'active' : '' ?>" aria-current="<?= $idx === 0 ? 'true' : 'false' ?>" aria-label="Slide <?= $idx + 1 ?>"></button>
                    <?php endforeach; ?>
                </div>

                <div class="carousel-inner">
                    <?php foreach ($featuredMovies as $idx => $movie): ?>
                        <div class="carousel-item hero-carousel-item <?= $idx === 0 ? 'active' : '' ?>">
                            <img src="<?= movie_backdrop_url($movie['backdrop_url'], $movie['poster_url']) ?>" class="hero-backdrop-img" alt="<?= htmlspecialchars($movie['title']) ?>" onerror="this.onerror=null; this.src='<?= asset_url('images/poster-placeholder.svg') ?>';">
                            <div class="hero-overlay"></div>
                            <div class="hero-content">
                                <div class="hero-premiere-pill">
                                    <span class="hero-live-dot"></span> NOW SCREENING • PREMIERE
                                </div>
                                <h1 class="hero-title"><?= htmlspecialchars($movie['title']) ?></h1>
                                
                                <div class="hero-meta-bar">
                                    <span class="hero-rating-pill">
                                        <i class="bi bi-star-fill text-warning"></i>
                                        <?= number_format((float)$movie['rating'], 1) ?>/10
                                    </span>
                                    <span>&bull;</span>
                                    <span><i class="bi bi-clock me-1"></i><?= format_duration((int)$movie['duration_mins']) ?></span>
                                    <span>&bull;</span>
                                    <span class="badge bg-dark border border-secondary border-opacity-50 text-light"><?= htmlspecialchars($movie['certificate'] ?: 'UA') ?></span>
                                    <span>&bull;</span>
                                    <span><?= htmlspecialchars($movie['language']) ?></span>
                                    <span>&bull;</span>
                                    <span class="text-light"><?= htmlspecialchars($movie['genre']) ?></span>
                                </div>

                                <p class="hero-synopsis">
                                    <?= htmlspecialchars($movie['description']) ?>
                                </p>

                                <div class="d-flex align-items-center gap-3">
                                    <a href="<?= base_url('movie-details.php?id=' . $movie['id']) ?>" class="btn btn-accent px-4 py-2">
                                        <i class="bi bi-ticket-perforated me-2"></i>Book Tickets
                                    </a>
                                    <?php if (!empty($movie['trailer_url'])): ?>
                                        <button type="button" class="btn btn-outline-dark-custom px-4 py-2" data-bs-toggle="modal" data-bs-target="#trailerModal" data-trailer-url="<?= htmlspecialchars($movie['trailer_url']) ?>">
                                            <i class="bi bi-play-circle me-2 text-danger"></i>Watch Trailer
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <button class="carousel-control-prev" type="button" data-bs-target="#heroMovieCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-label="Previous"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#heroMovieCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-label="Next"></span>
                </button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Cinema Experience Benchmark Ribbon (Single unified horizontal bar - No AI 3-card slop) -->
    <div class="cinema-tech-strip mb-5">
        <div class="cinema-tech-item">
            <div class="tech-icon-wrap"><i class="bi bi-badge-hd"></i></div>
            <div class="tech-details">
                <span class="tech-title">IMAX® with Laser</span>
                <span class="tech-desc">Next-gen 4K dual laser & deep contrast</span>
            </div>
        </div>
        <div class="cinema-tech-divider"></div>
        <div class="cinema-tech-item">
            <div class="tech-icon-wrap"><i class="bi bi-soundwave"></i></div>
            <div class="tech-details">
                <span class="tech-title">Dolby Atmos® 64-Ch</span>
                <span class="tech-desc">Immersive multidimensional acoustic spatial audio</span>
            </div>
        </div>
        <div class="cinema-tech-divider"></div>
        <div class="cinema-tech-item">
            <div class="tech-icon-wrap"><i class="bi bi-gem"></i></div>
            <div class="tech-details">
                <span class="tech-title">VIP Recliner Loungers</span>
                <span class="tech-desc">Full-motorized leather recliners & dine-in</span>
            </div>
        </div>
        <div class="cinema-tech-divider"></div>
        <div class="cinema-tech-item">
            <div class="tech-icon-wrap"><i class="bi bi-qr-code-scan"></i></div>
            <div class="tech-details">
                <span class="tech-title">Contactless M-Pass</span>
                <span class="tech-desc">Direct smartphone turnstile scan & quick entry</span>
            </div>
        </div>
    </div>

    <!-- Now Showing Section -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h2 class="section-heading-editorial">Now Showing</h2>
                <span class="section-count-badge"><?= count($nowShowing) ?> Titles</span>
            </div>
            <p class="text-secondary small mb-0">Book tickets for movies currently playing in cinemas near you</p>
        </div>
        <a href="<?= base_url('movies.php?status=now_showing') ?>" class="btn btn-outline-dark-custom btn-sm">
            See All <i class="bi bi-chevron-right ms-1"></i>
        </a>
    </div>

    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3 g-md-4 mb-5">
        <?php foreach ($nowShowing as $movie): ?>
            <div class="col">
                <div class="movie-grid-card">
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
                            <a href="<?= base_url('movie-details.php?id=' . $movie['id']) ?>" class="btn-book-card text-decoration-none">
                                Book Tickets
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Coming Soon Section -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h2 class="section-heading-editorial">Coming Soon</h2>
                <span class="section-count-badge"><?= count($comingSoon) ?> Upcoming</span>
            </div>
            <p class="text-secondary small mb-0">Upcoming blockbuster releases heading to the big screen soon</p>
        </div>
        <a href="<?= base_url('movies.php?status=coming_soon') ?>" class="btn btn-outline-dark-custom btn-sm">
            See All <i class="bi bi-chevron-right ms-1"></i>
        </a>
    </div>

    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3 g-md-4 mb-5">
        <?php foreach ($comingSoon as $movie): ?>
            <div class="col">
                <div class="movie-grid-card">
                    <a href="<?= base_url('movie-details.php?id=' . $movie['id']) ?>" class="movie-poster-container text-decoration-none">
                        <img src="<?= movie_poster_url($movie['poster_url']) ?>" 
                             alt="<?= htmlspecialchars($movie['title']) ?>" 
                             class="movie-poster-img" 
                             loading="lazy"
                             onerror="this.onerror=null; this.src='<?= asset_url('images/poster-placeholder.svg') ?>';">
                        <div class="poster-meta-strip">
                            <span class="text-white fw-medium small" style="font-size: 0.75rem;">
                                <i class="bi bi-calendar3 me-1 text-danger"></i> <?= format_date($movie['release_date'], 'M Y') ?>
                            </span>
                            <span class="poster-cert"><?= htmlspecialchars($movie['certificate'] ?: 'UA') ?></span>
                        </div>
                    </a>

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
                            <span class="badge bg-dark border border-secondary border-opacity-50 text-light fw-normal" style="font-size: 0.65rem;">Teaser Out</span>
                        </div>
                        <div class="movie-card-action">
                            <a href="<?= base_url('movie-details.php?id=' . $movie['id']) ?>" class="btn-details-card text-decoration-none">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
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
