<?php ?>
<nav class="navbar navbar-expand-lg navbar-dark sticky-top shadow">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>">
      <i class="bi bi-images me-2"></i><?= SITE_NAME ?>
    </a>
    <button class="navbar-toggler" type="button"
            data-bs-toggle="collapse" data-bs-target="#navbarMain">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarMain">
      <ul class="navbar-nav me-auto">
        <?php if (isLoggedIn()): ?>
          <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>/dashboard.php">
              <i class="bi bi-speedometer2 me-1"></i>Dashboard
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>/albums/index.php">
              <i class="bi bi-collection me-1"></i>Albums
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>/photos/upload.php">
              <i class="bi bi-cloud-upload me-1"></i>Upload
            </a>
          </li>
        <?php endif; ?>
      </ul>
      <ul class="navbar-nav ms-auto align-items-center">
        <?php if (isLoggedIn()): ?>
          <?php if (isAdmin()): ?>
          <li class="nav-item">
            <a class="nav-link text-warning fw-semibold" 
               href="<?= BASE_URL ?>/admin/index.php">
              <i class="bi bi-shield-lock me-1"></i>Admin
            </a>
          </li>
          <?php endif; ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
              <i class="bi bi-person-circle me-1"></i>
              <?= sanitize($_SESSION['username'] ?? 'User') ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/user/profile.php">
                <i class="bi bi-person me-2"></i>My Profile</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/auth/logout.php">
                <i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>/auth/login.php">Login</a>
          </li>
          <li class="nav-item">
            <a class="btn btn-light btn-sm ms-2" href="<?= BASE_URL ?>/auth/register.php">Register</a>
          </li>
        <?php endif; ?>
        <!-- Dark Mode Toggle -->
        <li class="nav-item ms-2">
          <button id="darkModeBtn" class="btn btn-link text-white p-1" title="Toggle Dark Mode">
            <i class="bi bi-moon-stars-fill fs-5"></i>
          </button>
        </li>
      </ul>
    </div>
  </div>
</nav>