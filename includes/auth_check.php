<?php
if (session_status() === PHP_SESSION_NONE) {
    if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
}

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/functions.php';

if (isset($_SESSION['last_activity'])) {
    $inactive = time() - $_SESSION['last_activity'];
    if ($inactive >= SESSION_TIMEOUT) {
        session_unset();
        session_destroy();
        redirect(BASE_URL . '/auth/login.php?reason=timeout');
    }
}

$_SESSION['last_activity'] = time();

requireLogin();
?>