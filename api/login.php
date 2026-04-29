<?php
include __DIR__ . '/config.php';

function setAuthCookie(string $name, string $value, int $days = 1): void {
    setcookie($name, $value, [
        'expires'  => time() + (86400 * $days),
        'path'     => '/',
        'secure'   => true,
        'httponly' => true,
        'samesite' => 'Lax'   // PERBAIKAN: Strict -> Lax agar cookie terkirim saat navigasi
    ]);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query  = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        if (password_verify($password, $row['password'])) {
            // Buat token & simpan hash ke DB
            $token       = bin2hex(random_bytes(32));
            $hashedToken = hash('sha256', $token);
            $userId      = (int) $row['id'];
            $expiresAt   = date('Y-m-d H:i:s', time() + 86400);

            $saveToken = "INSERT INTO user_tokens (user_id, token, expires_at)
                          VALUES ('$userId', '$hashedToken', '$expiresAt')
                          ON DUPLICATE KEY UPDATE token = '$hashedToken', expires_at = '$expiresAt'";
            mysqli_query($conn, $saveToken);

            // Set cookie
            setAuthCookie('auth_token', $token);

            // Redirect sesuai role
            if ($row['role'] == 'admin') {
                header("Location: /api/dashboard-admin.php");
            } else {
                header("Location: /api/dashboard-user.php");
            }
            exit();

        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Email tidak terdaftar!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Klinik Sehat</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md border border-gray-100">
        <h2 class="text-3xl font-bold text-center text-blue-600 mb-2">Masuk</h2>
        <p class="text-center text-gray-500 mb-8">Sistem Jadwal Praktik Dokter</p>
        
        <form action="" method="POST" class="space-y-5">
            <div>
                <label class="block text-sm font-semibold text-gray-700">Email</label>
                <input type="email" name="email" required class="w-full mt-1 px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">Password</label>
                <input type="password" name="password" required class="w-full mt-1 px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition">
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-xl font-bold hover:bg-blue-700 shadow-lg shadow-blue-100 transition transform active:scale-95">
                Masuk ke Dashboard
            </button>
        </form>
        <p class="mt-6 text-center text-sm text-gray-600">Belum punya akun? <a href="register.php" class="text-blue-600 font-bold hover:underline">Daftar Pasien</a></p>
    </div>
</body>
</html>