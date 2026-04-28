<?php
// dashboard-user.php
include __DIR__ . '/auth_check.php';
$user = checkAuth();

// Akses data user yang login
echo $user['nama'];
echo $user['email'];
echo $user['role'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Pasien</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex">

    <aside class="w-64 min-h-screen bg-blue-700 text-white p-6">
        <h2 class="text-2xl font-bold mb-8">Klinik Sehat</h2>
        <nav class="space-y-4">
            <a href="dashboard-user.php" class="block py-2 px-4 bg-blue-800 rounded shadow-md font-semibold">Cek Jadwal</a>
            <a href="riwayat.php" class="block py-2.5 px-4 hover:bg-blue-600 rounded transition">Riwayat Antrean</a>
            <a href="logout.php" class="block py-2 px-4 text-red-300 mt-10">Keluar</a>
        </nav>
    </aside>

    <main class="flex-1 p-8">
        <header class="flex justify-between items-center mb-10">
            <h1 class="text-3xl font-bold text-gray-800">Cek Ketersediaan Dokter</h1>
        </header>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase">Nama Dokter</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase">Spesialis</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase">Jadwal</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase">Status</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php
                    // Ambil data lewat API
                    $url = "https://" . $_SERVER['HTTP_HOST'] . "/api/api-dokter.php";
                    $json_data = @file_get_contents($url); 
                    $ambil_data = json_decode($json_data, true);

                    if (!empty($ambil_data)) {
                        foreach ($ambil_data as $d): // Memulai loop
                    ?>
                    <tr class="hover:bg-blue-50/50 transition">
                        <td class="px-6 py-5 font-bold text-gray-800"><?php echo $d['nama_dokter']; ?></td>
                        <td class="px-6 py-5 text-gray-600"><?php echo $d['spesialis']; ?></td>
                        <td class="px-6 py-5">
                            <span class="block font-medium text-gray-700"><?php echo $d['hari']; ?></span>
                            <span class="text-xs text-gray-400"><?php echo $d['jam_praktik']; ?></span>
                        </td>
                        <td class="px-6 py-5">
                            <span class="px-3 py-1 text-xs font-bold rounded-full <?php echo ($d['status'] == 'Tersedia') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                                <?php echo $d['status']; ?>
                            </span>
                        </td>
                        <td class="px-6 py-5 text-center">
                            <a href="ambil-antrean.php?id=<?php echo $d['id']; ?>" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-md">Ambil Antrean</a>
                        </td>
                    </tr>
                    <?php 
                        endforeach; // Menutup loop dengan benar
                    } else {
                        echo "<tr><td colspan='5' class='p-5 text-center text-red-500'>Gagal mengambil data dari API atau data kosong.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>