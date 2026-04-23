<?php
// 1. Hubungkan ke database
include 'config.php';

// 2. Set header agar browser tahu ini adalah format JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Izin agar bisa diakses dari mana saja

// 3. Ambil data dari tabel dokter
$query = mysqli_query($conn, "SELECT * FROM dokter");

$data_dokter = array();

// 4. Masukkan hasil database ke dalam array PHP
while ($row = mysqli_fetch_assoc($query)) {
    $data_dokter[] = $row;
}

// 5. Ubah array PHP menjadi format JSON dan tampilkan
echo json_encode($data_dokter);
?>