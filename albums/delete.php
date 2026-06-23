<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

// Sirf POST allow karo
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/albums/index.php');
}

// CSRF verify karo
if (!verifyToken($_POST['csrf_token'] ?? '')) {
    setFlash('error', 'Invalid request!');
    redirect(BASE_URL . '/albums/index.php');
}

$user_id  = $_SESSION['user_id'];
$album_id = (int)($_POST['id'] ?? 0);

if ($album_id > 0) {

    // Album ki sari photos nikalo
    $stmt = $pdo->prepare(
        "SELECT filename FROM photos WHERE album_id = ? AND user_id = ?"
    );
    $stmt->execute([$album_id, $user_id]);
    $photos = $stmt->fetchAll();

    // Physical image files delete karo
    foreach ($photos as $photo) {
        $file = UPLOAD_PATH . $photo['filename'];
        if (file_exists($file)) {
            unlink($file);
        }
    }

    // Photos table se records delete karo
    $stmt = $pdo->prepare(
        "DELETE FROM photos WHERE album_id = ? AND user_id = ?"
    );
    $stmt->execute([$album_id, $user_id]);

    // Album delete karo
    $stmt = $pdo->prepare(
        "DELETE FROM albums WHERE id = ? AND user_id = ?"
    );
    $stmt->execute([$album_id, $user_id]);

    setFlash('success', 'Album aur uski photos delete ho gayi!');
}

redirect(BASE_URL . '/albums/index.php');
?>