<?php
session_start();
require_once 'config/constants.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    redirect(BASE_URL . '/dashboard.php');
}

$pageTitle = "Welcome";
require_once 'includes/header.php';
?>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top landing-navbar">
  <div class="container">
    <a class="navbar-brand fw-bold fs-4" href="<?= BASE_URL ?>">
      <i class="bi bi-images me-2 text-primary"></i><?= SITE_NAME ?>
    </a>
    <div class="ms-auto d-flex gap-2 align-items-center">
      <button id="darkModeBtn" class="btn btn-link text-white p-1" title="Toggle Dark Mode">
        <i class="bi bi-moon-stars-fill fs-5"></i>
      </button>
      <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-outline-light btn-sm px-3">Sign In</a>
      <a href="<?= BASE_URL ?>/auth/register.php" class="btn btn-primary btn-sm px-3">Get Started</a>
    </div>
  </div>
</nav>

<!-- Hero Section -->
<section class="landing-hero">

  <!-- Background blur circles -->
  <div class="hero-blur hero-blur-1"></div>
  <div class="hero-blur hero-blur-2"></div>

  <div class="container py-5">
    <div class="row align-items-center">

      <!-- Left Content -->
      <div class="col-lg-6 text-center text-lg-start mb-5 mb-lg-0">
        <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-25 px-3 py-2 mb-4 rounded-pill">
          <i class="bi bi-stars me-1"></i> Smart Photo Management
        </span>
        <h1 class="display-3 fw-bold mb-4 lh-sm landing-heading">
          Your Memories,<br>
          <span class="landing-gradient-text">
            Beautifully Organized
          </span>
        </h1>
        <p class="lead opacity-75 mb-5 landing-subtext" style="font-size:1.15rem;">
          Upload, organize, and share your photos in stunning albums.
          Your personal gallery — secure, fast, and beautiful.
        </p>
        <div class="d-flex gap-3 flex-wrap justify-content-center justify-content-lg-start">
          <a href="<?= BASE_URL ?>/auth/register.php"
             class="btn btn-primary btn-lg px-5 py-3 fw-semibold rounded-pill">
            <i class="bi bi-rocket-takeoff me-2"></i>Get Started Free
          </a>
          <a href="<?= BASE_URL ?>/auth/login.php"
             class="btn btn-outline-light btn-lg px-5 py-3 fw-semibold rounded-pill landing-outline-btn">
            <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
          </a>
        </div>

        <!-- Stats -->
        <div class="d-flex gap-4 mt-5 flex-wrap justify-content-center justify-content-lg-start landing-stats">
          <div>
            <div class="fw-bold fs-4">100%</div>
            <div class="opacity-50 small">Secure</div>
          </div>
          <div class="stat-divider"></div>
          <div>
            <div class="fw-bold fs-4">Fast</div>
            <div class="opacity-50 small">Upload</div>
          </div>
          <div class="stat-divider"></div>
          <div>
            <div class="fw-bold fs-4">Free</div>
            <div class="opacity-50 small">Forever</div>
          </div>
        </div>
      </div>

      <!-- Right — Feature Cards -->
      <div class="col-lg-6">
        <div class="row g-3">
          <div class="col-6">
            <div class="p-4 rounded-4 h-100 landing-feature-card">
              <i class="bi bi-cloud-upload fs-1 text-primary mb-3 d-block"></i>
              <h6 class="fw-bold landing-feature-title">Easy Upload</h6>
              <p class="opacity-50 small mb-0 landing-feature-text">Drag &amp; drop multiple photos at once</p>
            </div>
          </div>
          <div class="col-6">
            <div class="p-4 rounded-4 h-100 mt-4 landing-feature-card">
              <i class="bi bi-collection fs-1 mb-3 d-block" style="color:#7c3aed"></i>
              <h6 class="fw-bold landing-feature-title">Smart Albums</h6>
              <p class="opacity-50 small mb-0 landing-feature-text">Organize photos into beautiful albums</p>
            </div>
          </div>
          <div class="col-6">
            <div class="p-4 rounded-4 h-100 landing-feature-card">
              <i class="bi bi-shield-check fs-1 mb-3 d-block" style="color:#06d6a0"></i>
              <h6 class="fw-bold landing-feature-title">Secure &amp; Private</h6>
              <p class="opacity-50 small mb-0 landing-feature-text">Your photos are safe and private</p>
            </div>
          </div>
          <div class="col-6">
            <div class="p-4 rounded-4 h-100 mt-4 landing-feature-card">
              <i class="bi bi-search fs-1 mb-3 d-block" style="color:#f72585"></i>
              <h6 class="fw-bold landing-feature-title">Quick Search</h6>
              <p class="opacity-50 small mb-0 landing-feature-text">Find any photo instantly</p>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- Features Section -->
<section class="py-5 landing-features-section">
  <div class="container py-4">
    <div class="text-center mb-5">
      <h2 class="fw-bold display-6 landing-heading">Everything You Need</h2>
      <p class="opacity-50 landing-subtext">Powerful features for managing your photo gallery</p>
    </div>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="p-4 rounded-4 text-center h-100 landing-feature-card-2">
          <div class="mb-3" style="font-size:3rem;">📸</div>
          <h5 class="fw-bold landing-feature-title">Multi Upload</h5>
          <p class="opacity-50 small landing-feature-text">Upload multiple photos simultaneously with live preview</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="p-4 rounded-4 text-center h-100 landing-feature-card-highlight">
          <div class="mb-3" style="font-size:3rem;">🔐</div>
          <h5 class="fw-bold landing-feature-title">Google Login</h5>
          <p class="opacity-50 small landing-feature-text">Sign in instantly with your Google account</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="p-4 rounded-4 text-center h-100 landing-feature-card-2">
          <div class="mb-3" style="font-size:3rem;">🌙</div>
          <h5 class="fw-bold landing-feature-title">Dark Mode</h5>
          <p class="opacity-50 small landing-feature-text">Beautiful dark mode for comfortable viewing</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CTA Section -->
<section class="py-5 text-center text-white landing-cta">
  <div class="container py-4">
    <h2 class="fw-bold display-6 mb-3">Ready to Get Started?</h2>
    <p class="opacity-75 mb-4">Join thousands of users who trust Smart Photo Gallery</p>
    <a href="<?= BASE_URL ?>/auth/register.php"
       class="btn btn-light btn-lg px-5 py-3 fw-bold rounded-pill">
      <i class="bi bi-rocket-takeoff me-2"></i>Create Free Account
    </a>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>