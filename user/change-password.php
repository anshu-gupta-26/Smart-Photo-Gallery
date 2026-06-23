<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

$user_id = $_SESSION['user_id'];
$errors  = [];

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = generateToken();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verifyToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid request. Please try again.";
    } else {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        // Verify current password
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if (!password_verify($current, $user['password'])) {
            $errors[] = "Current password is incorrect.";
        }
        if (strlen($new) < 6) {
            $errors[] = "New password must be at least 6 characters long.";
        }
        if ($new !== $confirm) {
            $errors[] = "Passwords do not match.";
        }

        if (empty($errors)) {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password=? WHERE id=?")
                ->execute([$hashed, $user_id]);

            setFlash('success', 'Password changed successfully!');
            redirect(BASE_URL . '/user/profile.php');
        }
    }
}

$pageTitle = "Change Password";
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="page-header">
  <div class="container">
    <h2 class="fw-bold mb-1">
      <i class="bi bi-key me-2"></i>Change Password
    </h2>
  </div>
</div>

<div class="container pb-5" style="max-width:500px">

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <?php foreach ($errors as $e): ?>
        <div><i class="bi bi-exclamation-circle me-1"></i><?= $e ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="card p-4">
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

      <div class="mb-3">
        <label class="form-label fw-medium">Current Password</label>
        <input type="password" name="current_password"
               class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label fw-medium">New Password</label>
        <input type="password" name="new_password"
               class="form-control"
               placeholder="At least 6 characters" required>
      </div>

      <div class="mb-4">
        <label class="form-label fw-medium">Confirm New Password</label>
        <input type="password" name="confirm_password"
               class="form-control" required>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary px-4 fw-semibold">
          <i class="bi bi-key me-2"></i>Change Password
        </button>
        <a href="<?= BASE_URL ?>/user/profile.php"
           class="btn btn-outline-secondary">Cancel</a>
      </div>

    </form>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>