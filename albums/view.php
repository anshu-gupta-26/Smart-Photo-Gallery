<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

$user_id  = $_SESSION['user_id'];
$album_id = (int)($_GET['id'] ?? 0);

// Album fetch karo
$stmt = $pdo->prepare("SELECT * FROM albums WHERE id = ? AND user_id = ?");
$stmt->execute([$album_id, $user_id]);
$album = $stmt->fetch();

if (!$album) {
    setFlash('error', 'Album nahi mila!');
    redirect(BASE_URL . '/albums/index.php');
}

// Is album ki photos fetch karo
$stmt = $pdo->prepare("SELECT * FROM photos WHERE album_id = ? ORDER BY created_at DESC");
$stmt->execute([$album_id]);
$photos = $stmt->fetchAll();

$pageTitle = sanitize($album['title']);
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="page-header">
  <div class="container d-flex justify-content-between align-items-center">
    <div>
      <h2 class="fw-bold mb-1">
        <i class="bi bi-collection me-2"></i><?= sanitize($album['title']) ?>
      </h2>
      <p class="mb-0 opacity-75">
        <?= count($photos) ?> photos &nbsp;•&nbsp;
        <?= $album['status'] === 'private' ? '🔒 Private' : '🌍 Public' ?>
      </p>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= BASE_URL ?>/photos/upload.php" class="btn btn-light">
        <i class="bi bi-cloud-upload me-1"></i>Upload
      </a>
      <a href="<?= BASE_URL ?>/albums/index.php" class="btn btn-outline-light">
        <i class="bi bi-arrow-left me-1"></i>Back
      </a>
    </div>
  </div>
</div>

<div class="container pb-5">
  <?= getFlash() ?>

  <?php if (empty($photos)): ?>
    <div class="card text-center py-5">
      <div class="card-body">
        <i class="bi bi-images text-muted" style="font-size:4rem"></i>
        <h5 class="mt-3 text-muted">Is album mein koi photo nahi hai</h5>
        <a href="<?= BASE_URL ?>/photos/upload.php" class="btn btn-primary mt-2">
          <i class="bi bi-cloud-upload me-2"></i>Photo Upload Karo
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
            <a href="<?= BASE_URL ?>/photos/view.php?id=<?= $photo['id'] ?>"
               class="text-white"><i class="bi bi-eye"></i></a>
            <form method="POST" action="<?= BASE_URL ?>/photos/delete.php"
      class="d-inline confirm-delete-form"
      data-message="Photo delete karna chahte ho?">
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
</div>

<?php require_once '../includes/footer.php'; ?>