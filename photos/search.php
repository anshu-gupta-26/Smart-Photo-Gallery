<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

$user_id = $_SESSION['user_id'];
$query   = sanitize($_GET['q'] ?? '');
$photos  = [];

if (!empty($query)) {
    $stmt = $pdo->prepare("
        SELECT p.*, a.title as album_name 
        FROM photos p
        LEFT JOIN albums a ON p.album_id = a.id
        WHERE p.user_id = ? AND p.title LIKE ?
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$user_id, "%$query%"]);
    $photos = $stmt->fetchAll();
}

$pageTitle = "Search Photos";
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="page-header">
  <div class="container">
    <h2 class="fw-bold mb-1">
      <i class="bi bi-search me-2"></i>Search Photos
    </h2>
    <p class="mb-0 opacity-75">Find your photos by title</p>
  </div>
</div>

<div class="container pb-5">

  <!-- Search Box -->
  <div class="card p-4 mb-4" style="max-width:600px;margin:0 auto">
    <form method="GET">
      <div class="input-group input-group-lg">
        <span class="input-group-text">
          <i class="bi bi-search"></i>
        </span>
        <input type="text" name="q" class="form-control"
               placeholder="Search by photo title..."
               value="<?= sanitize($query) ?>" autofocus>
        <button type="submit" class="btn btn-primary px-4">Search</button>
      </div>
    </form>
  </div>

  <!-- Results -->
  <?php if (!empty($query)): ?>
    <div class="mb-3 text-center">
      <?php if (count($photos) > 0): ?>
        <span class="badge bg-success fs-6">
          <?= count($photos) ?> result(s) found for "<?= sanitize($query) ?>"
        </span>
      <?php else: ?>
        <span class="badge bg-danger fs-6">
          No photos found for "<?= sanitize($query) ?>"
        </span>
      <?php endif; ?>
    </div>

    <?php if (!empty($photos)): ?>
      <div class="photo-grid">
        <?php foreach ($photos as $photo): ?>
          <div class="photo-card">
            <img src="<?= BASE_URL ?>/uploads/photos/<?= $photo['filename'] ?>"
                 alt="<?= sanitize($photo['title']) ?>"
                 loading="lazy">
            <div class="overlay">
              <a href="<?= BASE_URL ?>/photos/view.php?id=<?= $photo['id'] ?>"
                 class="text-white" title="View">
                <i class="bi bi-eye"></i>
              </a>
              <form method="POST" action="<?= BASE_URL ?>/photos/delete.php"
                    class="d-inline confirm-delete-form"
                    data-message="Delete this photo?">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="id" value="<?= $photo['id'] ?>">
                <button type="submit" class="text-white border-0 bg-transparent">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  <?php else: ?>
    <!-- No search yet -->
    <div class="text-center py-5">
      <i class="bi bi-search text-muted" style="font-size:5rem"></i>
      <h5 class="mt-3 text-muted">Type something above to search your photos</h5>
    </div>
  <?php endif; ?>

</div>

<?php require_once '../includes/footer.php'; ?>