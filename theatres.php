<?php
/**
 * CINEMA BOOKING - Theatre Listing
 * Multiplex & Screenings Discovery
 */

require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Cinemas & Theatres - Select Multiplex';
$activeCity = get_selected_city();
$pdo = getDBConnection();

// Fetch theatres in active city
$stmt = $pdo->prepare("
    SELECT t.*, c.name AS city_name, c.state AS city_state,
           (SELECT COUNT(*) FROM screens sc WHERE sc.theatre_id = t.id) AS total_screens
    FROM theatres t
    JOIN cities c ON t.city_id = c.id
    WHERE t.city_id = ? AND t.is_active = 1
    ORDER BY t.name ASC
");
$stmt->execute([$activeCity['id']]);
$theatres = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="container mb-5">
    <div class="page-header-wrap">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <h1 class="page-title">Cinemas in <?= htmlspecialchars($activeCity['name']) ?></h1>
                <p class="page-subtitle">Explore premier multiplexes featuring IMAX, 4DX, and Dolby Atmos audio.</p>
            </div>

            <!-- City Switcher -->
            <div class="dropdown">
                <button class="btn btn-outline-dark-custom dropdown-toggle btn-sm" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-geo-alt-fill text-danger me-1"></i><?= htmlspecialchars($activeCity['name']) ?> (Change City)
                </button>
                <ul class="dropdown-menu dropdown-menu-city dropdown-menu-end shadow">
                    <?php foreach (get_all_cities() as $c): ?>
                        <li><a class="dropdown-item" href="?city=<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <?php if (empty($theatres)): ?>
        <div class="p-5 text-center my-4 rounded-3" style="background: var(--bg-card); border: 1px dashed var(--border);">
            <div class="fs-1 text-secondary mb-3"><i class="bi bi-building-x"></i></div>
            <h4 class="text-white fw-semibold">No Theatres Listed in <?= htmlspecialchars($activeCity['name']) ?></h4>
            <p class="text-secondary small mb-3">Please select another city from the location dropdown above.</p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($theatres as $theatre): ?>
                <?php
                // Fetch active shows today for this theatre
                $showStmt = $pdo->prepare("
                    SELECT s.id, s.show_time, s.regular_price, sc.screen_name, sc.screen_type,
                           m.id AS movie_id, m.title AS movie_title, m.poster_url, m.certificate
                    FROM shows s
                    JOIN screens sc ON s.screen_id = sc.id
                    JOIN movies m ON s.movie_id = m.id
                    WHERE s.theatre_id = ? AND s.show_date >= CURDATE() AND s.status = 'active'
                    ORDER BY s.show_date ASC, s.show_time ASC
                    LIMIT 8
                ");
                $showStmt->execute([$theatre['id']]);
                $runningShows = $showStmt->fetchAll();
                ?>
                <div class="col-12">
                    <div class="theatre-show-card">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3 pb-3 mb-3 border-bottom border-secondary border-opacity-25">
                            <div>
                                <h3 class="theatre-name mb-1">
                                    <i class="bi bi-building text-danger me-2"></i><?= htmlspecialchars($theatre['name']) ?>
                                </h3>
                                <p class="theatre-address mb-2">
                                    <i class="bi bi-geo-alt me-1 text-secondary"></i><?= htmlspecialchars($theatre['address']) ?>
                                </p>
                                <div class="d-flex align-items-center gap-3 text-secondary fs-xs">
                                    <?php if (!empty($theatre['contact_phone'])): ?>
                                        <span><i class="bi bi-telephone me-1 text-danger"></i><?= htmlspecialchars($theatre['contact_phone']) ?></span>
                                    <?php endif; ?>
                                    <span><i class="bi bi-tv me-1 text-secondary"></i><?= (int)$theatre['total_screens'] ?> Screens</span>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-1">
                                <?php 
                                $facs = explode(',', $theatre['facilities']);
                                foreach ($facs as $fac): 
                                ?>
                                    <span class="badge bg-dark border border-secondary border-opacity-50 text-secondary fs-xs">
                                        <?= htmlspecialchars(trim($fac)) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Shows Running -->
                        <div>
                            <h6 class="text-white-50 text-uppercase fs-xs fw-bold mb-3">Available Screenings</h6>
                            <?php if (empty($runningShows)): ?>
                                <p class="text-secondary small fst-italic mb-0">No active shows scheduled for today. Please check back shortly.</p>
                            <?php else: ?>
                                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
                                    <?php foreach ($runningShows as $rShow): ?>
                                        <div class="col">
                                            <a href="<?= base_url('seats.php?show_id=' . $rShow['id']) ?>" class="theatre-screening-card">
                                                <div class="d-flex align-items-center gap-2 mb-2">
                                                    <img src="<?= movie_poster_url($rShow['poster_url']) ?>" alt="" style="width: 38px; height: 52px; object-fit: cover; border-radius: 4px;" onerror="this.onerror=null; this.src='<?= asset_url('images/poster-placeholder.svg') ?>';">
                                                    <div class="text-truncate">
                                                        <div class="text-white fw-semibold text-truncate" style="font-size: 0.875rem;"><?= htmlspecialchars($rShow['movie_title']) ?></div>
                                                        <span class="badge bg-dark border border-secondary text-secondary fs-xs"><?= htmlspecialchars($rShow['certificate'] ?: 'UA') ?></span>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center pt-2 border-top border-secondary border-opacity-25">
                                                    <span class="text-danger fw-bold fs-6"><?= format_time($rShow['show_time']) ?></span>
                                                    <span class="badge bg-dark border border-secondary text-light fs-xs"><?= htmlspecialchars($rShow['screen_type']) ?></span>
                                                </div>
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
