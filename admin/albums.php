<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';
requireAdmin();

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = generateToken();
}

// Album delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_album'])) {
    if (!verifyToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid request!');
        redirect(BASE_URL . '/admin/albums.php');
    }
    $album_id = (int)($_POST['delete_album'] ?? 0);
    if ($album_id > 0) {
        // Photos ki files delete karo
        $stmt = $pdo->prepare("SELECT filename FROM photos WHERE album_id = ?");
        $stmt->execute([$album_id]);
        $photos = $stmt->fetchAll();
        foreach ($photos as $photo) {
            $file = UPLOAD_PATH . $photo['filename'];
            if (file_exists($file)) unlink($file);
        }
        // Photos delete karo
        $pdo->prepare("DELETE FROM photos WHERE album_id = ?")
            ->execute([$album_id]);
        // Album delete karo
        $pdo->prepare("DELETE FROM albums WHERE id = ?")
            ->execute([$album_id]);
        setFlash('success', 'Album delete ho gaya!');
    }
    redirect(BASE_URL . '/admin/albums.php');
}

// All albums fetch
$albums = $pdo->query("
    SELECT a.*, 
           u.username,
           u.full_name,
           COUNT(p.id) as photo_count,
           (SELECT filename FROM photos 
            WHERE album_id = a.id 
            ORDER BY created_at DESC LIMIT 1) as cover_photo
    FROM albums a
    JOIN users u ON a.user_id = u.id
    LEFT JOIN photos p ON p.album_id = a.id
    GROUP BY a.id
    ORDER BY a.created_at DESC
")->fetchAll();

$pageTitle = "Manage Albums";
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="page-header">
  <div class="container">
    <h2 class="fw-bold mb-1">
      <i class="bi bi-collection me-2"></i>Manage Albums
    </h2>
    <p class="mb-0 opacity-75">Total <?= count($albums) ?> albums</p>
  </div>
</div>

<div class="container pb-5">
  <?= getFlash() ?>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>Cover</th>
            <th>Album</th>
            <th>Owner</th>
            <th>Photos</th>
            <th>Status</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($albums as $i => $album): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td>
              <?php if ($album['cover_photo']): ?>
                <img src="<?= BASE_URL ?>/uploads/photos/<?= $album['cover_photo'] ?>"
                     style="width:50px;height:50px;object-fit:cover;border-radius:8px;">
              <?php else: ?>
                <div style="width:50px;height:50px;border-radius:8px;
                            background:linear-gradient(135deg,#4361ee,#764ba2);
                            display:flex;align-items:center;justify-content:center;">
                  <i class="bi bi-images text-white"></i>
                </div>
              <?php endif; ?>
            </td>
            <td>
              <strong><?= sanitize($album['title']) ?></strong><br>
              <small class="text-muted"><?= sanitize($album['description'] ?? '') ?></small>
            </td>
            <td>
              <span class="badge bg-secondary">@<?= sanitize($album['username']) ?></span><br>
              <small class="text-muted"><?= sanitize($album['full_name']) ?></small>
            </td>
            <td>
              <span class="badge bg-primary"><?= $album['photo_count'] ?> photos</span>
            </td>
            <td>
              <?php if ($album['status'] === 'public'): ?>
                <span class="badge bg-success">Public</span>
              <?php else: ?>
                <span class="badge bg-danger">Private</span>
              <?php endif; ?>
            </td>
            <td class="small text-muted">
              <?= date('d M Y', strtotime($album['created_at'])) ?>
            </td>
            <td>
              <div class="d-flex gap-1">
                <a href="<?= BASE_URL ?>/albums/view.php?id=<?= $album['id'] ?>"
                   class="btn btn-sm btn-outline-primary" title="View">
                  <i class="bi bi-eye"></i>
                </a>
                <form method="POST" class="d-inline confirm-delete-form"
                      data-message="Is album ko delete karna chahte ho? Saari photos bhi delete ho jayengi!">
                  <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                  <input type="hidden" name="delete_album" value="<?= $album['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>