<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!defined('SITE_NAME')) {
    require_once __DIR__ . '/../config/constants.php';
}
if (!function_exists('sanitize')) {
    require_once __DIR__ . '/functions.php';
}
$pageTitle = $pageTitle ?? SITE_NAME;
?>
<!DOCTYPE html>
<html lang="en" <?php if(isset($_COOKIE['darkMode']) && $_COOKIE['darkMode'] === 'true') echo 'data-bs-theme="dark"'; ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> — <?= SITE_NAME ?></title>

    <!-- Dark Mode: HTML render hone se pehle hi theme set karo, taki flash na ho -->
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>