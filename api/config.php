<?php
$host = 'gateway01.ap-southeast-1.prod.alicloud.tidbcloud.com';
$port = 4000;
$user = '2h7AMb9FTB6BmYe.root';
$pass = '29xnSbI4kHNOZnPN';
$db   = 'klinik_db'; // PERBAIKAN: ganti tanda '-' dengan '_' di nama database TiDB Anda juga!

$conn = mysqli_init();

// Tidak set cipher spesifik — biarkan OpenSSL di server Vercel negotiate sendiri
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);

$real_connect = mysqli_real_connect(
    $conn,
    $host,
    $user,
    $pass,
    $db,
    $port,
    NULL,
    MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT
);

if (!$real_connect) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Set charset agar karakter Indonesia (é, ñ, dll) tidak rusak
mysqli_set_charset($conn, 'utf8mb4');
?>