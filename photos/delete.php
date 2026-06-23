<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

// Sirf POST allow karo
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/dashboard.php');
}

// CSRF verify karo
if (!verifyToken($_POST['csrf_token'] ?? '')) {
    setFlash('error', 'Invalid request!');
    redirect(BASE_URL . '/dashboard.php');
}

$user_id  = $_SESSION['user_id'];
$photo_id = (int)($_POST['id'] ?? 0);

if ($photo_id > 0) {
    // Photo fetch karo
    $stmt = $pdo->prepare(
        "SELECT * FROM photos WHERE id = ? AND user_id = ?"
    );
    $stmt->execute([$photo_id, $user_id]);
    $photo = $stmt->fetch();

    if ($photo) {
        // File bhi delete karo server se
        $filepath = UPLOAD_PATH . $photo['filename'];
        if (file_exists($filepath)) {
            unlink($filepath);
        }

        // Database se delete karo
        $pdo->prepare("DELETE FROM photos WHERE id = ? AND user_id = ?")
            ->execute([$photo_id, $user_id]);

        setFlash('success', 'Photo delete ho gayi!');
    }
}

redirect(BASE_URL . '/dashboard.php');
?>