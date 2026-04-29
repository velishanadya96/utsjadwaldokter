<?php
$host = 'gateway01.ap-southeast-1.prod.alicloud.tidbcloud.com';
$port = 4000;
$user = '2h7AMb9FTB6BmYe.root';
$pass = '29xnSbI4kHNOZnPN';
$db   = 'klinik_db';

$conn = mysqli_init();

// Set SSL: aktifkan SSL tapi skip verifikasi sertifikat server
// Cara ini kompatibel dengan OpenSSL versi lama di Vercel
mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);

$real_connect = mysqli_real_connect(
    $conn,
    $host,
    $user,
    $pass,
    $db,
    $port,
    NULL,
    MYSQLI_CLIENT_SSL  // aktifkan SSL
);

if (!$real_connect) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');
?>