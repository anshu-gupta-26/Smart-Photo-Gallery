<?php
session_start();
require_once '../config/constants.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if (isLoggedIn()) redirect(BASE_URL . '/dashboard.php');

// Google OAuth URL banao
$clientId    = $_ENV['GOOGLE_CLIENT_ID'];
$redirectUri = $_ENV['GOOGLE_REDIRECT_URI'];

$params = [
    'client_id'     => $clientId,
    'redirect_uri'  => $redirectUri,
    'response_type' => 'code',
    'scope'         => 'openid email profile',
    'access_type'   => 'online',
    'state'         => bin2hex(random_bytes(16)),
];

$_SESSION['oauth_state'] = $params['state'];

$authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);

header('Location: ' . $authUrl);
exit();
?>