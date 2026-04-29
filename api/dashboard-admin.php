<?php
include __DIR__ . '/auth_check.php';
$user = checkAuth();

if ($user['role'] !== 'admin') {
    header("Location: /api/dashboard-user.php");
    exit();
}

// Handle hapus pasien
if (isset($_GET['hapus'])) {
    $hapus_id = (int) $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM users WHERE id = $hapus_id AND role = 'user'");
    header("Location: /api/dashboard-admin.php");
    exit();
}

// Ambil data pasien
$result = mysqli_query($conn, "SELECT * FROM users WHERE role = 'user' ORDER BY id DESC");

// Debug sementara — hapus setelah berhasil
if (!$result) {
    die("Query error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin - Kelola Pasien</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 flex">
    <aside class="w-64 min-h-screen bg-slate-900 text-white p-6">
        <h2 class="text-xl font-bold mb-8 text-blue-400">Admin Klinik</h2>
        <nav class="flex flex-col gap-2">
            <a href="/api/dashboard-admin.php" class="block py-2.5 px-4 bg-slate-800 rounded text-white font-semibold"> Data Pasien</a>
            <a href="/api/kelola-jadwal.php" class="block py-2.5 px-4 hover:bg-slate-700 rounded transition text-blue-300 font-semibold"> Kelola Jadwal Dokter</a>
            <a href="/api/logout.php" class="block py-2.5 px-4 text-red-400 hover:bg-red-900/20 rounded transition mt-10"> Logout</a>
        </nav>
    </aside>

    <main class="flex-1 p-8">
        <h1 class="text-2xl font-bold text-slate-800 mb-6">Daftar Pasien Terdaftar</h1>

        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-slate-200">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-100">
                    <tr>
                        <th class="px-6 py-4 text-sm font-bold text-slate-600 border-b">Nama & NIK</th>
                        <th class="px-6 py-4 text-sm font-bold text-slate-600 border-b">TTL</th>
                        <th class="px-6 py-4 text-sm font-bold text-slate-600 border-b">Domisili</th>
                        <th class="px-6 py-4 text-sm font-bold text-slate-600 border-b">Riwayat</th>
                        <th class="px-6 py-4 text-sm font-bold text-slate-600 border-b">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (mysqli_num_rows($result) === 0): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-400">Belum ada data pasien.</td>
                    </tr>
                    <?php else: ?>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4">
                            <p class="font-bold text-slate-800"><?php echo htmlspecialchars($row['nama']); ?></p>
                            <p class="text-xs text-slate-500"><?php echo htmlspecialchars($row['nik']); ?></p>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600">
                            <?php echo htmlspecialchars($row['tempat_lahir'] . ", " . $row['tgl_lahir']); ?>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-blue-600">
                            <?php echo !empty($row['provinsi']) ? htmlspecialchars($row['provinsi']) : '-'; ?>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-medium bg-orange-100 text-orange-700 rounded">
                                <?php echo !empty($row['riwayat_penyakit']) ? htmlspecialchars($row['riwayat_penyakit']) : '-'; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <a href="/api/edit-pasien.php?id=<?php echo $row['id']; ?>" class="text-blue-600 hover:text-blue-800 font-bold mr-3">Edit</a>
                            <a href="/api/dashboard-admin.php?hapus=<?php echo $row['id']; ?>" onclick="return confirm('Yakin ingin menghapus data ini?')" class="text-red-600 hover:text-red-800 font-bold">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <h1 class="text-2xl font-bold text-slate-800 mb-6 mt-12">Daftar Antrean Hari Ini</h1>
        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-slate-200">
            <table class="w-full text-left">
                <thead class="bg-slate-100">
                    <tr>
                        <th class="px-6 py-4">No. Antrean</th>
                        <th class="px-6 py-4">Nama Pasien</th>
                        <th class="px-6 py-4">Dokter Tujuan</th>
                        <th class="px-6 py-4">Waktu Daftar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php
                    $q_antrean = mysqli_query($conn, "SELECT antrean.*, dokter.nama_dokter FROM antrean JOIN dokter ON antrean.id_dokter = dokter.id ORDER BY antrean.id DESC");
                    if ($q_antrean && mysqli_num_rows($q_antrean) > 0):
                        while($a = mysqli_fetch_assoc($q_antrean)):
                    ?>
                    <tr>
                        <td class="px-6 py-4 font-bold text-blue-600 text-xl">#<?php echo $a['no_antrean']; ?></td>
                        <td class="px-6 py-4 font-medium"><?php echo htmlspecialchars($a['nama_pasien']); ?></td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($a['nama_dokter']); ?></td>
                        <td class="px-6 py-4 text-xs text-gray-400"><?php echo $a['tanggal_daftar']; ?></td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-slate-400">Belum ada antrean hari ini.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>