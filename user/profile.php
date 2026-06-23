<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

$user_id = $_SESSION['user_id'];

// User ki info fetch karo
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// User ki stats
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM photos WHERE user_id = ?");
$stmt->execute([$user_id]);
$totalPhotos = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM albums WHERE user_id = ?");
$stmt->execute([$user_id]);
$totalAlbums = $stmt->fetch()['total'];

$pageTitle = "My Profile";
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="page-header">
  <div class="container">
    <h2 class="fw-bold mb-1">
      <i class="bi bi-person-circle me-2"></i>My Profile
    </h2>
    <p class="mb-0 opacity-75">Apni profile dekho aur edit karo</p>
  </div>
</div>

<div class="container pb-5">
  <?= getFlash() ?>

  <div class="row g-4 justify-content-center">

    <!-- Left: Profile Card -->
    <div class="col-md-4">
      <div class="card text-center p-4">

        <!-- Profile Picture -->
        <div class="mb-3">
          <?php
            $pic = !empty($user['profile_pic']) && $user['profile_pic'] !== 'default.png'
                ? BASE_URL . '/uploads/profiles/' . $user['profile_pic']
                : 'https://ui-avatars.com/api/?name=' . urlencode($user['full_name'] ?: $user['username']) . '&size=120&background=4361ee&color=fff&rounded=true';
          ?>
          <img src="<?= $pic ?>"
               class="rounded-circle border border-4 border-primary"
               width="120" height="120"
               style="object-fit:cover;">
        </div>

        <h4 class="fw-bold mb-1">
          <?= sanitize($user['full_name'] ?: $user['username']) ?>
        </h4>
        <p class="text-muted mb-1">@<?= sanitize($user['username']) ?></p>
        <p class="text-muted small mb-3">
          <?= sanitize($user['email']) ?>
        </p>

        <!-- Role Badge -->
        <?php if ($user['role'] === 'admin'): ?>
          <span class="badge bg-danger mb-3">
            <i class="bi bi-shield-fill me-1"></i>Admin
          </span>
        <?php else: ?>
          <span class="badge bg-primary mb-3">
            <i class="bi bi-person-fill me-1"></i>User
          </span>
        <?php endif; ?>

        <!-- Bio -->
        <?php if (!empty($user['bio'])): ?>
          <p class="text-muted small border-top pt-3">
            <?= sanitize($user['bio']) ?>
          </p>
        <?php endif; ?>

        <!-- Member Since -->
        <p class="text-muted small mb-4">
          <i class="bi bi-calendar3 me-1"></i>
          Member since <?= date('M Y', strtotime($user['created_at'])) ?>
        </p>

        <!-- Edit Button -->
        <a href="<?= BASE_URL ?>/user/edit-profile.php"
           class="btn btn-primary w-100 mb-2">
          <i class="bi bi-pencil me-2"></i>Edit Profile
        </a>
        <a href="<?= BASE_URL ?>/user/change-password.php"
           class="btn btn-outline-secondary w-100">
          <i class="bi bi-key me-2"></i>Change Password
        </a>

      </div>
    </div>

    <!-- Right: Stats + Activity -->
    <div class="col-md-8">

      <!-- Stats -->
      <div class="row g-3 mb-4">
        <div class="col-6">
          <div class="stat-card-v2">
            <div class="stat-icon-box icon-blue">
              <i class="bi bi-image"></i>
            </div>
            <div class="stat-number-v2"><?= $totalPhotos ?></div>
            <div class="stat-label-v2">Total Photos</div>
          </div>
        </div>
        <div class="col-6">
          <div class="stat-card-v2">
            <div class="stat-icon-box icon-pink">
              <i class="bi bi-collection"></i>
            </div>
            <div class="stat-number-v2"><?= $totalAlbums ?></div>
            <div class="stat-label-v2">Total Albums</div>
          </div>
        </div>
      </div>

      <!-- Account Info -->
      <div class="card p-4">
        <h5 class="fw-bold mb-3">
          <i class="bi bi-info-circle me-2 text-primary"></i>Account Details
        </h5>
        <table class="table table-borderless mb-0">
          <tr>
            <td class="text-muted" width="40%">Username</td>
            <td class="fw-medium">@<?= sanitize($user['username']) ?></td>
          </tr>
          <tr>
            <td class="text-muted">Email</td>
            <td class="fw-medium"><?= sanitize($user['email']) ?></td>
          </tr>
          <tr>
            <td class="text-muted">Full Name</td>
            <td class="fw-medium">
              <?= sanitize($user['full_name'] ?: 'Not set') ?>
            </td>
          </tr>
          <tr>
            <td class="text-muted">Account Type</td>
            <td class="fw-medium"><?= ucfirst($user['role']) ?></td>
          </tr>
          <tr>
            <td class="text-muted">Joined</td>
            <td class="fw-medium">
              <?= date('d M Y', strtotime($user['created_at'])) ?>
            </td>
          </tr>
        </table>
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