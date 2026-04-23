<?php
include 'config.php';
session_start();

// Cek login
if (!isset($_SESSION['nama'])) {
    header("Location: login.php");
    exit();
}

// Ambil ID Dokter dari URL
$id_dokter = $_GET['id'];

// Ambil data dokter dari database
$query_dokter = mysqli_query($conn, "SELECT * FROM dokter WHERE id = '$id_dokter'");
$dokter = mysqli_fetch_assoc($query_dokter);

// Jika tombol Konfirmasi diklik
if (isset($_POST['konfirmasi'])) {
    $nama = $_SESSION['nama'];
    $nik = "Data dari Session"; // Kamu bisa ambil NIK dari database jika disimpan di session
    
    // Cari nomor antrean terakhir untuk dokter ini
    $cek_antrean = mysqli_query($conn, "SELECT MAX(no_antrean) as terakhir FROM antrean WHERE id_dokter = '$id_dokter'");
    $data_antrean = mysqli_fetch_assoc($cek_antrean);
    $nomor_baru = $data_antrean['terakhir'] + 1;

    // Simpan ke tabel antrean
    $simpan = mysqli_query($conn, "INSERT INTO antrean (id_dokter, nama_pasien, nik_pasien, no_antrean) 
                                   VALUES ('$id_dokter', '$nama', '$nik', '$nomor_baru')");

    if ($simpan) {
        echo "<script>alert('Berhasil! Nomor Antrean Anda: $nomor_baru'); window.location='dashboard-user.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Konfirmasi Antrean</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md border border-blue-100">
        <h2 class="text-2xl font-bold text-blue-600 mb-6 text-center">Konfirmasi Antrean</h2>
        
        <div class="bg-blue-50 p-4 rounded-xl mb-6 text-sm">
            <p class="text-gray-500">Pasien:</p>
            <p class="font-bold text-lg mb-3"><?php echo $_SESSION['nama']; ?></p>
            
            <p class="text-gray-500">Dokter Tujuan:</p>
            <p class="font-bold text-blue-700"><?php echo $dokter['nama_dokter']; ?></p>
            <p class="text-gray-600"><?php echo $dokter['spesialis']; ?></p>
            <p class="text-xs mt-2 text-gray-400"><?php echo $dokter['hari']; ?> | <?php echo $dokter['jam_praktik']; ?></p>
        </div>

        <form method="POST">
            <button name="konfirmasi" class="w-full bg-blue-600 text-white py-3 rounded-xl font-bold hover:bg-blue-700 shadow-lg shadow-blue-100 transition">
                Konfirmasi Ambil Antrean
            </button>
            <a href="dashboard-user.php" class="block text-center mt-4 text-gray-500 text-sm">Batal</a>
        </form>
    </div>
</body>
</html>