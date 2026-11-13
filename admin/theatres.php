<?php
/**
 * CINEMA BOOKING - Admin Theatres Management
 */

require_once __DIR__ . '/includes/auth-check.php';

$adminTitle = 'Manage Theatres';
$pdo = getDBConnection();

$action = $_POST['action'] ?? '';

// Handle Delete Theatre
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete') {
    $token = $_POST['csrf_token'] ?? '';
    if (verify_csrf($token)) {
        $theatreId = (int)($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM theatres WHERE id = ?");
        $stmt->execute([$theatreId]);
        set_flash('success', 'Theatre deleted successfully.');
    }
    redirect(base_url('admin/theatres.php'));
}

// Handle Add / Edit Theatre
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['create', 'update'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        set_flash('danger', 'Security validation failed.');
        redirect(base_url('admin/theatres.php'));
    }

    $id = (int)($_POST['id'] ?? 0);
    $cityId = (int)($_POST['city_id'] ?? 1);
    $name = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['contact_phone'] ?? '');
    $facilities = trim($_POST['facilities'] ?? 'IMAX Laser, Dolby Atmos, Luxury Recliners');

    if (empty($name) || empty($address)) {
        set_flash('danger', 'Theatre name and address are required.');
        redirect(base_url('admin/theatres.php'));
    }

    if ($action === 'create') {
        $stmt = $pdo->prepare("INSERT INTO theatres (city_id, name, address, contact_phone, facilities, is_active) VALUES (?, ?, ?, ?, ?, 1)");
        $stmt->execute([$cityId, $name, $address, $phone, $facilities]);
        set_flash('success', "Theatre '{$name}' created.");
    } elseif ($action === 'update' && $id > 0) {
        $stmt = $pdo->prepare("UPDATE theatres SET city_id = ?, name = ?, address = ?, contact_phone = ?, facilities = ? WHERE id = ?");
        $stmt->execute([$cityId, $name, $address, $phone, $facilities, $id]);
        set_flash('success', "Theatre '{$name}' updated.");
    }

    redirect(base_url('admin/theatres.php'));
}

// Fetch theatres
$theatres = $pdo->query("
    SELECT t.*, c.name AS city_name, 
           (SELECT COUNT(*) FROM screens sc WHERE sc.theatre_id = t.id) AS screen_count
    FROM theatres t
    JOIN cities c ON t.city_id = c.id
    ORDER BY c.name ASC, t.name ASC
")->fetchAll();

$allCities = get_all_cities();

include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="text-white fw-bold mb-1">Cinema Theatres</h2>
        <p class="text-secondary small mb-0">Configure multiplex locations, addresses, sound systems and facilities</p>
    </div>
    <button class="btn btn-danger rounded-pill px-4" style="background: linear-gradient(135deg, #ff0055 0%, #7928ca 100%); border: none;" data-bs-toggle="modal" data-bs-target="#theatreModal" onclick="openCreateModal()">
        <i class="bi bi-plus-circle me-1"></i>Add Theatre
    </button>
</div>

<div class="admin-card">
    <div class="table-responsive">
        <table class="table admin-table">
            <thead>
                <tr>
                    <th>Theatre Name</th>
                    <th>City & State</th>
                    <th>Address</th>
                    <th>Screens</th>
                    <th>Contact</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($theatres as $t): ?>
                    <tr>
                        <td>
                            <h6 class="text-white fw-bold mb-1"><i class="bi bi-building text-danger me-2"></i><?= htmlspecialchars($t['name']) ?></h6>
                            <small class="text-secondary"><?= htmlspecialchars($t['facilities']) ?></small>
                        </td>
                        <td>
                            <span class="badge bg-purple bg-opacity-25 text-purple-light"><?= htmlspecialchars($t['city_name']) ?></span>
                        </td>
                        <td>
                            <span class="text-light small d-inline-block text-truncate" style="max-width: 250px;"><?= htmlspecialchars($t['address']) ?></span>
                        </td>
                        <td>
                            <span class="badge bg-secondary"><?= (int)$t['screen_count'] ?> Screens</span>
                        </td>
                        <td>
                            <span class="text-secondary small"><?= htmlspecialchars($t['contact_phone'] ?? '-') ?></span>
                        </td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-info rounded-circle me-1" onclick='openEditModal(<?= json_encode($t) ?>)' title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>

                            <form action="" method="POST" class="d-inline" onsubmit="return confirm('Delete this theatre and all its screens/shows?');">
                                <?= csrf_input() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $t['id'] ?>">
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

<!-- Add / Edit Theatre Modal -->
<div class="modal fade" id="theatreModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary border-opacity-50">
            <div class="modal-header border-secondary border-opacity-25">
                <h5 class="modal-title text-white" id="theatreModalTitle">Add Theatre</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                <?= csrf_input() ?>
                <input type="hidden" name="action" id="theatreFormAction" value="create">
                <input type="hidden" name="id" id="theatreId" value="">

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-white small fw-bold">Select City</label>
                        <select name="city_id" id="tCity" class="form-select cinematic-input" required>
                            <?php foreach ($allCities as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white small fw-bold">Theatre Name</label>
                        <input type="text" name="name" id="tName" class="form-control cinematic-input" placeholder="e.g. CineLux IMAX, Phoenix Mall" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white small fw-bold">Full Address</label>
                        <textarea name="address" id="tAddress" class="form-control cinematic-input" rows="2" placeholder="Full street address and landmark" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white small fw-bold">Contact Phone</label>
                        <input type="text" name="contact_phone" id="tPhone" class="form-control cinematic-input" placeholder="+91 22 4001 5000">
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white small fw-bold">Facilities / Amenities</label>
                        <input type="text" name="facilities" id="tFacilities" class="form-control cinematic-input" value="IMAX Laser, Dolby Atmos 7.1, Luxury Recliners, Food Court, Valet Parking">
                    </div>
                </div>

                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-cinematic-gradient rounded-pill px-4">Save Theatre</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('theatreModalTitle').textContent = 'Add Theatre';
    document.getElementById('theatreFormAction').value = 'create';
    document.getElementById('theatreId').value = '';
    document.getElementById('tName').value = '';
    document.getElementById('tAddress').value = '';
    document.getElementById('tPhone').value = '';
}

function openEditModal(t) {
    const modal = new bootstrap.Modal(document.getElementById('theatreModal'));
    document.getElementById('theatreModalTitle').textContent = 'Edit Theatre';
    document.getElementById('theatreFormAction').value = 'update';
    document.getElementById('theatreId').value = t.id;
    document.getElementById('tCity').value = t.city_id;
    document.getElementById('tName').value = t.name;
    document.getElementById('tAddress').value = t.address;
    document.getElementById('tPhone').value = t.contact_phone || '';
    document.getElementById('tFacilities').value = t.facilities || '';
    modal.show();
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

