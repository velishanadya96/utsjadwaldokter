<?php
include __DIR__ . '/config.php';

function checkAuth(): array {
    global $conn;

    // Cek apakah cookie auth_token ada
    if (empty($_COOKIE['auth_token'])) {
        header("Location: /login.php");
        exit();
    }

    // Hash token dari cookie lalu cocokkan dengan DB
    $hashedToken = hash('sha256', $_COOKIE['auth_token']);
    $query = "SELECT u.* FROM users u
              JOIN user_tokens t ON u.id = t.user_id
              WHERE t.token = '$hashedToken'
                AND t.expires_at > NOW()
              LIMIT 1";
    $result = mysqli_query($conn, $query);

    // Token tidak valid atau sudah expired
    if (mysqli_num_rows($result) === 0) {
        setcookie('auth_token', '', time() - 3600, '/');
        header("Location: /login.php");
        exit();
    }

    return mysqli_fetch_assoc($result);
}
?>