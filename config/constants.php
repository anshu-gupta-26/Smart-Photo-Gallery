<?php
// .env file load karo
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}
define('SITE_NAME', 'Smart Photo Gallery');
define('SITE_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/photo_gallery');
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . '/uploads/photos/');
define('PROFILE_UPLOAD_PATH', ROOT_PATH . '/uploads/profiles/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024);
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('PHOTOS_PER_PAGE', 12);
define('ALBUMS_PER_PAGE', 9);
define('SESSION_TIMEOUT', 1800);
?>