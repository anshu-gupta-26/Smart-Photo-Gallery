<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';
requireAdmin();

// CSRF token generate karo
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = generateToken();
}

// Photo delete — POST only
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_photo'])) {
    if (!verifyToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid request!');
        redirect(BASE_URL . '/admin/photos.php');
    }
    $pid = (int)($_POST['delete_photo'] ?? 0);
    if ($pid > 0) {
        $stmt = $pdo->prepare("SELECT filename FROM photos WHERE id = ?");
        $stmt->execute([$pid]);
        $p = $stmt->fetch();
        if ($p) {
            $file = UPLOAD_PATH . $p['filename'];
            if (file_exists($file)) unlink($file);
            $pdo->prepare("DELETE FROM photos WHERE id = ?")
                ->execute([$pid]);
            setFlash('success', 'Photo delete ho gayi!');
        }
    }
    redirect(BASE_URL . '/admin/photos.php');
}

// All photos
$photos = $pdo->query("
    SELECT p.*, u.username 
    FROM photos p
    JOIN users u ON p.user_id = u.id
    ORDER BY p.created_at DESC
")->fetchAll();

$pageTitle = "Manage Photos";
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="page-header">
  <div class="container">
    <h2 class="fw-bold mb-1">
      <i class="bi bi-images me-2"></i>Manage Photos
    </h2>
    <p class="mb-0 opacity-75">Total <?= count($photos) ?> photos</p>
  </div>
</div>

<div class="container pb-5">
  <?= getFlash() ?>

  <div class="photo-grid">
    <?php foreach ($photos as $photo): ?>
      <div class="photo-card">
        <img src="<?= BASE_URL ?>/uploads/photos/<?= $photo['filename'] ?>"
     alt="<?= sanitize($photo['title']) ?>"
     loading="lazy">
        <div class="overlay">
          <div style="text-align:center">
            <small class="text-white d-block mb-2">
              @<?= sanitize($photo['username']) ?>
            </small>
            <a href="<?= BASE_URL ?>/photos/view.php?id=<?= $photo['id'] ?>"
               class="text-white me-2">
              <i class="bi bi-eye fs-5"></i>
            </a>
            <form method="POST" class="d-inline confirm-delete-form"
                  data-message="Photo permanently delete ho jayegi!">
              <input type="hidden" name="csrf_token"
                     value="<?= $_SESSION['csrf_token'] ?>">
              <input type="hidden" name="delete_photo"
                     value="<?= $photo['id'] ?>">
              <button type="submit"
                      class="text-white border-0 bg-transparent">
                <i class="bi bi-trash fs-5"></i>
              </button>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>