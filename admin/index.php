<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';
requireAdmin();

// Stats
$totalUsers  = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalPhotos = $pdo->query("SELECT COUNT(*) FROM photos")->fetchColumn();
$totalAlbums = $pdo->query("SELECT COUNT(*) FROM albums")->fetchColumn();

// Recent users
$recentUsers = $pdo->query("
    SELECT * FROM users ORDER BY created_at DESC LIMIT 5
")->fetchAll();

// Recent photos
$recentPhotos = $pdo->query("
    SELECT p.*, u.username FROM photos p
    JOIN users u ON p.user_id = u.id
    ORDER BY p.created_at DESC LIMIT 6
")->fetchAll();

$pageTitle = "Admin Dashboard";
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="page-header">
  <div class="container">
    <h2 class="fw-bold mb-1">
      <i class="bi bi-shield-lock me-2"></i>Admin Dashboard
    </h2>
    <p class="mb-0 opacity-75">Complete control over your system</p>
  </div>
</div>

<div class="container pb-5">

  <?= getFlash() ?>
  <!-- Admin Navigation -->
<div class="d-flex gap-3 mb-4 flex-wrap">
  <a href="<?= BASE_URL ?>/admin/index.php" 
     class="btn btn-primary">
    <i class="bi bi-speedometer2 me-2"></i>Dashboard
  </a>
  <a href="<?= BASE_URL ?>/admin/users.php" 
     class="btn btn-outline-primary">
    <i class="bi bi-people me-2"></i>Manage Users
  </a>
  <a href="<?= BASE_URL ?>/admin/photos.php" 
     class="btn btn-outline-primary">
    <i class="bi bi-images me-2"></i>Manage Photos
  </a>
  <a href="<?= BASE_URL ?>/admin/albums.php" 
     class="btn btn-outline-success">
    <i class="bi bi-collection me-2"></i>Manage Albums
  </a>
</div>

  <!-- Stats -->
  <div class="row g-4 mb-5">
    <div class="col-6 col-md-4">
      <div class="stat-card-v2">
        <div class="stat-icon-box icon-blue">
          <i class="bi bi-people"></i>
        </div>
        <div class="stat-number-v2"><?= $totalUsers ?></div>
        <div class="stat-label-v2">Total Users</div>
      </div>
    </div>
    <div class="col-6 col-md-4">
      <div class="stat-card-v2">
        <div class="stat-icon-box icon-pink">
          <i class="bi bi-images"></i>
        </div>
        <div class="stat-number-v2"><?= $totalPhotos ?></div>
        <div class="stat-label-v2">Total Photos</div>
      </div>
    </div>
    <div class="col-6 col-md-4">
      <div class="stat-card-v2">
        <div class="stat-icon-box icon-green">
          <i class="bi bi-collection"></i>
        </div>
        <div class="stat-number-v2"><?= $totalAlbums ?></div>
        <div class="stat-label-v2">Total Albums</div>
      </div>
    </div>
  </div>

  <div class="row g-4">

    <!-- Recent Users -->
    <div class="col-md-6">
      <div class="card p-4">
        <h5 class="fw-bold mb-3">
          <i class="bi bi-people me-2 text-primary"></i>Recent Users
        </h5>
        <div class="table-responsive">
          <table class="table table-hover">
            <thead class="table-light">
              <tr>
                <th>User</th>
                <th>Email</th>
                <th>Role</th>
                <th>Joined</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recentUsers as $u): ?>
              <tr>
                <td class="fw-medium">
                  @<?= sanitize($u['username']) ?>
                </td>
                <td class="text-muted small">
                  <?= sanitize($u['email']) ?>
                </td>
                <td>
                  <?php if ($u['role'] === 'admin'): ?>
                    <span class="badge bg-danger">Admin</span>
                  <?php else: ?>
                    <span class="badge bg-primary">User</span>
                  <?php endif; ?>
                </td>
                <td class="text-muted small">
                  <?= date('d M Y', strtotime($u['created_at'])) ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Recent Photos -->
    <div class="col-md-6">
      <div class="card p-4">
        <h5 class="fw-bold mb-3">
          <i class="bi bi-images me-2 text-primary"></i>Recent Photos
        </h5>
        <div class="row g-2">
          <?php foreach ($recentPhotos as $photo): ?>
            <div class="col-4">
              <div style="aspect-ratio:1;overflow:hidden;border-radius:8px;">
                <img src="<?= BASE_URL ?>/uploads/photos/<?= $photo['filename'] ?>"
                     style="width:100%;height:100%;object-fit:cover;"
                     alt="<?= sanitize($photo['title']) ?>">
              </div>
              <small class="text-muted d-block text-center mt-1">
                @<?= sanitize($photo['username']) ?>
              </small>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

  </div>
</div>

<style>
.stat-icon {
    position: absolute;
    right: -5px;
    bottom: -5px;
    font-size: 4rem;
    opacity: 0.15;
}
</style>

<?php require_once '../includes/footer.php'; ?>