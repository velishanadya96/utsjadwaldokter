<?php
include __DIR__ . '/config.php';

function checkAuth(): array {
    global $conn;

    // Cek apakah cookie auth_token ada
    if (empty($_COOKIE['auth_token'])) {
        // PERBAIKAN: Redirect konsisten ke /api/login.php
        header("Location: /api/login.php");
        exit();
    }

    $hashedToken = hash('sha256', $_COOKIE['auth_token']);

    // PERBAIKAN: Pakai prepared statement
    $stmt = mysqli_prepare($conn,
        "SELECT u.* FROM users u
         JOIN user_tokens t ON u.id = t.user_id
         WHERE t.token = ?
           AND t.expires_at > NOW()
         LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, 's', $hashedToken);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (!$result || mysqli_num_rows($result) === 0) {
        // Hapus cookie yang tidak valid / expired
        setcookie('auth_token', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'secure'   => true,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        header("Location: /api/login.php");
        exit();
    }

    return mysqli_fetch_assoc($result);
}
?>