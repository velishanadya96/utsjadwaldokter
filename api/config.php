<?php
$host = 'gateway01.ap-southeast-1.prod.alicloud.tidbcloud.com';
$port = 4000;
$user = '2h7AMb9FTB6BmYe.root';
$pass = '29xnSbI4kHNOZnPN';
$db   = 'klinik-db';

$conn = mysqli_init(); // Pakai $conn
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);

$real_connect = mysqli_real_connect(
    $conn, // Jangan pakai $koneksi
    $host, 
    $user, 
    $pass, 
    $db, 
    $port, 
    NULL, 
    MYSQLI_CLIENT_SSL
);

if (!$real_connect) {
    die("Koneksi ke TiDB Cloud gagal: " . mysqli_connect_error());
}
?>