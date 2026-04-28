<?php
// riwayat.php
include __DIR__ . '/config.php';
include __DIR__ . '/auth_check.php';

// Gunakan fungsi yang sama agar sistem tahu user sudah login
$user = checkAuth(); 

// Gunakan nama user dari hasil checkAuth untuk query
$nama_user = mysqli_real_escape_string($conn, $user['nama']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Antrean - Klinik Sehat</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 flex">
    <aside class="w-64 min-h-screen bg-blue-700 text-white p-6">
        <h2 class="text-xl font-bold mb-8">Klinik Sehat</h2>
        <nav class="space-y-2">
            <a href="dashboard-user.php" class="block py-2.5 px-4 hover:bg-blue-600 rounded transition">Cek Jadwal</a>
            <a href="riwayat.php" class="block py-2.5 px-4 bg-blue-800 rounded transition font-bold">Riwayat Antrean</a>
            <a href="logout.php" class="block py-2.5 px-4 text-blue-200 mt-20">Keluar</a>
        </nav>
    </aside>

    <main class="flex-1 p-8">
        <h1 class="text-2xl font-bold text-slate-800 mb-6">Riwayat Antrean Saya</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php
            // Mengambil data antrean milik user yang sedang login
            $query = "SELECT antrean.*, dokter.nama_dokter, dokter.spesialis 
                      FROM antrean 
                      JOIN dokter ON antrean.id_dokter = dokter.id 
                      WHERE antrean.nama_pasien = '$nama_user'
                      ORDER BY antrean.id DESC";
            
            $result = mysqli_query($conn, $query);

            if (mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)):
            ?>
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex justify-between items-center">
                <div>
                    <p class="text-sm text-slate-500 mb-1"><?php echo $row['tanggal_daftar']; ?></p>
                    <h3 class="text-lg font-bold text-slate-800"><?php echo $row['nama_dokter']; ?></h3>
                    <p class="text-blue-600 text-sm"><?php echo $row['spesialis']; ?></p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-slate-400 uppercase font-bold tracking-wider">No. Antrean</p>
                    <p class="text-4xl font-black text-blue-700">#<?php echo $row['no_antrean']; ?></p>
                </div>
            </div>
            <?php 
                endwhile; 
            } else {
                echo "<div class='col-span-full bg-orange-50 text-orange-700 p-4 rounded-xl'>Anda belum memiliki riwayat antrean.</div>";
            }
            ?>
        </div>
    </main>
</body>
</html>