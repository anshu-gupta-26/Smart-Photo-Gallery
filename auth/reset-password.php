<?php
session_start();
require_once '../config/constants.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if (isLoggedIn()) redirect(BASE_URL . '/dashboard.php');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = generateToken();
}

$errors     = [];
$success    = '';
$token      = trim($_GET['token'] ?? '');
$validToken = false;
$userEmail  = '';

if (!empty($token)) {
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();
    if ($reset) {
        $validToken = true;
        $userEmail  = $reset['email'];
    }
}

if (!$validToken) {
    $errors[] = "Reset link invalid ya expire ho gaya. Dobara try karo.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    if (!verifyToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid request.";
    } else {
        $password        = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        if (strlen($password) < 6) {
            $errors[] = "Password kam se kam 6 characters ka hona chahiye.";
        } elseif ($password !== $confirmPassword) {
            $errors[] = "Dono passwords match nahi karte.";
        } else {
            $pdo->prepare("UPDATE users SET password = ? WHERE email = ?")
                ->execute([password_hash($password, PASSWORD_DEFAULT), $userEmail]);
            $pdo->prepare("DELETE FROM password_resets WHERE email = ?")
                ->execute([$userEmail]);
            $success = "Password successfully change ho gaya! Ab login karo.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card">

    <div class="text-center mb-4">
      <i class="bi bi-shield-lock text-primary" style="font-size:3rem"></i>
      <h3 class="fw-bold mt-2">Naya Password Set Karo</h3>
      <?php if ($validToken): ?>
        <p class="text-success small">
          <i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($userEmail) ?> ke liye
        </p>
      <?php endif; ?>
    </div>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <?php foreach ($errors as $e): ?>
          <div><i class="bi bi-exclamation-circle me-1"></i><?= $e ?></div>
        <?php endforeach; ?>
        <div class="mt-2">
          <a href="<?= BASE_URL ?>/auth/forgot.php" class="btn btn-outline-danger btn-sm">
            Dobara Try Karo
          </a>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert alert-success">
        <i class="bi bi-check-circle me-1"></i><?= $success ?>
        <div class="mt-2">
          <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-success btn-sm w-100">
            <i class="bi bi-box-arrow-in-right me-1"></i>Login Karo
          </a>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($validToken && empty($success)): ?>
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
      <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

      <div class="mb-3">
        <label class="form-label fw-medium">Naya Password</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-lock"></i></span>
          <input type="password" name="password" id="password"
                 class="form-control" placeholder="Naya password" minlength="6" required>
          <button class="btn btn-outline-secondary" type="button" onclick="togglePass('password')">
            <i class="bi bi-eye"></i>
          </button>
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label fw-medium">Password Confirm Karo</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
          <input type="password" name="confirm_password" id="confirm_password"
                 class="form-control" placeholder="Password dobara daalo" minlength="6" required>
          <button class="btn btn-outline-secondary" type="button" onclick="togglePass('confirm_password')">
            <i class="bi bi-eye"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
        <i class="bi bi-shield-check me-2"></i>Password Reset Karo
      </button>
    </form>
    <?php endif; ?>

    <hr class="my-3">
    <p class="text-center text-muted mb-0">
      <a href="<?= BASE_URL ?>/auth/login.php" class="text-primary fw-semibold">
        <i class="bi bi-arrow-left me-1"></i>Login page pe wapas jao
      </a>
    </p>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePass(id) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>