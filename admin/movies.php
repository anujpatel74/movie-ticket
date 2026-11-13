<?php
/**
 * CINEMA BOOKING - Admin Movie Management (CRUD)
 */

require_once __DIR__ . '/includes/auth-check.php';

$adminTitle = 'Manage Movies';
$pdo = getDBConnection();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Handle Delete Movie
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete') {
    $token = $_POST['csrf_token'] ?? '';
    if (verify_csrf($token)) {
        $movieId = (int)($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM movies WHERE id = ?");
        $stmt->execute([$movieId]);
        set_flash('success', 'Movie removed from catalogue.');
    }
    redirect(base_url('admin/movies.php'));
}

// Handle Add / Edit Movie
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['create', 'update'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        set_flash('danger', 'Security token invalid.');
        redirect(base_url('admin/movies.php'));
    }

    $id = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $duration = (int)($_POST['duration_mins'] ?? 120);
    $releaseDate = $_POST['release_date'] ?? date('Y-m-d');
    $genre = trim($_POST['genre'] ?? '');
    $language = trim($_POST['language'] ?? 'English');
    $rating = (float)($_POST['rating'] ?? 8.0);
    $certificate = trim($_POST['certificate'] ?? 'UA');
    $posterUrl = trim($_POST['poster_url'] ?? '');
    $backdropUrl = trim($_POST['backdrop_url'] ?? '');
    $trailerUrl = trim($_POST['trailer_url'] ?? '');
    $status = $_POST['status'] ?? 'now_showing';
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;

    // Generate slug
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));

    if (empty($title) || empty($description)) {
        set_flash('danger', 'Title and Description are required.');
        redirect(base_url('admin/movies.php'));
    }

    if ($action === 'create') {
        // Ensure slug is unique
        $checkStmt = $pdo->prepare("SELECT id FROM movies WHERE slug = ?");
        $checkStmt->execute([$slug]);
        if ($checkStmt->fetch()) {
            $slug .= '-' . rand(10, 99);
        }

        $stmt = $pdo->prepare("
            INSERT INTO movies (
                title, slug, description, duration_mins, release_date,
                genre, language, rating, certificate, poster_url,
                backdrop_url, trailer_url, status, is_featured
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $title, $slug, $description, $duration, $releaseDate,
            $genre, $language, $rating, $certificate, $posterUrl,
            $backdropUrl, $trailerUrl, $status, $isFeatured
        ]);
        set_flash('success', "Movie '{$title}' created successfully.");
    } elseif ($action === 'update' && $id > 0) {
        $stmt = $pdo->prepare("
            UPDATE movies SET
                title = ?, description = ?, duration_mins = ?, release_date = ?,
                genre = ?, language = ?, rating = ?, certificate = ?, poster_url = ?,
                backdrop_url = ?, trailer_url = ?, status = ?, is_featured = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $title, $description, $duration, $releaseDate,
            $genre, $language, $rating, $certificate, $posterUrl,
            $backdropUrl, $trailerUrl, $status, $isFeatured, $id
        ]);
        set_flash('success', "Movie '{$title}' updated successfully.");
    }

    redirect(base_url('admin/movies.php'));
}

// Fetch all movies
$movies = $pdo->query("SELECT * FROM movies ORDER BY release_date DESC")->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="text-white fw-bold mb-1">Movie Catalogue</h2>
        <p class="text-secondary small mb-0">Manage now showing titles, coming soon releases, posters, and trailers</p>
    </div>
    <button class="btn btn-danger rounded-pill px-4" style="background: linear-gradient(135deg, #ff0055 0%, #7928ca 100%); border: none;" data-bs-toggle="modal" data-bs-target="#movieModal" onclick="openCreateModal()">
        <i class="bi bi-plus-circle me-1"></i>Add New Movie
    </button>
</div>

<div class="admin-card">
    <div class="table-responsive">
        <table class="table admin-table">
            <thead>
                <tr>
                    <th>Poster</th>
                    <th>Title & Genre</th>
                    <th>Runtime & Cert</th>
                    <th>Rating</th>
                    <th>Status</th>
                    <th>Featured</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($movies as $m): ?>
                    <tr>
                        <td style="width: 70px;">
                            <img src="<?= movie_poster_url($m['poster_url']) ?>" alt="<?= htmlspecialchars($m['title']) ?>" style="width: 48px; height: 72px; aspect-ratio: 2 / 3; object-fit: cover; border-radius: 6px;" onerror="this.onerror=null; this.src='<?= asset_url('images/poster-placeholder.svg') ?>';">
                        </td>
                        <td>
                            <h6 class="text-white fw-bold mb-1"><?= htmlspecialchars($m['title']) ?></h6>
                            <span class="text-secondary small"><?= htmlspecialchars($m['genre']) ?> &bull; <?= htmlspecialchars($m['language']) ?></span>
                        </td>
                        <td>
                            <span class="badge bg-secondary me-1"><?= htmlspecialchars($m['certificate']) ?></span>
                            <span class="text-light small"><?= format_duration((int)$m['duration_mins']) ?></span>
                        </td>
                        <td>
                            <span class="text-warning fw-bold"><i class="bi bi-star-fill me-1"></i><?= htmlspecialchars($m['rating']) ?></span>
                        </td>
                        <td>
                            <?php if ($m['status'] === 'now_showing'): ?>
                                <span class="badge bg-success bg-opacity-25 text-success">Now Showing</span>
                            <?php else: ?>
                                <span class="badge bg-warning bg-opacity-25 text-warning">Coming Soon</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= $m['is_featured'] ? '<i class="bi bi-check-circle-fill text-success fs-5"></i>' : '<span class="text-muted">-</span>' ?>
                        </td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-info rounded-circle me-1" onclick='openEditModal(<?= json_encode($m) ?>)' title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>

                            <form action="" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this movie?');">
                                <?= csrf_input() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $m['id'] ?>">
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

<!-- Add / Edit Movie Modal -->
<div class="modal fade" id="movieModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark border border-secondary border-opacity-50">
            <div class="modal-header border-secondary border-opacity-25">
                <h5 class="modal-title text-white" id="modalTitle">Add Movie</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                <?= csrf_input() ?>
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="id" id="movieId" value="">

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label text-white small fw-bold">Movie Title</label>
                            <input type="text" name="title" id="mTitle" class="form-control cinematic-input" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-white small fw-bold">Rating (e.g. 8.5)</label>
                            <input type="number" step="0.1" name="rating" id="mRating" class="form-control cinematic-input" value="8.5">
                        </div>

                        <div class="col-12">
                            <label class="form-label text-white small fw-bold">Description / Synopsis</label>
                            <textarea name="description" id="mDesc" class="form-control cinematic-input" rows="3" required></textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-white small fw-bold">Duration (Minutes)</label>
                            <input type="number" name="duration_mins" id="mDuration" class="form-control cinematic-input" value="145" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-white small fw-bold">Release Date</label>
                            <input type="date" name="release_date" id="mRelease" class="form-control cinematic-input" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-white small fw-bold">Certificate</label>
                            <select name="certificate" id="mCert" class="form-select cinematic-input">
                                <option value="U">U (Universal)</option>
                                <option value="UA" selected>UA (Parental Guidance)</option>
                                <option value="A">A (Adults Only)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-white small fw-bold">Genre</label>
                            <input type="text" name="genre" id="mGenre" class="form-control cinematic-input" placeholder="Action, Sci-Fi, Adventure" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-white small fw-bold">Language</label>
                            <input type="text" name="language" id="mLang" class="form-control cinematic-input" value="English" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-white small fw-bold">Poster Image Path / URL</label>
                            <input type="text" name="poster_url" id="mPoster" class="form-control cinematic-input" placeholder="assets/images/movie.jpg or https://..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-white small fw-bold">Backdrop Image Path / URL</label>
                            <input type="text" name="backdrop_url" id="mBackdrop" class="form-control cinematic-input" placeholder="assets/images/oppenheimer_bg.jpg or https://..." required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-white small fw-bold">YouTube Trailer URL</label>
                            <input type="url" name="trailer_url" id="mTrailer" class="form-control cinematic-input" placeholder="https://www.youtube.com/watch?v=...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-white small fw-bold">Status</label>
                            <select name="status" id="mStatus" class="form-select cinematic-input">
                                <option value="now_showing">Now Showing</option>
                                <option value="coming_soon">Coming Soon</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-center pt-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_featured" id="mFeatured" value="1">
                                <label class="form-check-label text-white small" for="mFeatured">
                                    Hero Banner (Featured)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-cinematic-gradient rounded-pill px-4" id="submitBtn">Save Movie</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Add New Movie';
    document.getElementById('formAction').value = 'create';
    document.getElementById('movieId').value = '';
    document.getElementById('mTitle').value = '';
    document.getElementById('mDesc').value = '';
    document.getElementById('mGenre').value = '';
    document.getElementById('mPoster').value = 'assets/images/poster-placeholder.svg';
    document.getElementById('mBackdrop').value = 'assets/images/oppenheimer_bg.jpg';
    document.getElementById('mTrailer').value = '';
    document.getElementById('mFeatured').checked = false;
}

function openEditModal(movie) {
    const modal = new bootstrap.Modal(document.getElementById('movieModal'));
    document.getElementById('modalTitle').textContent = 'Edit Movie: ' + movie.title;
    document.getElementById('formAction').value = 'update';
    document.getElementById('movieId').value = movie.id;
    document.getElementById('mTitle').value = movie.title;
    document.getElementById('mDesc').value = movie.description;
    document.getElementById('mDuration').value = movie.duration_mins;
    document.getElementById('mRelease').value = movie.release_date;
    document.getElementById('mCert').value = movie.certificate;
    document.getElementById('mRating').value = movie.rating;
    document.getElementById('mGenre').value = movie.genre;
    document.getElementById('mLang').value = movie.language;
    document.getElementById('mPoster').value = movie.poster_url;
    document.getElementById('mBackdrop').value = movie.backdrop_url;
    document.getElementById('mTrailer').value = movie.trailer_url || '';
    document.getElementById('mStatus').value = movie.status;
    document.getElementById('mFeatured').checked = (parseInt(movie.is_featured) === 1);
    modal.show();
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

