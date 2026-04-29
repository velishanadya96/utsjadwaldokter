<?php
include __DIR__ . '/config.php';

if (!empty($_COOKIE['auth_token'])) {
    $hashed = hash('sha256', $_COOKIE['auth_token']);
    mysqli_query($conn, "DELETE FROM user_tokens WHERE token = '$hashed'");
}

setcookie('auth_token', '', [
    'expires'  => time() - 3600,
    'path'     => '/',
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);
header("Location: /api/login.php");
exit();