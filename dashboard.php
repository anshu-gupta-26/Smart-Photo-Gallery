<?php
require_once 'includes/auth_check.php';
require_once 'config/database.php';

// Database se stats lo
$user_id = $_SESSION['user_id'];

// Total photos count
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM photos WHERE user_id = ?");
$stmt->execute([$user_id]);
$totalPhotos = $stmt->fetch()['total'];

// Total albums count
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM albums WHERE user_id = ?");
$stmt->execute([$user_id]);
$totalAlbums = $stmt->fetch()['total'];

// Total users — sirf admin ke liye
$totalUsers = 0;
if (isAdmin()) {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $totalUsers = $stmt->fetch()['total'];
}

// Pagination
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = PHOTOS_PER_PAGE;
$offset  = ($page - 1) * $perPage;

// Total photos count (for pagination)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM photos WHERE user_id = ?");
$stmt->execute([$user_id]);
$totalPhotosCount = (int)$stmt->fetchColumn();
$totalPages       = ceil($totalPhotosCount / $perPage);

// Photos with pagination
$stmt = $pdo->prepare("
    SELECT * FROM photos 
    WHERE user_id = ? 
    ORDER BY created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->bindValue(1, $user_id, PDO::PARAM_INT);
$stmt->bindValue(2, $perPage, PDO::PARAM_INT);
$stmt->bindValue(3, $offset, PDO::PARAM_INT);
$stmt->execute();
$recentPhotos = $stmt->fetchAll();

$pageTitle = "Dashboard";
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<!-- Page Header -->
<div class="page-header">
  <div class="container">
   <h2 class="fw-bold display-6 mb-2">
Welcome back,
<?= sanitize($_SESSION['full_name'] ?: $_SESSION['username']) ?> 👋
</h2>

<p class="opacity-75 mb-0">
Here's a quick overview of your gallery today.
</p>
    <p class="mb-0 opacity-75">
      Welcome back, <strong><?= sanitize($_SESSION['full_name'] ?: $_SESSION['username']) ?></strong>! 👋
    </p>
  </div>
</div>

<div class="container pb-5">

  <!-- Flash Message -->
  <?= getFlash() ?>

  <!-- Stats Cards -->
  <div class="row g-4 mb-5">

    <div class="col-6 col-md-3">
      <a href="<?= BASE_URL ?>/photos/upload.php" class="text-decoration-none">
        <div class="stat-card-v2">
          <div class="stat-icon-box icon-blue">
            <i class="bi bi-image"></i>
          </div>
          <div class="stat-number-v2"><?= $totalPhotos ?></div>
          <div class="stat-label-v2">My Photos</div>
        </div>
      </a>
    </div>

    <div class="col-6 col-md-3">
      <a href="<?= BASE_URL ?>/albums/index.php" class="text-decoration-none">
        <div class="stat-card-v2">
          <div class="stat-icon-box icon-pink">
            <i class="bi bi-collection"></i>
          </div>
          <div class="stat-number-v2"><?= $totalAlbums ?></div>
          <div class="stat-label-v2">My Albums</div>
        </div>
      </a>
    </div>

    <div class="col-6 col-md-3">
      <a href="<?= BASE_URL ?>/admin/index.php" class="text-decoration-none">
        <div class="stat-card-v2">
          <div class="stat-icon-box icon-green">
            <i class="bi bi-people"></i>
          </div>
          <div class="stat-number-v2"><?= $totalUsers ?></div>
          <div class="stat-label-v2">Total Users</div>
        </div>
      </a>
    </div>

    <div class="col-6 col-md-3">
      <a href="<?= BASE_URL ?>/photos/search.php" class="text-decoration-none">
        <div class="stat-card-v2">
          <div class="stat-icon-box icon-orange">
            <i class="bi bi-bar-chart"></i>
          </div>
          <div class="stat-number-v2">
            <?= $totalPhotos > 0 ? round(($totalPhotos / max($totalAlbums,1)), 1) : 0 ?>
          </div>
          <div class="stat-label-v2">Avg Photos/Album</div>
        </div>
      </a>
    </div>

  </div>

  <!-- Quick Actions -->
  <div class="row g-3 mb-5">
    <div class="col-12">
      <h5 class="fw-bold mb-3">
        <i class="bi bi-lightning me-2 text-warning"></i>Quick Actions
      </h5>
    </div>

    <div class="col-6 col-md-3">
      <a href="<?= BASE_URL ?>/photos/upload.php" class="text-decoration-none">
        <div class="action-card-v2">
          <div class="action-icon-box icon-blue">
            <i class="bi bi-cloud-upload"></i>
          </div>
          <div class="action-label-v2">Photo Upload</div>
        </div>
      </a>
    </div>

    <div class="col-6 col-md-3">
      <a href="<?= BASE_URL ?>/albums/create.php" class="text-decoration-none">
        <div class="action-card-v2">
          <div class="action-icon-box icon-green">
            <i class="bi bi-folder-plus"></i>
          </div>
          <div class="action-label-v2">New Album</div>
        </div>
      </a>
    </div>

    <div class="col-6 col-md-3">
      <a href="<?= BASE_URL ?>/photos/search.php" class="text-decoration-none">
        <div class="action-card-v2">
          <div class="action-icon-box icon-cyan">
            <i class="bi bi-search"></i>
          </div>
          <div class="action-label-v2">Search Photos</div>
        </div>
      </a>
    </div>

    <div class="col-6 col-md-3">
      <a href="<?= BASE_URL ?>/user/profile.php" class="text-decoration-none">
        <div class="action-card-v2">
          <div class="action-icon-box icon-orange">
            <i class="bi bi-person"></i>
          </div>
          <div class="action-label-v2">My Profile</div>
        </div>
      </a>
    </div>
  </div>

  <!-- Recent Photos -->
  <div class="row">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">
          <i class="bi bi-clock-history me-2 text-primary"></i>Recent Photos
        </h5>
        <a href="<?= BASE_URL ?>/photos/upload.php" class="btn btn-sm btn-outline-primary">
          + Upload New
        </a>
      </div>

      <?php if (empty($recentPhotos)): ?>
        <!-- Koi photo nahi hai abhi -->
        <div class="card text-center py-5">
          <div class="card-body">
            <i class="bi bi-images text-muted" style="font-size:4rem"></i>
            <h5 class="mt-3 text-muted">Abhi koi photo nahi hai</h5>
            <p class="text-muted">Pehli photo upload karo!</p>
            <a href="<?= BASE_URL ?>/photos/upload.php" class="btn btn-primary">
              <i class="bi bi-cloud-upload me-2"></i>Upload Karo
            </a>
          </div>
        </div>

      <?php else: ?>
        <!-- Photos Grid -->
        <div class="photo-grid">
          <?php foreach ($recentPhotos as $photo): ?>
            <div class="photo-card">
              <img src="<?= BASE_URL ?>/uploads/photos/<?= $photo['filename'] ?>"
                   alt="<?= sanitize($photo['title']) ?>"
                   loading="lazy">
              <div class="overlay">
                <a href="#" onclick="openLightbox('<?= BASE_URL ?>/uploads/photos/<?= $photo['filename'] ?>', '<?= sanitize($photo['title']) ?>')"
                   title="View">
                  <i class="bi bi-eye"></i>
                </a>
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

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
      <nav class="mt-4 d-flex justify-content-center">
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

  </div>
</div>

<?php require_once 'includes/footer.php'; ?>