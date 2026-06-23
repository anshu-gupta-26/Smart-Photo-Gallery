<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

$user_id = $_SESSION['user_id'];

$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = PHOTOS_PER_PAGE;
$offset  = ($page - 1) * $perPage;

$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM photos WHERE user_id = ?");
$totalStmt->execute([$user_id]);
$totalPhotos = (int)$totalStmt->fetchColumn();
$totalPages  = ceil($totalPhotos / $perPage);

$stmt = $pdo->prepare("SELECT * FROM photos WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $user_id, PDO::PARAM_INT);
$stmt->bindValue(2, $perPage, PDO::PARAM_INT);
$stmt->bindValue(3, $offset, PDO::PARAM_INT);
$stmt->execute();
$photos = $stmt->fetchAll();

$pageTitle = "My Photos";
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="page-header">
  <div class="container d-flex justify-content-between align-items-center">
    <div>
      <h2 class="fw-bold mb-1">
        <i class="bi bi-images me-2"></i>My Photos
      </h2>
      <p class="mb-0 opacity-75">Total <?= $totalPhotos ?> photos • Page <?= $page ?> of <?= max(1,$totalPages) ?></p>
    </div>
    <a href="<?= BASE_URL ?>/photos/upload.php" class="btn btn-light fw-semibold">
      <i class="bi bi-cloud-upload me-2"></i>Upload New
    </a>
  </div>
</div>

<div class="container pb-5">
  <?= getFlash() ?>

  <?php if (empty($photos)): ?>
    <div class="card text-center py-5">
      <div class="card-body">
        <i class="bi bi-images text-muted" style="font-size:4rem"></i>
        <h5 class="mt-3 text-muted">Koi photo nahi hai abhi</h5>
        <a href="<?= BASE_URL ?>/photos/upload.php" class="btn btn-primary mt-2">
          <i class="bi bi-cloud-upload me-2"></i>Pehli Photo Upload Karo
        </a>
      </div>
    </div>

  <?php else: ?>
    <div class="photo-grid">
      <?php foreach ($photos as $photo): ?>
        <div class="photo-card">
          <img src="<?= BASE_URL ?>/uploads/photos/<?= $photo['filename'] ?>"
               alt="<?= sanitize($photo['title']) ?>"
               loading="lazy">
          <div class="overlay">
            <a href="#" onclick="openLightbox('<?= BASE_URL ?>/uploads/photos/<?= $photo['filename'] ?>', '<?= sanitize($photo['title']) ?>')"
               title="View">
              <i class="bi bi-eye fs-5 text-white"></i>
            </a>
            <a href="<?= BASE_URL ?>/photos/view.php?id=<?= $photo['id'] ?>"
               title="Details" class="ms-3">
              <i class="bi bi-info-circle fs-5 text-white"></i>
            </a>
            <form method="POST" action="<?= BASE_URL ?>/photos/delete.php"
                  class="d-inline confirm-delete-form ms-3"
                  data-message="Photo delete karna chahte ho?">
              <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
              <input type="hidden" name="id" value="<?= $photo['id'] ?>">
              <button type="submit" class="text-white border-0 bg-transparent">
                <i class="bi bi-trash fs-5"></i>
              </button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
      <nav class="mt-5 d-flex justify-content-center">
        <ul class="pagination">
          <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $page - 1 ?>">
              <i class="bi bi-chevron-left"></i>
            </a>
          </li>
          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
              <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>
          <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $page + 1 ?>">
              <i class="bi bi-chevron-right"></i>
            </a>
          </li>
        </ul>
      </nav>
    <?php endif; ?>

  <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>