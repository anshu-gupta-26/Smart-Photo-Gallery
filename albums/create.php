<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

$errors = [];
$user_id = $_SESSION['user_id'];

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
        $errors[] = "Album ka title required hai.";
    } elseif (strlen($title) < 2) {
        $errors[] = "Title kam se kam 2 characters ka hona chahiye.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO albums (user_id, title, description, status) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $title, $description, $status]);
        
        setFlash('success', "Album '$title' successfully ban gaya!");
        redirect(BASE_URL . '/albums/index.php');
        } // CSRF else closing
    }
}

$pageTitle = "Create Album";
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="page-header">
  <div class="container">
    <h2 class="fw-bold mb-1">
  <i class="bi bi-folder-plus me-2"></i>Create New Album
</h2>
<p class="mb-0 opacity-75">Create an album to organize your photos</p>
  </div>
</div>

<div class="container pb-5" style="max-width:600px">

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <?php foreach ($errors as $e): ?>
        <div><i class="bi bi-exclamation-circle me-1"></i><?= $e ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="card p-4">
    <form method="POST">
  <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

      <div class="mb-3">
        <label class="form-label fw-medium">
          Album Title <span class="text-danger">*</span>
        </label>
        <input type="text" name="title" class="form-control form-control-lg"
       placeholder="e.g. Family Trip, College Photos..."
       value="<?= sanitize($_POST['title'] ?? '') ?>" required>
      </div>

      <div class="mb-3">
        <label class="form-label fw-medium">
          Description <span class="text-muted small">(optional)</span>
        </label>
        <textarea name="description" class="form-control" rows="3"
          placeholder="Write something about this album..."
        ><?= sanitize($_POST['description'] ?? '') ?></textarea>
      </div>

      <div class="mb-4">
        <label class="form-label fw-medium">Album Privacy</label>
        <div class="d-flex gap-3">
          <div class="form-check">
            <input class="form-check-input" type="radio" 
                   name="status" value="public" id="public" checked>
            <label class="form-check-label" for="public">
              <i class="bi bi-globe me-1 text-success"></i>Public
            </label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" 
                   name="status" value="private" id="private">
            <label class="form-check-label" for="private">
              <i class="bi bi-lock me-1 text-danger"></i>Private
            </label>
          </div>
        </div>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-success px-4 py-2 fw-semibold">
  <i class="bi bi-folder-plus me-2"></i>Create Album
</button>
        <a href="<?= BASE_URL ?>/dashboard.php" 
           class="btn btn-outline-secondary px-4">Cancel</a>
      </div>

    </form>
  </div>

</div>

<?php require_once '../includes/footer.php'; ?>