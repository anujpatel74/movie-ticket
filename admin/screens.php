<?php
/**
 * CINEMA BOOKING - Admin Screens & Seats Management
 */

require_once __DIR__ . '/includes/auth-check.php';

$adminTitle = 'Manage Screens & Seats';
$pdo = getDBConnection();

$action = $_POST['action'] ?? '';

// Handle Delete Screen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete') {
    $token = $_POST['csrf_token'] ?? '';
    if (verify_csrf($token)) {
        $screenId = (int)($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM screens WHERE id = ?");
        $stmt->execute([$screenId]);
        set_flash('success', 'Screen and its seat layout deleted.');
    }
    redirect(base_url('admin/screens.php'));
}

// Handle Add Screen & Generate Default 60 Seats
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        set_flash('danger', 'Security validation failed.');
        redirect(base_url('admin/screens.php'));
    }

    $theatreId = (int)($_POST['theatre_id'] ?? 0);
    $screenName = trim($_POST['screen_name'] ?? '');
    $screenType = trim($_POST['screen_type'] ?? 'IMAX Laser 3D');

    if ($theatreId <= 0 || empty($screenName)) {
        set_flash('danger', 'Please provide a valid theatre and screen name.');
        redirect(base_url('admin/screens.php'));
    }

    $stmt = $pdo->prepare("INSERT INTO screens (theatre_id, screen_name, screen_type, total_seats) VALUES (?, ?, ?, 60)");
    $stmt->execute([$theatreId, $screenName, $screenType]);
    $newScreenId = (int)$pdo->lastInsertId();

    // Auto-generate standard 60-seat matrix:
    // Row A (1-10) Recliner, Rows B-D (1-10) Premium, Rows E-F (1-10) Regular
    $seatInsert = $pdo->prepare("INSERT INTO seats (screen_id, seat_row, seat_number, seat_code, seat_type, is_active) VALUES (?, ?, ?, ?, ?, 1)");
    $rows = [
        'A' => 'recliner',
        'B' => 'premium',
        'C' => 'premium',
        'D' => 'premium',
        'E' => 'regular',
        'F' => 'regular'
    ];

    foreach ($rows as $r => $type) {
        for ($num = 1; $num <= 10; $num++) {
            $code = $r . $num;
            $seatInsert->execute([$newScreenId, $r, $num, $code, $type]);
        }
    }

    set_flash('success', "Screen '{$screenName}' created with 60 configured interactive seats!");
    redirect(base_url('admin/screens.php'));
}

// Fetch all screens with theatre name
$screens = $pdo->query("
    SELECT sc.*, t.name AS theatre_name, c.name AS city_name,
           (SELECT COUNT(*) FROM seats st WHERE st.screen_id = sc.id) AS actual_seats
    FROM screens sc
    JOIN theatres t ON sc.theatre_id = t.id
    JOIN cities c ON t.city_id = c.id
    ORDER BY t.name ASC, sc.screen_name ASC
")->fetchAll();

$theatres = $pdo->query("SELECT id, name FROM theatres ORDER BY name ASC")->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="text-white fw-bold mb-1">Screens & Seat Layouts</h2>
        <p class="text-secondary small mb-0">Manage cinema auditoriums and tier-based seating configurations</p>
    </div>
    <button class="btn btn-danger rounded-pill px-4" style="background: linear-gradient(135deg, #ff0055 0%, #7928ca 100%); border: none;" data-bs-toggle="modal" data-bs-target="#screenModal">
        <i class="bi bi-plus-circle me-1"></i>Add New Screen
    </button>
</div>

<div class="admin-card">
    <div class="table-responsive">
        <table class="table admin-table">
            <thead>
                <tr>
                    <th>Theatre</th>
                    <th>Screen Name</th>
                    <th>Projection / Sound Type</th>
                    <th>Configured Seats</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($screens as $sc): ?>
                    <tr>
                        <td>
                            <strong class="text-white"><?= htmlspecialchars($sc['theatre_name']) ?></strong>
                            <small class="text-secondary d-block"><?= htmlspecialchars($sc['city_name']) ?></small>
                        </td>
                        <td>
                            <span class="badge bg-purple bg-opacity-25 text-purple-light fs-6">
                                <?= htmlspecialchars($sc['screen_name']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="text-light"><?= htmlspecialchars($sc['screen_type']) ?></span>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-30">
                                <i class="bi bi-grid-3x3 me-1"></i><?= (int)$sc['actual_seats'] ?> Seats (Recliner, Premium, Regular)
                            </span>
                        </td>
                        <td class="text-end">
                            <form action="" method="POST" class="d-inline" onsubmit="return confirm('Delete this screen and all associated seats & showtimes?');">
                                <?= csrf_input() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $sc['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" title="Delete Screen">
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

<!-- Add Screen Modal -->
<div class="modal fade" id="screenModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary border-opacity-50">
            <div class="modal-header border-secondary border-opacity-25">
                <h5 class="modal-title text-white">Add Auditorium Screen</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                <?= csrf_input() ?>
                <input type="hidden" name="action" value="create">

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-white small fw-bold">Select Theatre</label>
                        <select name="theatre_id" class="form-select cinematic-input" required>
                            <?php foreach ($theatres as $th): ?>
                                <option value="<?= $th['id'] ?>"><?= htmlspecialchars($th['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white small fw-bold">Screen / Auditorium Name</label>
                        <input type="text" name="screen_name" class="form-control cinematic-input" placeholder="e.g. Screen 1 - IMAX Laser" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white small fw-bold">Projection / Screen Type</label>
                        <select name="screen_type" class="form-select cinematic-input">
                            <option value="IMAX Laser 3D">IMAX Laser 3D</option>
                            <option value="Dolby Atmos 2D">Dolby Atmos 2D</option>
                            <option value="4DX Motion 3D">4DX Motion 3D</option>
                            <option value="Gold Class Recliner">Gold Class Recliner</option>
                            <option value="Standard 2D">Standard 2D</option>
                        </select>
                    </div>

                    <div class="alert alert-dark border-secondary text-secondary small mb-0">
                        <i class="bi bi-info-circle text-info me-1"></i> 
                        Creating this screen will automatically generate a standardized 60-seat matrix with Recliner (Row A), Premium (Rows B-D), and Regular (Rows E-F).
                    </div>
                </div>

                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-cinematic-gradient rounded-pill px-4">Generate Screen & Seats</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

