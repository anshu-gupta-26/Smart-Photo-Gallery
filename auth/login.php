<?php
session_start();
require_once '../config/constants.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if (isLoggedIn()) redirect(BASE_URL . '/dashboard.php');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = generateToken();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid request. Please try again.";
    } else {
        if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = 0;
        if (!isset($_SESSION['login_lockout'])) $_SESSION['login_lockout'] = 0;

        if ($_SESSION['login_lockout'] > time()) {
            $mins = ceil(($_SESSION['login_lockout'] - time()) / 60);
            $error = "Too many failed attempts. Please try again in $mins minutes.";
        } else {
            $username = sanitize($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                $error = "Please enter both username and password.";
            } else {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND status = 'active'");
                $stmt->execute([$username, $username]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['login_attempts'] = 0;
                    session_regenerate_id(true);
                    $_SESSION['user_id']       = $user['id'];
                    $_SESSION['username']      = $user['username'];
                    $_SESSION['full_name']     = $user['full_name'];
                    $_SESSION['role']          = $user['role'];
                    $_SESSION['profile_pic']   = $user['profile_pic'];
                    $_SESSION['last_activity'] = time();
                    redirect(BASE_URL . '/dashboard.php');
                } else {
                    $_SESSION['login_attempts']++;
                    if ($_SESSION['login_attempts'] >= 5) {
                        $_SESSION['login_lockout'] = time() + 900;
                        $_SESSION['login_attempts'] = 0;
                        $error = "Account locked for 15 minutes due to too many failed attempts.";
                    } else {
                        $remaining = 5 - $_SESSION['login_attempts'];
                        $error = "Invalid username or password. $remaining attempts remaining.";
                    }
                }
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
    <title>Sign In — <?= SITE_NAME ?></title>
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
     class="position-absolute top-0 start-0 m-4 text-white text-decoration-none d-flex align-items-center fw-medium"
     style="opacity:0.85;">
    <i class="bi bi-arrow-left-circle me-2 fs-5"></i> Back to Home
  </a>
  <div class="auth-card">

    <div class="text-center mb-4">
      <i class="bi bi-images text-primary" style="font-size:3rem"></i>
      <h3 class="fw-bold mt-2"><?= SITE_NAME ?></h3>
      <p class="text-muted">Sign in to your account</p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger">
        <i class="bi bi-exclamation-circle me-1"></i><?= $error ?>
      </div>
    <?php endif; ?>

    <?= getFlash() ?>

    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

      <div class="mb-3">
        <label class="form-label fw-medium">Username or Email</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-person"></i></span>
          <input type="text" name="username" class="form-control"
                 placeholder="Enter your username or email"
                 value="<?= sanitize($_POST['username'] ?? '') ?>" required>
        </div>
      </div>

      <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <label class="form-label fw-medium mb-0">Password</label>
          <a href="<?= BASE_URL ?>/auth/forgot.php"
             class="text-primary small text-decoration-none fw-medium">Forgot password?</a>
        </div>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-lock"></i></span>
          <input type="password" name="password" id="password"
                 class="form-control" placeholder="Enter your password" required>
          <button class="btn btn-outline-secondary" type="button" onclick="togglePass()">
            <i class="bi bi-eye"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
      </button>
    </form>

    <!-- Divider -->
    <div class="my-3 d-flex align-items-center gap-2">
      <hr class="flex-grow-1">
      <span class="text-muted small">or</span>
      <hr class="flex-grow-1">
    </div>

    <!-- Google Login Button (UI only) -->
    <a href="<?= BASE_URL ?>/auth/google-login.php"
       class="btn btn-outline-secondary w-100 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2 mb-3">
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
      Don't have an account?
      <a href="<?= BASE_URL ?>/auth/register.php" class="text-primary fw-semibold">
        Create an account
      </a>
    </p>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePass() {
    const field = document.getElementById('password');
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