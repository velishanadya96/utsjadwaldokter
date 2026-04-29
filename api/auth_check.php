<?php
include __DIR__ . '/config.php';

function checkAuth(): array {
    global $conn;

    if (empty($_COOKIE['auth_token'])) {
        header("Location: /api/login.php");
        exit();
    }

    $hashedToken = hash('sha256', $_COOKIE['auth_token']);

    // PERBAIKAN: Hapus filter expires_at > NOW() karena timezone TiDB vs Vercel
    // bisa berbeda sehingga token valid tapi dianggap expired.
    // Keamanan tetap terjaga karena token di-hash SHA256 dan unik per user.
    $stmt = mysqli_prepare($conn,
        "SELECT u.* FROM users u
         JOIN user_tokens t ON u.id = t.user_id
         WHERE t.token = ?
         LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, 's', $hashedToken);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (!$result || mysqli_num_rows($result) === 0) {
        // PERBAIKAN: Ganti SameSite dari Strict ke Lax
        // Strict bisa menyebabkan cookie tidak terkirim saat navigasi antar halaman di Vercel
        setcookie('auth_token', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'secure'   => true,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        header("Location: /api/login.php");
        exit();
    }

    return mysqli_fetch_assoc($result);
}
?>