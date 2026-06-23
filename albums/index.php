<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

$user_id = $_SESSION['user_id'];

// Pagination
$page       = max(1, (int)($_GET['page'] ?? 1));
$perPage    = ALBUMS_PER_PAGE;
$offset     = ($page - 1) * $perPage;

// Total albums count
$totalAlbums = $pdo->prepare("SELECT COUNT(*) FROM albums WHERE user_id = ?");
$totalAlbums->execute([$user_id]);
$totalAlbums = (int)$totalAlbums->fetchColumn();
$totalPages  = ceil($totalAlbums / $perPage);

// Albums fetch with pagination
$stmt = $pdo->prepare("
    SELECT a.*, COUNT(p.id) as photo_count,
           (SELECT filename FROM photos 
            WHERE album_id = a.id 
            ORDER BY created_at DESC 
            LIMIT 1) as cover_photo
    FROM albums a
    LEFT JOIN photos p ON p.album_id = a.id
    WHERE a.user_id = ?
    GROUP BY a.id
    ORDER BY a.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->execute([$user_id, $perPage, $offset]);
$albums = $stmt->fetchAll();

$pageTitle = "My Albums";
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="page-header">
  <div class="container d-flex justify-content-between align-items-center">
    <div>
      <h2 class="fw-bold mb-1">
        <i class="bi bi-collection me-2"></i>My Albums
      </h2>
      <p class="mb-0 opacity-75">
        Total <?= $totalAlbums ?> albums • Page <?= $page ?> of <?= max(1,$totalPages) ?>
      </p>
    </div>
    <a href="<?= BASE_URL ?>/albums/create.php" class="btn btn-light fw-semibold">
      <i class="bi bi-folder-plus me-2"></i>New Album
    </a>
  </div>
</div>

<div class="container pb-5">

  <?= getFlash() ?>

  <?php if (empty($albums)): ?>
    <div class="card text-center py-5">
      <div class="card-body">
        <i class="bi bi-collection text-muted" style="font-size:4rem"></i>
        <h5 class="mt-3 text-muted">Koi album nahi hai abhi</h5>
        <a href="<?= BASE_URL ?>/albums/create.php" class="btn btn-success mt-2">
          <i class="bi bi-folder-plus me-2"></i>Pehla Album Banao
        </a>
      </div>
    </div>

  <?php else: ?>
    <div class="row g-4">
      <?php foreach ($albums as $album): ?>
        <div class="col-sm-6 col-md-4 col-lg-3">
          <div class="card h-100">

            <!-- Album Cover -->
<div style="height:160px;border-radius:12px 12px 0 0;overflow:hidden;position:relative;">
  <?php if ($album['cover_photo']): ?>
    <img src="<?= BASE_URL ?>/uploads/photos/<?= $album['cover_photo'] ?>"
         style="width:100%;height:100%;object-fit:cover;"
         loading="lazy">
  <?php else: ?>
    <div style="height:100%;
                background:linear-gradient(135deg,#4361ee,#764ba2);
                display:flex;flex-direction:column;
                align-items:center;justify-content:center;">
      <i class="bi bi-images text-white" style="font-size:3rem;opacity:0.8"></i>
    </div>
  <?php endif; ?>
  <!-- Photo count badge -->
  <span style="position:absolute;bottom:8px;right:8px;
               background:rgba(0,0,0,0.6);color:white;
               padding:2px 8px;border-radius:20px;font-size:0.8rem;">
    <i class="bi bi-images me-1"></i><?= $album['photo_count'] ?>
  </span>
</div>

            <div class="card-body">
              <h6 class="fw-bold mb-1"><?= sanitize($album['title']) ?></h6>
              <p class="text-muted small mb-0">
                <?php if ($album['status'] === 'private'): ?>
                  <i class="bi bi-lock text-danger"></i> Private
                <?php else: ?>
                  <i class="bi bi-globe text-success"></i> Public
                <?php endif; ?>
                &nbsp;•&nbsp;
                <i class="bi bi-calendar3"></i>
                <?= date('d M Y', strtotime($album['created_at'])) ?>
              </p>
            </div>

            <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
              <div class="d-flex gap-2">
                <a href="<?= BASE_URL ?>/albums/view.php?id=<?= $album['id'] ?>"
                   class="btn btn-primary btn-sm flex-fill">
                  <i class="bi bi-eye me-1"></i>View
                </a>
                <a href="<?= BASE_URL ?>/albums/edit.php?id=<?= $album['id'] ?>"
                   class="btn btn-outline-secondary btn-sm">
                  <i class="bi bi-pencil"></i>
                </a>
                <form method="POST" action="<?= BASE_URL ?>/albums/delete.php"
                      class="d-inline confirm-delete-form"
                      data-message="Ye album delete ho jayega!">
                  <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                  <input type="hidden" name="id" value="<?= $album['id'] ?>">
                  <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </div>
            </div>

          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
      <nav class="mt-5 d-flex justify-content-center">
        <ul class="pagination">

          <!-- Previous -->
          <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
            <a class="page-link" 
               href="?page=<?= $page - 1 ?>">
              <i class="bi bi-chevron-left"></i>
            </a>
          </li>

          <!-- Page Numbers -->
          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
              <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>

          <!-- Next -->
          <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
            <a class="page-link" 
               href="?page=<?= $page + 1 ?>">
              <i class="bi bi-chevron-right"></i>
            </a>
          </li>

        </ul>
      </nav>
    <?php endif; ?>

  <?php endif; ?>

</div>

<?php require_once '../includes/footer.php'; ?>