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


    <aside class="w-64 min-h-screen bg-gradient-to-b from-blue-700 to-blue-900 text-white p-6 flex flex-col shadow-xl">
        <div class="mb-10">
            <h2 class="text-2xl font-black tracking-tight">Klinik<span class="text-blue-300">Sehat</span></h2>
            <p class="text-blue-300 text-xs mt-1">Sistem Jadwal Dokter</p>
        </div>
        <nav class="space-y-2 flex-1">
            <a href="/api/dashboard-user.php"
               class="flex items-center gap-3 py-2.5 px-4 hover:bg-white/10 rounded-xl transition text-blue-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Cek Jadwal
            </a>
            <a href="/api/riwayat.php"
               class="flex items-center gap-3 py-2.5 px-4 bg-white/15 rounded-xl font-semibold text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Riwayat Antrean
            </a>
        </nav>
        <div class="mt-auto pt-6 border-t border-blue-600">
            <p class="text-xs text-blue-300 mb-1">Login sebagai</p>
            <p class="font-bold text-sm truncate"><?php echo htmlspecialchars($user['nama']); ?></p>
            <a href="/api/logout.php" class="mt-3 flex items-center gap-2 text-red-300 text-sm hover:text-red-200 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Logout
            </a>
        </div>
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