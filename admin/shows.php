<?php
/**
 * CINEMA BOOKING - Admin Showtimes Management
 */

require_once __DIR__ . '/includes/auth-check.php';

$adminTitle = 'Manage Showtimes';
$pdo = getDBConnection();

$action = $_POST['action'] ?? '';

// Handle Delete Show
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete') {
    $token = $_POST['csrf_token'] ?? '';
    if (verify_csrf($token)) {
        $showId = (int)($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM shows WHERE id = ?");
        $stmt->execute([$showId]);
        set_flash('success', 'Showtime deleted successfully.');
    }
    redirect(base_url('admin/shows.php'));
}

// Handle Add Show
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        set_flash('danger', 'Security validation failed.');
        redirect(base_url('admin/shows.php'));
    }

    $movieId = (int)($_POST['movie_id'] ?? 0);
    $theatreId = (int)($_POST['theatre_id'] ?? 0);
    $screenId = (int)($_POST['screen_id'] ?? 0);
    $showDate = $_POST['show_date'] ?? date('Y-m-d');
    $showTime = $_POST['show_time'] ?? '18:00:00';
    $regPrice = (float)($_POST['regular_price'] ?? 200.00);
    $premPrice = (float)($_POST['premium_price'] ?? 300.00);
    $recPrice = (float)($_POST['recliner_price'] ?? 450.00);

    if ($movieId <= 0 || $theatreId <= 0 || $screenId <= 0) {
        set_flash('danger', 'Please select valid movie, theatre, and screen.');
        redirect(base_url('admin/shows.php'));
    }

    $stmt = $pdo->prepare("
        INSERT INTO shows (movie_id, theatre_id, screen_id, show_date, show_time, regular_price, premium_price, recliner_price, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')
    ");
    $stmt->execute([$movieId, $theatreId, $screenId, $showDate, $showTime, $regPrice, $premPrice, $recPrice]);
    set_flash('success', 'Show scheduled successfully!');
    redirect(base_url('admin/shows.php'));
}

// Fetch shows
$shows = $pdo->query("
    SELECT s.*, m.title AS movie_title, m.poster_url, t.name AS theatre_name, sc.screen_name, sc.screen_type,
           (SELECT COUNT(*) FROM booking_seats bs WHERE bs.show_id = s.id) AS booked_count
    FROM shows s
    JOIN movies m ON s.movie_id = m.id
    JOIN theatres t ON s.theatre_id = t.id
    JOIN screens sc ON s.screen_id = sc.id
    ORDER BY s.show_date DESC, s.show_time ASC
")->fetchAll();

$movies = $pdo->query("SELECT id, title FROM movies WHERE status = 'now_showing' ORDER BY title ASC")->fetchAll();
$theatres = $pdo->query("SELECT id, name FROM theatres ORDER BY name ASC")->fetchAll();
$screens = $pdo->query("SELECT id, theatre_id, screen_name FROM screens ORDER BY screen_name ASC")->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="text-white fw-bold mb-1">Showtimes & Schedules</h2>
        <p class="text-secondary small mb-0">Schedule screenings, set tier ticket prices, and monitor occupancy</p>
    </div>
    <button class="btn btn-danger rounded-pill px-4" style="background: linear-gradient(135deg, #ff0055 0%, #7928ca 100%); border: none;" data-bs-toggle="modal" data-bs-target="#showModal">
        <i class="bi bi-plus-circle me-1"></i>Schedule New Show
    </button>
</div>

<div class="admin-card">
    <div class="table-responsive">
        <table class="table admin-table">
            <thead>
                <tr>
                    <th>Movie</th>
                    <th>Theatre & Screen</th>
                    <th>Show Date & Time</th>
                    <th>Pricing Tiers</th>
                    <th>Occupancy</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($shows as $sh): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="<?= movie_poster_url($sh['poster_url']) ?>" alt="<?= htmlspecialchars($sh['movie_title']) ?>" style="width: 36px; height: 54px; aspect-ratio: 2 / 3; object-fit: cover; border-radius: 4px;" onerror="this.onerror=null; this.src='<?= asset_url('images/poster-placeholder.svg') ?>';">
                                <div>
                                    <strong class="text-white d-block"><?= htmlspecialchars($sh['movie_title']) ?></strong>
                                    <span class="badge bg-purple bg-opacity-25 text-purple-light fs-xs"><?= htmlspecialchars($sh['screen_type']) ?></span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <strong class="text-white"><?= htmlspecialchars($sh['theatre_name']) ?></strong>
                            <small class="text-secondary d-block"><?= htmlspecialchars($sh['screen_name']) ?></small>
                        </td>
                        <td>
                            <span class="text-light fw-bold"><?= format_date($sh['show_date'], 'D, d M Y') ?></span>
                            <div class="text-danger fw-bold fs-6"><?= format_time($sh['show_time']) ?></div>
                        </td>
                        <td>
                            <span class="text-secondary small">Reg: <strong class="text-white">₹<?= number_format($sh['regular_price']) ?></strong></span> &bull; 
                            <span class="text-secondary small">Prem: <strong class="text-white">₹<?= number_format($sh['premium_price']) ?></strong></span> &bull; 
                            <span class="text-secondary small">Rec: <strong class="text-white">₹<?= number_format($sh['recliner_price']) ?></strong></span>
                        </td>
                        <td>
                            <span class="badge bg-danger bg-opacity-20 text-danger border border-danger border-opacity-30">
                                <?= (int)$sh['booked_count'] ?> / 60 Booked
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="<?= base_url('seats.php?show_id=' . $sh['id']) ?>" target="_blank" class="btn btn-sm btn-outline-info rounded-circle me-1" title="View Seat Layout">
                                <i class="bi bi-grid-3x3"></i>
                            </a>

                            <form action="" method="POST" class="d-inline" onsubmit="return confirm('Delete this scheduled show?');">
                                <?= csrf_input() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $sh['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Show Modal -->
<div class="modal fade" id="showModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark border border-secondary border-opacity-50">
            <div class="modal-header border-secondary border-opacity-25">
                <h5 class="modal-title text-white">Schedule New Screening</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                <?= csrf_input() ?>
                <input type="hidden" name="action" value="create">

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-white small fw-bold">Select Movie</label>
                            <select name="movie_id" class="form-select cinematic-input" required>
                                <?php foreach ($movies as $mv): ?>
                                    <option value="<?= $mv['id'] ?>"><?= htmlspecialchars($mv['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-white small fw-bold">Select Theatre</label>
                            <select name="theatre_id" id="theatreSelect" class="form-select cinematic-input" required onchange="filterScreens()">
                                <?php foreach ($theatres as $th): ?>
                                    <option value="<?= $th['id'] ?>"><?= htmlspecialchars($th['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-white small fw-bold">Select Screen / Auditorium</label>
                            <select name="screen_id" id="screenSelect" class="form-select cinematic-input" required>
                                <?php foreach ($screens as $sc): ?>
                                    <option value="<?= $sc['id'] ?>" data-theatre="<?= $sc['theatre_id'] ?>">
                                        <?= htmlspecialchars($sc['screen_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label text-white small fw-bold">Show Date</label>
                            <input type="date" name="show_date" class="form-control cinematic-input" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label text-white small fw-bold">Show Time</label>
                            <input type="time" name="show_time" class="form-control cinematic-input" value="18:00" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-white small fw-bold">Regular Seat Price (₹)</label>
                            <input type="number" step="10" name="regular_price" class="form-control cinematic-input" value="200" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-white small fw-bold">Premium Seat Price (₹)</label>
                            <input type="number" step="10" name="premium_price" class="form-control cinematic-input" value="300" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-white small fw-bold">Recliner Seat Price (₹)</label>
                            <input type="number" step="10" name="recliner_price" class="form-control cinematic-input" value="480" required>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-cinematic-gradient rounded-pill px-4">Publish Show</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function filterScreens() {
    const theatreId = document.getElementById('theatreSelect').value;
    const screenSelect = document.getElementById('screenSelect');
    const options = screenSelect.querySelectorAll('option');

    let firstMatch = null;
    options.forEach(opt => {
        if (opt.getAttribute('data-theatre') === theatreId) {
            opt.style.display = 'block';
            if (!firstMatch) firstMatch = opt;
        } else {
            opt.style.display = 'none';
        }
    });

    if (firstMatch) {
        screenSelect.value = firstMatch.value;
    }
}
filterScreens();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

