<?php
session_start();
require_once '../config/constants.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/PHPMailer/Exception.php';
require_once '../includes/PHPMailer/PHPMailer.php';
require_once '../includes/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if (isLoggedIn()) redirect(BASE_URL . '/dashboard.php');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = generateToken();
}

$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid request. Dobara try karo.";
    } else {
        $email = trim($_POST['email'] ?? '');
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Valid email address daalo.";
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND status = 'active'");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                $pdo->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);
                $token = generateToken();
                $pdo->prepare("INSERT INTO password_resets (email, token) VALUES (?, ?)")->execute([$email, $token]);
                $resetLink = BASE_URL . '/auth/reset-password.php?token=' . $token;

                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = $_ENV['MAIL_HOST'];
                    $mail->SMTPAuth   = true;
                    $mail->Username   = $_ENV['MAIL_USERNAME'];
                    $mail->Password   = $_ENV['MAIL_PASSWORD'];
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = (int)$_ENV['MAIL_PORT'];

                    $mail->setFrom($_ENV['MAIL_USERNAME'], $_ENV['MAIL_FROM_NAME']);
                    $mail->addAddress($email);
                    $mail->isHTML(true);
                    $mail->Subject = 'Password Reset — ' . SITE_NAME;
                    $mail->Body    = '
                    <div style="font-family:Inter,sans-serif;max-width:500px;margin:0 auto;padding:30px;background:#f8f9fa;border-radius:12px;">
                        <div style="text-align:center;margin-bottom:20px;">
                            <h2 style="color:#4361ee;">🔐 Password Reset</h2>
                        </div>
                        <p>Aapne password reset request ki hai.</p>
                        <p>Neeche diye button pe click karo:</p>
                        <div style="text-align:center;margin:30px 0;">
                            <a href="' . $resetLink . '" 
                               style="background:#4361ee;color:white;padding:14px 30px;
                                      border-radius:8px;text-decoration:none;font-weight:600;">
                                Reset Password
                            </a>
                        </div>
                        <p style="color:#666;font-size:0.85rem;">
                            Ye link 1 ghante mein expire ho jayega.<br>
                            Agar aapne request nahi ki to ignore kar do.
                        </p>
                    </div>';

                    $mail->send();
                    $success = "Password reset link aapki email pe bhej diya gaya!";

                } catch (Exception $e) {
                    $errors[] = "Email nahi bhej paya: " . $mail->ErrorInfo;
                }
            } else {
                $success = "Agar ye email registered hai to reset link bhej diya jayega.";
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
    <title>Forgot Password — <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card">

    <div class="text-center mb-4">
      <i class="bi bi-key text-primary" style="font-size:3rem"></i>
      <h3 class="fw-bold mt-2">Password Bhool Gaye?</h3>
      <p class="text-muted">Email daalo — reset link milega</p>
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
        <i class="bi bi-envelope-check" style="font-size:2rem"></i>
        <p class="mt-2 mb-0"><?= $success ?></p>
        <small class="text-muted">Spam folder bhi check karo</small>
      </div>
    <?php endif; ?>

    <?php if (empty($success)): ?>
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
      <div class="mb-4">
        <label class="form-label fw-medium">Email Address</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-envelope"></i></span>
          <input type="email" name="email" class="form-control"
                 placeholder="apni registered email" required>
        </div>
      </div>
      <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
        <i class="bi bi-send me-2"></i>
      </button>
    </form>
    <?php endif; ?>

    <hr class="my-3">
    <p class="text-center text-muted mb-0">
      <a href="<?= BASE_URL ?>/auth/login.php" class="text-primary fw-semibold">
        <i class="bi bi-arrow-left me-1"></i>
      </a>
    </p>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>