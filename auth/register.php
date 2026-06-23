<?php
session_start();
require_once '../config/constants.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if (isLoggedIn()) redirect(BASE_URL . '/dashboard.php');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = generateToken();
}

$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid request. Please try again.";
    } else {
        $full_name = trim($_POST['full_name'] ?? '');
        $username  = trim($_POST['username'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $password  = $_POST['password'] ?? '';
        $confirm   = $_POST['confirm_password'] ?? '';

        if (empty($username)) $errors[] = "Username is required.";
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
        if (strlen($password) < 8) $errors[] = "Password must be at least 8 characters.";
        if (!preg_match('/[A-Z]/', $password)) $errors[] = "Password must contain at least 1 uppercase letter.";
        if (!preg_match('/[0-9]/', $password)) $errors[] = "Password must contain at least 1 number.";
        if (!preg_match('/[@$!%*?&]/', $password)) $errors[] = "Password must contain at least 1 symbol (@$!%*?&).";
        if ($password !== $confirm) $errors[] = "Passwords do not match.";

        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $errors[] = "Username or email already exists.";
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $pdo->prepare("INSERT INTO users (username, email, password, full_name, role, status) VALUES (?, ?, ?, ?, 'user', 'active')")
                    ->execute([$username, $email, $hashed, $full_name]);
                $success = "Account created successfully! You can now sign in.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — <?= SITE_NAME ?></title>
    <script>
        (function() {
            var saved = localStorage.getItem('darkMode');
            if (saved === 'true') {
                document.documentElement.setAttribute('data-bs-theme', 'dark');
            } else {
                document.documentElement.removeAttribute('data-bs-theme');
            }
        })();
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper position-relative">
  <a href="<?= BASE_URL ?>" 
     class="position-fixed top-0 start-0 m-4 text-white text-decoration-none d-flex align-items-center fw-medium"
     style="opacity:0.85;z-index:999;">
    <i class="bi bi-arrow-left-circle me-2 fs-5"></i> Back to Home
  </a>
  <div class="auth-card" style="max-width:600px">

    <div class="text-center mb-4">
      <i class="bi bi-images text-primary" style="font-size:3rem"></i>
      <h3 class="fw-bold mt-2"><?= SITE_NAME ?></h3>
      <p class="text-muted">Create a new account</p>
    </div>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <?php foreach ($errors as $e): ?>
          <div><i class="bi bi-exclamation-circle me-1"></i><?= $e ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert alert-success text-center">
        <i class="bi bi-check-circle" style="font-size:2rem"></i>
        <p class="mt-2 mb-0"><?= $success ?></p>
        <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-success mt-3 w-100">
          <i class="bi bi-box-arrow-in-right me-1"></i>Sign In Now
        </a>
      </div>
    <?php endif; ?>

    <?php if (empty($success)): ?>
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

      <!-- Full Name + Username -->
      <div class="row g-3 mb-3">
        <div class="col-6">
          <label class="form-label fw-medium">Full Name <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input type="text" name="full_name" class="form-control"
                   placeholder="Your full name"
                   value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
          </div>
        </div>
        <div class="col-6">
          <label class="form-label fw-medium">Username <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-at"></i></span>
            <input type="text" name="username" class="form-control"
                   placeholder="Choose a username"
                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
          </div>
        </div>
      </div>

      <!-- Email -->
      <div class="mb-3">
        <label class="form-label fw-medium">Email <span class="text-danger">*</span></label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-envelope"></i></span>
          <input type="email" name="email" class="form-control"
                 placeholder="name@example.com"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>
      </div>

      <!-- Password + Confirm — 2 column -->
      <div class="row g-3 mb-1">
        <div class="col-6">
          <label class="form-label fw-medium">Password <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" name="password" id="password"
                   class="form-control" placeholder="Strong password" required>
            <button class="btn btn-outline-secondary" type="button" onclick="togglePass('password')">
              <i class="bi bi-eye"></i>
            </button>
          </div>
        </div>
        <div class="col-6">
          <label class="form-label fw-medium">Confirm <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
            <input type="password" name="confirm_password" id="confirm_password"
                   class="form-control" placeholder="Confirm password" required>
            <button class="btn btn-outline-secondary" type="button" onclick="togglePass('confirm_password')">
              <i class="bi bi-eye"></i>
            </button>
          </div>
        </div>
      </div>
      <p class="text-muted small mb-3">Min 8 chars, 1 Uppercase, 1 Number, 1 Symbol (@$!%*?&)</p>

      <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
        <i class="bi bi-person-plus me-2"></i>Create Account
      </button>
    </form>

    <div class="my-3 d-flex align-items-center gap-2">
      <hr class="flex-grow-1">
      <span class="text-muted small">or</span>
      <hr class="flex-grow-1">
    </div>

    <a href="<?= BASE_URL ?>/auth/google-login.php" class="btn btn-outline-secondary w-100 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2 mb-3">
      <svg width="18" height="18" viewBox="0 0 48 48">
        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.18 1.48-4.97 2.31-8.16 2.31-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
      </svg>
      Continue with Google
    </a>

    <hr class="my-3">
    <p class="text-center text-muted mb-0">
      Already have an account?
      <a href="<?= BASE_URL ?>/auth/login.php" class="text-primary fw-semibold">Sign In</a>
    </p>
    <?php endif; ?>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePass(id) {
    const field = document.getElementById(id);
    const btn = field.nextElementSibling.querySelector('i');
    if (field.type === 'password') {
        field.type = 'text';
        btn.className = 'bi bi-eye-slash';
    } else {
        field.type = 'password';
        btn.className = 'bi bi-eye';
    }
}
</script>
</body>
</html>