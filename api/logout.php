<?php
include __DIR__ . '/config.php';

if (!empty($_COOKIE['auth_token'])) {
    $hashed = hash('sha256', $_COOKIE['auth_token']);
    mysqli_query($conn, "DELETE FROM user_tokens WHERE token = '$hashed'");
}

setcookie('auth_token', '', time() - 3600, '/');
header("Location: /login.php");
exit();