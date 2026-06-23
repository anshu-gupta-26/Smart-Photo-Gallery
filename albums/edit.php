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

$errors = [];

// CSRF token generate karo
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = generateToken();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verifyToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid request. Dobara try karo.";
    } else {
    $title       = sanitize($_POST['title'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $status      = $_POST['status'] ?? 'public';

    if (empty($title)) {
        $errors[] = "Title required hai.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE albums SET title=?, description=?, status=? WHERE id=? AND user_id=?
        ");
        $stmt->execute([$title, $description, $status, $album_id, $user_id]);
        setFlash('success', 'Album update ho gaya!');
        redirect(BASE_URL . '/albums/index.php');
        } // CSRF else closing
    }
}

$pageTitle = "Edit Album";
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="page-header">
  <div class="container">
    <h2 class="fw-bold mb-1"><i class="bi bi-pencil me-2"></i>Album Edit Karo</h2>
  </div>
</div>

<div class="container pb-5" style="max-width:600px">

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <?php foreach ($errors as $e): ?>
        <div><?= $e ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="card p-4">
    <form method="POST">
  <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

      <div class="mb-3">
        <label class="form-label fw-medium">Album Title *</label>
        <input type="text" name="title" class="form-control form-control-lg"
               value="<?= sanitize($_POST['title'] ?? $album['title']) ?>" required>
      </div>

      <div class="mb-3">
        <label class="form-label fw-medium">Description</label>
        <textarea name="description" class="form-control" rows="3"
                ><?= sanitize($_POST['description'] ?? $album['description']) ?></textarea>
      </div>

      <div class="mb-4">
        <label class="form-label fw-medium">Privacy</label>
        <div class="d-flex gap-3">
          <div class="form-check">
            <input class="form-check-input" type="radio" name="status" 
                   value="public" id="public"
                   <?= ($album['status']==='public') ? 'checked' : '' ?>>
            <label class="form-check-label" for="public">
              <i class="bi bi-globe me-1 text-success"></i>Public
            </label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="status" 
                   value="private" id="private"
                   <?= ($album['status']==='private') ? 'checked' : '' ?>>
            <label class="form-check-label" for="private">
              <i class="bi bi-lock me-1 text-danger"></i>Private
            </label>
          </div>
        </div>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary px-4 fw-semibold">
          <i class="bi bi-save me-2"></i>Save Karo
        </button>
        <a href="<?= BASE_URL ?>/albums/index.php" 
           class="btn btn-outline-secondary px-4">Cancel</a>
      </div>

    </form>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>