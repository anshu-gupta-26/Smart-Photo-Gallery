<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

$user_id  = $_SESSION['user_id'];
$photo_id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM photos WHERE id = ? AND user_id = ?");
$stmt->execute([$photo_id, $user_id]);
$photo = $stmt->fetch();

if (!$photo) {
    setFlash('error', 'Photo nahi mili!');
    redirect(BASE_URL . '/dashboard.php');
}

// View count badhao
$pdo->prepare("UPDATE photos SET views = views + 1 WHERE id = ?")
    ->execute([$photo_id]);

$pageTitle = sanitize($photo['title'] ?: 'Photo');
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">

      <!-- Photo -->
      <div class="card mb-4 p-2">
        <img src="<?= BASE_URL ?>/uploads/photos/<?= $photo['filename'] ?>"
             class="img-fluid rounded"
             style="width:100%;max-height:500px;object-fit:contain;"
             alt="<?= sanitize($photo['title']) ?>">
      </div>

      <!-- Details -->
      <div class="card p-4">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div>
            <h4 class="fw-bold mb-1"><?= sanitize($photo['title'] ?: 'Untitled') ?></h4>
            <p class="text-muted small mb-0">
              <i class="bi bi-calendar3 me-1"></i>
              <?= date('d M Y', strtotime($photo['created_at'])) ?>
              &nbsp;•&nbsp;
              <i class="bi bi-eye me-1"></i><?= $photo['views'] ?> 
              &nbsp;•&nbsp;
              <i class="bi bi-hdd me-1"></i>
              <?= formatFileSize($photo['file_size']) ?>
            </p>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex gap-2 flex-wrap">
          <a href="<?= BASE_URL ?>/uploads/photos/<?= $photo['filename'] ?>"
             download class="btn btn-success">
            <i class="bi bi-download me-2"></i>
          </a>
          <form method="POST" action="<?= BASE_URL ?>/photos/delete.php"
      class="d-inline confirm-delete-form"
      data-message="Photo permanently delete ho jayegi!">
  <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
  <input type="hidden" name="id" value="<?= $photo['id'] ?>">
  <button type="submit" class="btn btn-danger">
    <i class="bi bi-trash me-2"></i>Delete
  </button>
</form>
          <a href="<?= BASE_URL ?>/dashboard.php"
             class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
<?php require_once '../includes/footer.php'; ?>