<?php
session_start();
require_once '../config/constants.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if (isLoggedIn()) redirect(BASE_URL . '/dashboard.php');

// State verify karo
if (!isset($_GET['state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
    setFlash('error', 'Invalid OAuth state.');
    redirect(BASE_URL . '/auth/login.php');
}

if (!isset($_GET['code'])) {
    setFlash('error', 'Google login failed. Try again.');
    redirect(BASE_URL . '/auth/login.php');
}

// Code se token lo
$tokenResponse = file_get_contents('https://oauth2.googleapis.com/token', false, stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => 'Content-Type: application/x-www-form-urlencoded',
        'content' => http_build_query([
            'code'          => $_GET['code'],
            'client_id'     => $_ENV['GOOGLE_CLIENT_ID'],
            'client_secret' => $_ENV['GOOGLE_CLIENT_SECRET'],
            'redirect_uri'  => $_ENV['GOOGLE_REDIRECT_URI'],
            'grant_type'    => 'authorization_code',
        ]),
    ],
]));

$token = json_decode($tokenResponse, true);

if (!isset($token['access_token'])) {
    setFlash('error', 'Google token error. Try again.');
    redirect(BASE_URL . '/auth/login.php');
}

// User info lo
$userInfoResponse = file_get_contents('https://www.googleapis.com/oauth2/v2/userinfo', false, stream_context_create([
    'http' => [
        'header' => 'Authorization: Bearer ' . $token['access_token'],
    ],
]));

$googleUser = json_decode($userInfoResponse, true);

if (!isset($googleUser['email'])) {
    setFlash('error', 'Could not get Google user info.');
    redirect(BASE_URL . '/auth/login.php');
}

// Check karo user already exist karta hai
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$googleUser['email']]);
$user = $stmt->fetch();

if (!$user) {
    // Naya user banao
    $username = 'user_' . substr(md5($googleUser['email']), 0, 8);
    
    // Username unique banao
    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $checkStmt->execute([$username]);
    while ($checkStmt->fetch()) {
        $username = 'user_' . substr(md5($googleUser['email'] . time()), 0, 8);
        $checkStmt->execute([$username]);
    }

    $pdo->prepare("INSERT INTO users (username, email, password, full_name, profile_pic, role, status) VALUES (?, ?, ?, ?, ?, 'user', 'active')")
        ->execute([
            $username,
            $googleUser['email'],
            password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT),
            $googleUser['name'] ?? $username,
            $googleUser['picture'] ?? null,
        ]);

    $stmt->execute([$googleUser['email']]);
    $user = $stmt->fetch();
}

if ($user['status'] !== 'active') {
    setFlash('error', 'Your account is inactive.');
    redirect(BASE_URL . '/auth/login.php');
}

// Login karo
session_regenerate_id(true);
$_SESSION['user_id']       = $user['id'];
$_SESSION['username']      = $user['username'];
$_SESSION['full_name']     = $user['full_name'];
$_SESSION['role']          = $user['role'];
$_SESSION['profile_pic']   = $user['profile_pic'];
$_SESSION['last_activity'] = time();

setFlash('success', 'Google se successfully login ho gaya!');
redirect(BASE_URL . '/dashboard.php');
?>